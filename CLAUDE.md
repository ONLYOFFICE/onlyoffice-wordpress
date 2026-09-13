# CLAUDE.md

Guidance for Claude Code when working in the **ONLYOFFICE Docs plugin for WordPress**
(`onlyoffice-wordpress`, text domain `onlyoffice`).

## What this plugin is

A WordPress plugin that connects a WordPress site to an **ONLYOFFICE Docs / Document Server**
instance so office files in the Media Library can be viewed, edited, co-edited and filled
(PDF forms) without leaving the site. Two surfaces:

- **Admin** — an *ONLYOFFICE Docs* menu with a **Files** list table (every attachment ONLYOFFICE
  can open) and a **Settings** page.
- **Front end** — a Gutenberg block / `[onlyoffice]` shortcode that embeds the editor (view mode,
  or fill mode for PDF forms) in a published post, plus a standalone full-page editor
  ([editor.php](editor.php)).

Requires PHP ≥ 7.4 and WordPress ≥ 5.7. Licensed GPL-2.0-or-later; every source file carries the
Ascensio System SIA header — **keep it when creating new files**.

## Architecture

Built on the *WordPress Plugin Boilerplate* pattern.

[onlyoffice.php](onlyoffice.php) defines four constants (`ONLYOFFICE_PLUGIN_NAME`, `_VERSION`,
`_URL`, `_FILE`), registers activation/deactivation/uninstall hooks, and instantiates
[Onlyoffice_Plugin](includes/class-onlyoffice-plugin.php). That class does **all** hook wiring:
it `require_once`s every other class, then registers callbacks through
[Onlyoffice_Plugin_Loader](includes/class-onlyoffice-plugin-loader.php), which only collects
`add_action`/`add_filter` pairs and flushes them in `run()`.

> There is no autoloader for plugin classes (Composer's autoloader covers `firebase/php-jwt` only).
> **A new class must be added to `load_dependencies()`, and its hooks to `define_*_hooks()` or
> `init_plugin()`.** Class names are `Onlyoffice_Plugin_*`, files are `class-onlyoffice-plugin-*.php`.

### Managers (`includes/managers/`) — the core logic

| Class | Responsibility |
| --- | --- |
| `Onlyoffice_Plugin_Config_Manager` | Builds the DocsAPI editor config (document, permissions, editorConfig) and signs it when JWT is on. |
| `Onlyoffice_Plugin_Url_Manager` | Builds the api.js / callback / download / editor URLs and encrypts-decrypts the `token` query parameter. |
| `Onlyoffice_Plugin_JWT_Manager` | Encode/decode HS256 tokens; resolves the configured auth header name. |
| `Onlyoffice_Plugin_Document_Manager` | Format capabilities (view/edit/fill), document type, and the per-attachment capability checks. |
| `Onlyoffice_Plugin_Callback_Manager` | Downloads the saved file from Document Server and writes it back over the attachment. |

### Two independent token layers — do not conflate them

1. **Link token** (`?token=…` on `/onlyoffice/oo.getfile` and `/onlyoffice/oo.callback`) —
   an AES-256-CTR payload `{attachment_id, user_id, action}` encrypted with the site options
   `onlyoffice-plugin-uuid` (passphrase) and `onlyoffice-plugin-bytes` (IV), both generated in
   [Onlyoffice_Plugin_Activator](includes/class-onlyoffice-plugin-activator.php). The `action`
   field is checked per route, so a download token can't be replayed against the callback.
2. **ONLYOFFICE JWT** — signed with the admin-configured secret, sent by Document Server either in
   the configured header (`Authorization: Bearer …`, header name overridable) or inside the
   callback body. Only enforced when the secret is non-empty (`is_jwt_enabled()`).

The REST routes all use `permission_callback => '__return_true'` **by design** — Document Server is
unauthenticated and both token layers above are the actual authorization. After validating the link
token the handlers call `wp_set_current_user()` with the embedded `user_id` and then re-check
capabilities (`read`, `edit_post`). Never weaken this sequence.

### Request flows

- **Embed in a post** — block `render_callback` → `wp_onlyoffice_shortcode()` in
  [Onlyoffice_Plugin_Frontend_Controller](controllers/class-onlyoffice-plugin-frontend-controller.php)
  → `render_embedded()` emits a `<div data-config="…">`;
  [frontend-controller.js](controllers/js/frontend-controller.js) reads it and calls
  `new DocsAPI.DocEditor(...)`. Embedded views use `unic_key = true` so each visitor gets a fresh
  editing session.
- **Full-page editor** — [editor.php](editor.php) is hit **directly** (not routed through WP), so it
  bootstraps `wp-load.php` itself. Anonymous users are allowed only via
  `can_anonymous_user_view_attachment()` (attachment must be attached to a *published* post),
  otherwise they are redirected to `wp_login_url()`.
- **Save** — Document Server POSTs to `/onlyoffice/oo.callback`; on status `MustSave` (2) the
  callback takes a post lock, fetches `body.url`, and overwrites the file through `$wp_filesystem`.
  Statuses come from `Onlyoffice_Plugin_Callback::CALLBACK_STATUS`.
- **Public PDF forms** — the embedded renderer switches to `type=desktop`, `mode=edit`, and points
  at `/onlyoffice/oo.callback-public-forms`, which deliberately **discards** the result (returns
  `error: 0` without saving) so visitors can fill a form without mutating the attachment.

### Supported formats

Format capabilities are **not** hard-coded. `public/assets/document-formats` is a git submodule
(<https://github.com/ONLYOFFICE/document-formats>) whose `onlyoffice-docs-formats.json` is cached
into the site option `onlyoffice-formats`, re-imported whenever `onlyoffice-formats-version` no
longer matches `ONLYOFFICE_PLUGIN_VERSION`. `git submodule update --init --recursive` is required
for a working checkout. The same list reaches JS as `window.ONLYOFFICE.formats`, localized onto
[formatsUtils.js](assets-onlyoffice/js/formatsUtils.js) from
[Onlyoffice_Plugin_Admin::enqueue_scripts()](admin/class-onlyoffice-plugin-admin.php) — admin-side only.

### Settings and multisite

All settings live in one option array, `onlyoffice_settings`, keyed by the
`Onlyoffice_Plugin_Settings` constants. Multisite resolution is the subtle part:

- Network admin writes to `update_site_option`; a single site writes to `update_option`.
- A site falls back to the network value when `ALLOWED_OWERWRITE_NETWORK_SETTINGS` is off **or**
  `INHERIT_NETWORK_SETTINGS` is on (which defaults to `true`).
- `get_onlyoffice_setting()` (public, used at runtime) and `get_onlyoffice_current_value_setting()`
  (private, used to render the form) resolve differently — pick the right one.

## Block and TinyMCE editors

`onlyoffice-wordpress/onlyoffice` (see [block.json](onlyoffice-wordpress-block/block.json)) is a
**dynamic** block: [save.js](onlyoffice-wordpress-block/src/save.js) serializes attributes into an
`[onlyoffice … /]` shortcode string, and PHP renders it. Consequences to respect:

- Shortcode attributes arrive as **strings**; booleans must go through
  `filter_var( …, FILTER_VALIDATE_BOOLEAN )`.
- Do **not** add `default` values to boolean attributes in `block.json` — defaults are omitted from
  serialized output and cause block validation errors on existing posts. Defaults belong in
  `edit.js` (`attributes.x ?? true`) and in `$defaults_atts` in the shortcode handler.
- The classic editor is supported via [onlyoffice-tinymce](onlyoffice-tinymce/onlyoffice-tinymce.php),
  which inserts the same shortcode wrapped in block comments so both editors stay interoperable.

The block's `build/` directory is gitignored; a checkout without `npm run build` has no working
block editor script.

## Conventions

- **PHP follows the WordPress Coding Standard** (`composer phpcs`), enforced by a CaptainHook
  pre-commit hook and by CI. Tabs for indent, Yoda conditions, snake_case, full docblocks with
  `@since`, `$_GET`/`$_POST` always through `sanitize_text_field( wp_unslash( … ) )`, output always
  escaped. Where a rule must be broken, use a targeted `// phpcs:ignore <Sniff>` as the existing
  code does — never a blanket disable.
- **CSS is linted with csscomb** ([.csscomb.json](.csscomb.json)) in CI before phpcs.
- **Commits follow Conventional Commits** (`feat:`, `fix:`, `docs:`, `chore:`), validated by
  CaptainHook via `ramsey/conventional-commits`.
- Every user-facing string is translated with the `onlyoffice` text domain; `.pot`/`.po` live in
  `languages/` and `.mo`/`.json` are generated at build time, not committed.
- Work happens on `develop`; `main` is the release branch.

## Commands

```bash
composer install                    # PHP deps (+ installs the CaptainHook hooks)
composer phpcs                      # WPCS lint; excludes the block, vendor and public/assets
composer make-pot                   # regenerate languages/onlyoffice.pot
composer update-po                  # merge the .pot into every .po
composer translations               # compile .mo and .json (needs wp-cli, run before packaging)

cd onlyoffice-wordpress-block
npm install
npm run build                       # required — build/ is gitignored
npm start                           # watch mode
npm run lint:js
```

Version bumps must touch **three** places in step: the `Version:` header and
`ONLYOFFICE_PLUGIN_VERSION` in [onlyoffice.php](onlyoffice.php), `version` in
[package.json](onlyoffice-wordpress-block/package.json), and a new section in
[CHANGELOG.md](CHANGELOG.md) — CI derives the release tag from the first version number in the
changelog. Bumping `ONLYOFFICE_PLUGIN_VERSION` also forces a re-import of the formats JSON.

## Gotchas

- Changing the `onlyoffice-plugin-uuid` / `onlyoffice-plugin-bytes` options invalidates every link
  token already handed to Document Server; sessions in flight will fail to save.
- The REST route paths are prefixed `oo.` on purpose, to stay compatible with the
  *Force Lowercase URLs* plugin. Don't rename them.
- The document `key` must change whenever the file changes, or Document Server serves a stale
  cached copy.
- Read and write files through `$wp_filesystem`, initializing it with `WP_Filesystem()` first.
