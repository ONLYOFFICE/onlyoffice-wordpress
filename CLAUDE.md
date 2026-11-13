# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

The WordPress plugin "ONLYOFFICE Docs" (WordPress.org slug and text domain `onlyoffice`, main file `onlyoffice.php`). It opens Media Library files in ONLYOFFICE Docs (Document Server) for viewing and co-editing from the admin dashboard, and adds a Gutenberg block, a shortcode and a classic-editor (TinyMCE) button that embed documents in posts. ONLYOFFICE Docs is a separate server: the browser loads its editor, Docs calls back into WordPress through REST routes, and WordPress downloads saved files from Docs.

The supported WordPress and PHP versions are the `Requires at least` / `Requires PHP` headers in `onlyoffice.php` and `readme.txt`.

Sibling ONLYOFFICE integrations on GitHub (`ONLYOFFICE/onlyoffice-drupal`, `ONLYOFFICE/onlyoffice-nextcloud`, `ONLYOFFICE/moodle-mod_onlyofficeeditor` and others) solve the same problems; check them for prior art.

## Branches

- PRs target `develop`. `main` holds released code; merging into `main` tags and builds a release (see `.github/workflows/`).
- Branch names: `<type>/<short-kebab-description>` (`feature/`, `fix/`, `bugfix/`, `chore/`, `ci/`, `docs/`).

## Setup and commands

Clone with `--recurse-submodules`: `public/assets/document-formats` (the supported-formats JSON) is a git submodule and must be present.

```bash
composer install                  # runtime dep firebase/php-jwt (vendor/ is required at runtime) + dev tools + CaptainHook git hooks
cd onlyoffice-wordpress-block && npm ci && npm run build   # block build/ (gitignored); the block needs it
composer phpcs                    # WordPress coding standards; see the script for ignored paths
vendor/bin/phpcs --standard=WordPress <path>               # lint one file (phpcbf to autofix)
cd onlyoffice-wordpress-block && npm run lint:js           # wp-scripts eslint for the block
csscomb <path>                    # CSS order/format from .csscomb.json (npm install -g csscomb, as CI does)
composer make-pot && composer update-po   # refresh languages/onlyoffice.pot and every .po
composer translations             # build .mo and JS .json translations (gitignored, built at release)
```

CaptainHook enforces Conventional Commits on `commit-msg` and runs `composer phpcs` on `pre-commit`. CI runs the linters in `.github/workflows/lint.yml`; read it for the PHP and Node versions.

Runtime behaviour needs a live ONLYOFFICE Docs that can reach the WordPress site and vice versa, so lints don't show that a change works.

## Structure

- `onlyoffice.php` — bootstrap: constants (version, URL, file), activation/uninstall hooks, extra upload MIME types.
- `includes/class-onlyoffice-plugin.php` — wires every hook through `Onlyoffice_Plugin_Loader`.
- `includes/managers/` — static helpers: editor config (`Config_Manager`), formats and permissions (`Document_Manager`), URLs and link tokens (`Url_Manager`), JWT (`JWT_Manager`), saving (`Callback_Manager`).
- `includes/settings/` — settings page, multisite network/site settings, `get_onlyoffice_setting()`.
- `includes/files/` — "ONLYOFFICE Docs → Files" admin list table.
- `public/` — REST routes Docs calls (registered in `class-onlyoffice-plugin-public.php`) and their handlers in `public/views/`.
- `editor.php` — standalone full-page editor, loaded directly by URL (`editor.php?attachment_id=N`), bootstraps WordPress itself.
- `controllers/` — block render callback and the `[onlyoffice]` shortcode (front end).
- `onlyoffice-wordpress-block/` — Gutenberg block source (`src/`) and `block.json`.
- `onlyoffice-tinymce/` — classic editor button.
- `public/assets/document-formats/` — submodule; never edit it here, it belongs to ONLYOFFICE/document-formats.

## Rules

- Docs calls the REST routes without a WordPress session, so they use `permission_callback => '__return_true'`. They are protected by the encrypted link token in the `token` query parameter (`Onlyoffice_Plugin_Url_Manager`, whose `action` must match the route) and by the Docs JWT when a secret is set. Any change to these routes must keep both checks.
- Build every URL handed to Docs through `Onlyoffice_Plugin_Url_Manager` and every editor config through `Onlyoffice_Plugin_Config_Manager::get_config()`, so tokens and JWT signing apply.
- Read settings only through `Onlyoffice_Plugin_Settings::get_onlyoffice_setting()`; it resolves multisite network vs site settings. A new setting needs a constant, a field in `init()`, saving in `update_plugin_settings()` (the form is handled there, not by `options.php`), the multisite disabled state, and cleanup in the uninstall hook.
- Option keys and REST route paths are stored in user sites and saved links (including the `allowed_owerwrite` typo and the `oo.` route prefix). Don't rename them without a migration.
- The block's saved markup (`src/save.js`) and `block.json` attributes are stored in posts. Changing either without a `deprecated` entry breaks block validation in existing posts.
- Supported formats come only from the submodule JSON via `Onlyoffice_Plugin_Document_Manager`; don't hard-code extension lists.
- Add user-visible changes to the top of `CHANGELOG.md` under `## Added` or `## Changed`, without a version header; the version is added at release time. Release CI takes the version from the first dotted number in that file.

## Coding practices

- Before writing new code, check whether WordPress core already provides it (HTTP API, `WP_Filesystem`, options, REST, list tables, capabilities). Use public APIs only; avoid functions and classes marked private or `@access private`.
- Write code for the lowest WordPress and PHP versions in the `Requires at least` / `Requires PHP` headers. Check the `@since` of every core function you use in the WordPress developer reference. Functions from newer PHP versions are fine only where WordPress core polyfills them in `wp-includes/compat.php` with a `@since` at or below the minimum WordPress version; newer PHP syntax (named arguments, union types, `match`) is not.
- Sanitize input early (`sanitize_text_field( wp_unslash( ... ) )`), escape output late (`esc_html`, `esc_attr`, `esc_url`, `wp_kses`). Every admin action needs a nonce check and a capability check.
- Check capabilities against the attachment (`read_post`, `edit_post`) through the helpers in `Onlyoffice_Plugin_Document_Manager`, not against roles.
- Before relying on a Docs API option, event or callback field, check in the Docs API documentation which Docs version added it.
- Keep `// phpcs:ignore` rare, name the exact sniff, and only where the code is deliberately non-standard.
- The Docs document key is built from the attachment's `post_modified`. Code that replaces an attachment's file must bump it with `wp_update_post()`, as `Callback_Manager::proccess_save()` does, or Docs keeps serving the cached version.

## Conventions

- PHP follows the WordPress coding standards. Classes are `Onlyoffice_Plugin_*` in `class-onlyoffice-plugin-*.php` files; new global functions, options and hooks are prefixed `onlyoffice`.
- Register new hooks in `Onlyoffice_Plugin` through the loader. The hooks in `onlyoffice.php`, `onlyoffice-tinymce/onlyoffice-tinymce.php` and the list-table constructor are older exceptions.
- Load assets with `wp_enqueue_script` / `wp_enqueue_style`, versioned with `ONLYOFFICE_PLUGIN_VERSION`. The block loads through `block.json`, the TinyMCE button through `mce_external_plugins`, and `editor.php` prints Docs' `api.js` tag itself.
- Non-block JavaScript is an IIFE, using jQuery where it touches the DOM; the block is React with `@wordpress/*` packages built by `wp-scripts`. Pass page-wide PHP data with `wp_localize_script`; each block or shortcode instance's editor config goes in an escaped `data-config` JSON attribute. In JS, get formats from `ONLYOFFICE.formatsUtils`, never a hard-coded list.
- Every translatable string passes the literal text domain `onlyoffice` (PHP `__()` family, JS `wp.i18n` / `@wordpress/i18n`), except strings WordPress core already translates, which are called without a domain to reuse core's translation. Add a `translators:` comment for placeholders. Scripts with strings need `wp_set_script_translations`. After adding strings, build the block (`make-pot` reads block strings from `build/`), update the `.pot` and every `.po`, and translate them yourself; a human reviewer checks translations.
- PHP files and the block's `src/*.js` carry the GPL license header. Copy it from an existing file of the same kind onto any new one.
- Commit messages follow Conventional Commits: `<type>(<optional scope>): <description>` with a lowercase type (`feat`, `fix`, `refactor`, `docs`, `ci`, `style`, `build`, `chore`).
