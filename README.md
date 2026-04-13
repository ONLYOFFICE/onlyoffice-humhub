# ONLYOFFICE module for HumHub

This integration lets users create, edit, and collaborate on office documents directly within HumHub using [ONLYOFFICE Docs](https://www.onlyoffice.com/docs).

<p align="center">
  <a href="https://www.onlyoffice.com/office-for-humhub">
    <img width="800" src="https://static-site.onlyoffice.com/public/images/templates/office-for-humhub/hero/main@2x.png" alt="ONLYOFFICE Docs for HumHub">
  </a>
</p>

## Features ✨

- Create and edit text documents, spreadsheets, and presentations within HumHub.
- Share documents securely with other HumHub users.
- Collaborate in real time using:
  - Two co-editing modes (Fast and Strict)
  - Track Changes and version history
  - Comments and built-in chat

### 📁 Supported file formats

| Action                       | Supported formats                      |
| ---------------------------- | -------------------------------------- |
| View and Edit                | DOCX, XLSX, PPTX                       |
| View only                    | ODT, ODS, ODP, DOC, XLS, PPT, TXT, PDF |
| Convert to OOXML for editing | ODT, ODS, ODP, DOC, XLS, PPT, TXT, CSV |

## Installing ONLYOFFICE Docs

You'll need an instance of ONLYOFFICE Docs (Document Server) accessible from both HumHub and client side. ONLYOFFICE Docs must also be able to send POST requests directly to your HumHub instance.

You can install the self-hosted editors or use the cloud version.

### 🖥️ Self-hosted editors

- **Community Edition (Free):** [Docker guide](https://github.com/ONLYOFFICE/Docker-DocumentServer) or [manual installation](https://helpcenter.onlyoffice.com/installation/docs-community-install-ubuntu.aspx)
- **Enterprise Edition:** [Installation guide](https://helpcenter.onlyoffice.com/docs/installation/enterprise)

Community Edition vs Enterprise Edition comparison can be found [here](#onlyoffice-docs-editions).

### ☁️ Cloud

If you prefer not to set up your own server, use ONLYOFFICE Docs Cloud — no installation or configuration needed.

👉 [Get started here](https://www.onlyoffice.com/docs-registration)

## Installing ONLYOFFICE module for HumHub 🧩

Either install it from [HumHub Marketplace](https://marketplace.humhub.com/module/onlyoffice) OR simply clone the repository inside one of the folder specified by `moduleAutoloadPaths` parameter. Please see [HumHub Documentation](https://docs.humhub.org/docs/develop/environment#module-loader-path) for more information.

## Configuring ONLYOFFICE module for HumHub ⚙️

Navigate to `Administration` -> `Modules` find the plugin under Installed tab and click `Configure`.

Enter the URL of your ONLYOFFICE Docs instance (cloud or on-premises).

Configuration settings include JWT, enabled by default to protect the editors from unauthorized access. If setting a custom **secret key**, ensure it matches the one in the ONLYOFFICE Docs [config file](https://api.onlyoffice.com/docs/docs-api/additional-api/signature/) for proper validation.

## How it works

The ONLYOFFICE integration follows the API documented [here](https://api.onlyoffice.com/docs/docs-api/get-started/basic-concepts/):

* When creating a new file, the user will be provided with **Document**, **Spreadsheet**, or **Presentation** options in the `Create document` menu.

* The browser invokes the `index` method in the `/controllers/CreateController.php` controller.

* Or, when opening an existing file, the user will be provided with `View document` or `Edit document` depending on an extension.

* A popup is opened and the `index` method of the `/controllers/OpenController.php` controller is invoked.

* The app prepares a JSON object with the following properties:

  * **url** - the URL that ONLYOFFICE Document Server uses to download the document;
  * **callbackUrl** - the URL that ONLYOFFICE Document Server informs about status of the document editing;
  * **key** - the random MD5 hash to instruct ONLYOFFICE Document Server whether to download the document again or not;
  * **title** - the document Title (name);
  * **id** - the identification of the user;
  * **name** - the name of the user.

* HumHub takes this object and constructs a page from `views/open/index.php` template, filling in all of those values so that the client browser can load up the editor.

* The client browser makes a request for the javascript library from ONLYOFFICE Document Server and sends ONLYOFFICE Document Server the DocEditor configuration with the above properties.

* Then ONLYOFFICE Document Server downloads the document from HumHub and the user begins editing.

* ONLYOFFICE Document Server sends a POST request to the _callbackUrl_ to inform HumHub that a user is editing the document.

* When all users and client browsers are done with editing, they close the editing window.

* After [10 seconds](https://api.onlyoffice.com/docs/docs-api/get-started/how-it-works/saving-file/#save-delay) of inactivity, ONLYOFFICE Document Server sends a POST to the _callbackUrl_ letting HumHub know that the clients have finished editing the document and closed it.

* HumHub downloads the new version of the document, replacing the old one.

## ONLYOFFICE Docs editions

ONLYOFFICE offers different versions of its online document editors that can be deployed on your own servers.

* Community Edition 🆓 (`onlyoffice-documentserver` package)
* Enterprise Edition 🏢 (`onlyoffice-documentserver-ee` package)

The table below will help you to make the right choice.

| Pricing and licensing | Community Edition | Enterprise Edition |
| ------------- | ------------- | ------------- |
| | [Get it now](https://www.onlyoffice.com/download-community?utm_source=github&utm_medium=cpc&utm_campaign=GitHubHumHub#docs-community)  | [Start Free Trial](https://www.onlyoffice.com/download?utm_source=github&utm_medium=cpc&utm_campaign=GitHubHumHub#docs-enterprise)  |
| Cost  | FREE  | [Go to the pricing page](https://www.onlyoffice.com/docs-enterprise-prices?utm_source=github&utm_medium=cpc&utm_campaign=GitHubHumHub)  |
| Simultaneous connections | up to 20 maximum  | As in chosen pricing plan |
| Number of users | up to 20 recommended | As in chosen pricing plan |
| License | GNU AGPL v.3 | Proprietary |
| **Support** | **Community Edition** | **Enterprise Edition** |
| Documentation | [Help Center](https://helpcenter.onlyoffice.com/docs/installation/community) | [Help Center](https://helpcenter.onlyoffice.com/docs/installation/enterprise) |
| Standard support | [GitHub](https://github.com/ONLYOFFICE/DocumentServer/issues) or paid | 1 or 3 years support included |
| Premium support | [Contact us](mailto:sales@onlyoffice.com) | [Contact us](mailto:sales@onlyoffice.com) |
| **Services** | **Community Edition** | **Enterprise Edition** |
| Conversion Service                | + | + |
| Document Builder Service          | + | + |
| **Interface** | **Community Edition** | **Enterprise Edition** |
| Tabbed interface                  | + | + |
| Dark theme                        | + | + |
| 125%, 150%, 175%, 200% scaling    | + | + |
| White Label                       | - | - |
| Integrated test example (node.js) | + | + |
| Mobile web editors                | - | +* |
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
| Data validation                 | + | + |
| Conditional formatting          | + | + |
| Sparklines                      | + | + |
| Sheet Views                     | + | + |
| **Presentation Editor features** | **Community Edition** | **Enterprise Edition** |
| Font and paragraph formatting   | + | + |
| Object insertion                | + | + |
| Transitions                     | + | + |
| Animations                      | + | + |
| Presenter mode                  | + | + |
| Notes                           | + | + |
| **Form creator features** | **Community Edition** | **Enterprise Edition** |
| Adding form fields              | + | + |
| Form preview                    | + | + |
| Saving as PDF                   | + | + |
| **PDF Editor features**      | **Community Edition** | **Enterprise Edition** |
| Text editing and co-editing                                | + | + |
| Work with pages (adding, deleting, rotating)               | + | + |
| Inserting objects (shapes, images, hyperlinks, etc.)       | + | + |
| Text annotations (highlight, underline, cross out, stamps) | + | + |
| Comments                        | + | + |
| Freehand drawings               | + | + |
| Form filling                    | + | + |
| | [Get it now](https://www.onlyoffice.com/download-community?utm_source=github&utm_medium=cpc&utm_campaign=GitHubHumHub#docs-community)  | [Start Free Trial](https://www.onlyoffice.com/download?utm_source=github&utm_medium=cpc&utm_campaign=GitHubHumHub#docs-enterprise)  |

\* If supported by DMS.

## Need help? User Feedback and Support 💡

* **🐞 Found a bug?** Please report it by creating an [issue](https://github.com/ONLYOFFICE/onlyoffice-humhub/issues).
* **❓ Have a question?** Ask our community and developers on the [ONLYOFFICE Forum](https://community.onlyoffice.com).
* **👨‍💻 Need help for developers?** Check our [API documentation](https://api.onlyoffice.com).
* **💡 Want to suggest a feature?** Share your ideas on our [feedback platform](https://feedback.onlyoffice.com/forums/966080-your-voice-matters).