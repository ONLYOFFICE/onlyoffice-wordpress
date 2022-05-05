=== ONLYOFFICE ===
Contributors: onlyoffice
Tags: onlyoffice, integration, collaboration, editor, office, document, spreadsheet, presentation
Requires at least: 5.7
Tested up to: 5.9
Stable tag: 1.0.2
Requires PHP: 7.4
License: GPLv2
License URI: https://github.com/ONLYOFFICE/onlyoffice-wordpress/blob/main/LICENSE

ONLYOFFICE plugin allows users to edit and view office documents from WordPress using ONLYOFFICE Docs.

== Description ==

ONLYOFFICE integration plugin allows WordPress administrators to open documents, spreadsheets, and presentations for collaborative editing using ONLYOFFICE Docs (online document editors). In published posts, the editors are visible to all WordPress site visitors (both authorized and unauthorized) in the Embedded mode only.

**Editing files uploaded to Wordpress**

All uploaded files from the Media section will appear on the ONLYOFFICE -> Files page. The editor opens in the same tab by clicking on the file name. Users with administrator rights are able to co-edit documents. All the changes are saved in the same file.

**Creating a post**

When creating a post, you can add the ONLYOFFICE element (block) and then upload a new file or select one from the Media Library. The added file will be displayed as the ONLYOFFICE logo with the file name in the currently edited post. After the post is published (when you press the Publish or Update button), your WordPress site visitors will have access to this file for viewing in the Embedded mode.

== Frequently Asked Questions ==

= What should I know before using the plugin? =

You need to have [ONLYOFFICE Document Server](https://github.com/ONLYOFFICE/DocumentServer) installed. You can install free Community version or scalable Enterprise Edition.

= How to configure the plugin? =

Go to WordPress administrative dashboard -> ONLYOFFICE -> Settings. Specify the URL of the installed ONLYOFFICE Document Server and the Secret key.

= What collaborative features do the editors provide? =

You can co-author documents using real-time or paragraph-locking co-eding modes, Track Changes, comments, and built-in chat.

== Screenshots ==

1. ONLYOFFICE plugin configuration settings within the WordPress administrative dashboard.
2. ONLYOFFICE -> Files page within the WordPress administrative dashboard.
3. ONLYOFFICE document editor opened from the WordPress admin dashboard.
4. Adding ONLYOFFICE block when creating a post.
5. Uploading a new file or selecting one from the Media Library to the ONLYOFFICE block.
6. Added file displayed as the ONLYOFFICE logo with the file name in the currently edited post.
7. ONLYOFFICE file available for viewing in the Embedded mode to the WordPress site visitors.

== Changelog ==

= 1.0.2 =
* fixed issues for the marketplace release

= 1.0.1 =
* fixed issues for the marketplace release

= 1.0 =
* added configuration page of plugin
* added coediting docx, xlsx, pptx by authors
* added embedded view xls, xlsx, xlsm, xlt, xltx, xltm, ods, fods, ots, csv, pps, ppsx, ppsm, ppt, pptx, pptm, pot, potx, potm, odp, fodp, otp, doc, docx, docm, dot, dotx, dotm, odt, fodt, ott, rtf, txt, html, htm, mht, xml, pdf, djvu, fb2, epub, xps, oxps on public page
* JWT support
* detecting mobile browser
* set favicon on editor page
* added goBack url for document editor

== Upgrade Notice ==

= 1.0 =
This is the first version of the plugin.