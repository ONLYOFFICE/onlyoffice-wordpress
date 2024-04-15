=== ONLYOFFICE Docs ===
Contributors: onlyoffice
Tags: onlyoffice, collaboration, editor, office, document, spreadsheet, presentation, forms, pdf
Requires at least: 5.7
Tested up to: 6.1
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv2
License URI: https://github.com/ONLYOFFICE/onlyoffice-wordpress/blob/main/LICENSE

ONLYOFFICE Docs plugin allows users to edit and view office documents from WordPress using ONLYOFFICE Docs.

== Description ==

ONLYOFFICE Docs integration plugin allows WordPress administrators to open documents, spreadsheets, and presentations for collaborative editing using ONLYOFFICE Docs (online document editors). In published posts, the editors are visible to all WordPress site visitors (both authorized and unauthorized) in the Embedded mode only.

**Editing files uploaded to Wordpress**

All uploaded files from the Media section will appear on the ONLYOFFICE Docs -> Files page. The editor opens in the same tab by clicking on the file name. Users with administrator rights are able to co-edit documents. All the changes are saved in the same file.

**Creating a post**

When creating a post, you can add the ONLYOFFICE Docs element (block) and then upload a new file or select one from the Media Library. The added file will be displayed as the ONLYOFFICE logo with the file name in the currently edited post. After the post is published (when you press the Publish or Update button), your WordPress site visitors will have access to this file for viewing in the Embedded mode.

== Frequently Asked Questions ==

= What should I know before using the plugin? =

You need to have [ONLYOFFICE Document Server](https://github.com/ONLYOFFICE/DocumentServer) installed. You can install free Community version or scalable Enterprise Edition.

= How to configure the plugin? =

Go to WordPress administrative dashboard -> ONLYOFFICE Docs -> Settings. Specify the URL of the installed ONLYOFFICE Document Server and the Secret key.

Please note: Starting from version 7.2 of ONLYOFFICE Docs, JWT is enabled by default and the secret key is generated automatically to restrict the access to ONLYOFFICE Docs and for security reasons and data integrity. Specify your own secret key in the WordPress administrative configuration. In the ONLYOFFICE Docs [config file](https://api.onlyoffice.com/editors/signature/), specify the same secret key and enable the validation.

= What collaborative features do the editors provide? =

You can co-author documents using real-time or paragraph-locking co-eding modes, Track Changes, comments, and built-in chat.

== Screenshots ==

1. ONLYOFFICE Docs plugin configuration settings within the WordPress administrative dashboard.
2. ONLYOFFICE Docs -> Files page within the WordPress administrative dashboard.
3. ONLYOFFICE Docs editor opened from the WordPress admin dashboard.
4. Adding ONLYOFFICE Docs block when creating a post.
5. Uploading a new file or selecting one from the Media Library to the ONLYOFFICE Docs block.
6. Added file displayed as the ONLYOFFICE logo with the file name in the currently edited post.
7. ONLYOFFICE file available for viewing in the Embedded mode to the WordPress site visitors.

== Changelog ==
= 2.0.0 =
* multisite support
* stub for onlyoffice-wordpress-block that displays error information if the document server is not accessible or the file is not found 
* settings for onlyoffice block (width, height, align, show open in onlyoffice button)
* button copy link to editor, in the table with files
* ability to insert a link to the editor on the page
* access for an anonymous user, open the document for viewing in the editor if the document is attached to a public post
* filling pdf
* editor url
* displaying the onlyoffice-wordpress-block component in the Gutenberg editor
* remove filling for oform
* supported formats updated

= 1.1.0 =
* extended list of supported formats
* support docxf and oform formats
* support for the connector to work in conjunction with the plugin "Force Lowercase URLs" (https://wordpress.org/plugins/force-lowercase-urls)
* setting authorization header
* compatible with classic editor TinyMCE
* Link to docs cloud

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