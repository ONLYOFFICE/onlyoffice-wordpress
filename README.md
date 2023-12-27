# WordPress ONLYOFFICE integration plugin

This app enables users to edit and view office documents from [WordPress](https://wordpress.org/) pages on site and admin panel using ONLYOFFICE Docs packaged as Document Server - [Community or Enterprise Edition](#onlyoffice-docs-editions).

## Features

The app allows to:

* Edit text documents, spreadsheets, and presentations.
* Co-edit documents in real-time using two co-editing modes (Fast and Strict), Track Changes, comments, and built-in chat.

Supported formats:

**For viewing:**

* **WORD:** DJVU, DOC, DOCM, DOCX, DOCXF, DOT, DOTM, DOTX, EPUB, FB2, FODT, HTM, HTML, MHT, ODT, OFORM, OTT, OXPS, PDF, RTF, TXT, XML, XPS
* **CELL:** CSV, FODS, ODS, OTS, XLS, XLSM, XLSX, XLT, XLTM, XLTX
* **SLIDE:** FODP, ODP, OTP, POT, POTM, POTX, PPS, PPSM, PPSX, PPT, PPTM, PPTX

**For editing:**

* **WORD:** DOCM, DOCX, DOCXF, DOTM, DOTX, HTM, XML
* **CELL:** XLSM, XLSX, XLTM, XLTX
* **SLIDE:** POTM, POTX, PPSM, PPSX, PPTM, PPTX

## Installing ONLYOFFICE Docs

You will need an instance of ONLYOFFICE Docs (Document Server) that is resolvable and connectable both from WordPress and any end clients. ONLYOFFICE Document Server must also be able to POST to WordPress directly.

You can install free Community version of ONLYOFFICE Docs or scalable Enterprise Edition.

To install free Community version, use [Docker](https://github.com/onlyoffice/Docker-DocumentServer) (recommended) or follow [these instructions](https://helpcenter.onlyoffice.com/installation/docs-community-install-ubuntu.aspx) for Debian, Ubuntu, or derivatives.

To install Enterprise Edition, follow the instructions [here](https://helpcenter.onlyoffice.com/installation/docs-enterprise-index.aspx).

Community Edition vs Enterprise Edition comparison can be found [here](#onlyoffice-docs-editions).

## Installing WordPress ONLYOFFICE integration plugin

#### Minimum version of WordPress for ONLYOFFICE integration plugin is 5.9.0.

1. Download the zipped plugin.
2. Navigate to the **Plugins** section in your WordPress administrative dashboard.
3. Click **Add New** at the top of the page.
4. Click **Upload Plugin** at the top of the page.
5. Click **Choose File** and select the downloaded zipped plugin.
6. Once the plugin is installed, click **Activate**.

Alternatively, you can clone the master branch (and then activate the plugin from the WordPress administrative dashboard as well):
 
```
cd wp-content/plugins
git clone https://github.com/ONLYOFFICE/onlyoffice-wordpress
```

## Configuring WordPress ONLYOFFICE integration plugin

Configure the plugin via the WordPress interface. Go to **WordPress administrative dashboard -> ONLYOFFICE -> Settings** and specify the following parameters:

- **Document Editing Service address**:
  The URL of the installed ONLYOFFICE Document Server.

- **Document server JWT secret key**:
  Starting from version 7.2, JWT is enabled by default and the secret key is generated automatically to restrict the access to ONLYOFFICE Docs and for security reasons and data integrity. Specify your own secret key in the WordPress administrative configuration. In the ONLYOFFICE Docs [config file](https://api.onlyoffice.com/editors/signature/), specify the same secret key and enable the validation.

## Using WordPress ONLYOFFICE integration plugin

ONLYOFFICE integration plugin allows WordPress administrators to open files for collaborative editing using ONLYOFFICE Docs (online document editors). In published posts, the editors are visible to all WordPress site visitors (both authorized and unauthorized) in the Embedded mode only.

**Editing files uploaded to WordPress**

All uploaded files from the Media section will appear on the ONLYOFFICE -> Files page. The editor opens in the same tab by clicking on the file name. Users with administrator rights are able to co-edit documents. All the changes are saved in the same file.

**Creating a post**

When creating a post, you can add the ONLYOFFICE element (block) and then upload a new file or select one from the Media Library. The added file will be displayed as the ONLYOFFICE logo with the file name in the currently edited post. After the post is published (when you press the Publish or Update button), your WordPress site visitors will have access to this file for viewing in the Embedded mode.

## ONLYOFFICE Docs editions

ONLYOFFICE offers different versions of its online document editors that can be deployed on your own servers.

**ONLYOFFICE Docs** packaged as Document Server:

* Community Edition (`onlyoffice-documentserver` package)
* Enterprise Edition (`onlyoffice-documentserver-ee` package)

The table below will help you make the right choice.

| Pricing and licensing | Community Edition | Enterprise Edition |
| ------------- | ------------- | ------------- |
| | [Get it now](https://www.onlyoffice.com/download-docs.aspx#docs-community)  | [Start Free Trial](https://www.onlyoffice.com/download-docs.aspx#docs-enterprise)  |
| Cost  | FREE  | [Go to the pricing page](https://www.onlyoffice.com/docs-enterprise-prices.aspx)  |
| Simultaneous connections | up to 20 maximum  | As in chosen pricing plan |
| Number of users | up to 20 recommended | As in chosen pricing plan |
| License | GNU AGPL v.3 | Proprietary |
| **Support** | **Community Edition** | **Enterprise Edition** |
| Documentation | [Help Center](https://helpcenter.onlyoffice.com/installation/docs-community-index.aspx) | [Help Center](https://helpcenter.onlyoffice.com/installation/docs-enterprise-index.aspx) |
| Standard support | [GitHub](https://github.com/ONLYOFFICE/DocumentServer/issues) or paid | One year support included |
| Premium support | [Contact us](mailto:sales@onlyoffice.com) | [Contact us](mailto:sales@onlyoffice.com) |
| **Services** | **Community Edition** | **Enterprise Edition** |
| Conversion Service                | + | + |
| Document Builder Service          | + | + |
| **Interface** | **Community Edition** | **Enterprise Edition** |
| Tabbed interface                       | + | + |
| Dark theme                             | + | + |
| 125%, 150%, 175%, 200% scaling         | + | + |
| White Label                            | - | - |
| Integrated test example (node.js)      | + | + |
| Mobile web editors                     | - | +* |
| **Plugins & Macros** | **Community Edition** | **Enterprise Edition** |
| Plugins                           | + | + |
| Macros                            | + | + |
| **Collaborative capabilities** | **Community Edition** | **Enterprise Edition** |
| Two co-editing modes              | + | + |
| Comments                          | + | + |
| Built-in chat                     | + | + |
| Review and tracking changes       | + | + |
| Display modes of tracking changes | + | + |
| Version history                   | + | + |
| **Document Editor features** | **Community Edition** | **Enterprise Edition** |
| Font and paragraph formatting   | + | + |
| Object insertion                | + | + |
| Adding Content control          | + | + | 
| Editing Content control         | + | + | 
| Layout tools                    | + | + |
| Table of contents               | + | + |
| Navigation panel                | + | + |
| Mail Merge                      | + | + |
| Comparing Documents             | + | + |
| **Spreadsheet Editor features** | **Community Edition** | **Enterprise Edition** |
| Font and paragraph formatting   | + | + |
| Object insertion                | + | + |
| Functions, formulas, equations  | + | + |
| Table templates                 | + | + |
| Pivot tables                    | + | + |
| Data validation           | + | + |
| Conditional formatting          | + | + |
| Sparklines                   | + | + |
| Sheet Views                     | + | + |
| **Presentation Editor features** | **Community Edition** | **Enterprise Edition** |
| Font and paragraph formatting   | + | + |
| Object insertion                | + | + |
| Transitions                     | + | + |
| Animations                      | + | + |
| Presenter mode                  | + | + |
| Notes                           | + | + |
| **Form creator features** | **Community Edition** | **Enterprise Edition** |
| Adding form fields           | + | + |
| Form preview                    | + | + |
| Saving as PDF                   | + | + |
| **Working with PDF**      | **Community Edition** | **Enterprise Edition** |
| Text annotations (highlight, underline, cross out) | + | + |
| Comments                        | + | + |
| Freehand drawings               | + | + |
| Form filling                    | + | + |
| | [Get it now](https://www.onlyoffice.com/download-docs.aspx#docs-community)  | [Start Free Trial](https://www.onlyoffice.com/download-docs.aspx#docs-enterprise)  |

\* If supported by DMS.

In case of technical problems, the best way to get help is to submit your issues [here](https://github.com/ONLYOFFICE/onlyoffice-wordpress/issues). Alternatively, you can contact ONLYOFFICE team on [forum.onlyoffice.com](https://forum.onlyoffice.com/).