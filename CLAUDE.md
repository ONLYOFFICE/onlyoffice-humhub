# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

ONLYOFFICE connector module for HumHub (module id `onlyoffice`). It opens HumHub files in ONLYOFFICE Docs, which downloads the files from HumHub and posts saves back to it.

## Setup

`git submodule update --init --recursive` is required at runtime: `resources/formats` holds `onlyoffice-docs-formats.json`, which `Module::formats()` reads, and `resources/templates` holds the per-language blank documents used for new files.

## Architecture

- **`Module.php`** holds the shared logic:
  - which extensions are editable, view-only or convertible (`formats()`, built from the formats JSON)
  - JWT encode/decode
  - the Docs command and conversion services
  - document keys: `generateDocumentKey` stores them in `file.onlyoffice_key`, and `Events::onAfterNewStoredFile` clears it on a new file version unless `onlyoffice_key_lock` is set
  - `generateHash`/`readHash` for the `doc` parameter of backend URLs
- **`Events.php`** (wired in `config.php`) registers the file handlers in `filehandler/` when the user has the `CanUseOnlyOffice` permission. The handlers add Create/Edit/View/Fill/Convert to HumHub's file menus.
- **`components/BaseFileController`** (base of `Open`, `Convert`, `Share`) works out which file a request is for: from `guid`, or from a share secret (`onlyoffice_share`). It enforces the mode: `edit` requires `canDelete()` unless `restrict=fill`.
- **Two kinds of controllers:**
  - **Browser-facing:** `Open`, `Create`, `Convert`, `Share`, `Admin`, and `Api` (requests from the editor JS).
  - **`Backend`** is called by ONLYOFFICE Docs, not the browser. Its `beforeAction` rejects a missing or invalid `doc` hash with 404, and its actions require a JWT when JWT is enabled.
- **`widgets/EditorWidget`** builds the DocEditor config: download and callback URLs (prefixed with `storageUrl` when it's set), `goback` in new-tab mode, and the JWT `token`.

### Opening the editor

`Module::shouldOpenInNewTab()` (the `openInNewTab` setting, or always on mobile user agents) picks the mode.

- **Modal mode:** Edit/View/Fill links load `/onlyoffice/open` into the `onlyoffice-modal` modal via AJAX. A direct or pjax request instead gets `redirectToModal()`: a redirect to the file's content page (falling back to the home page, or the login page for guests) with a flash script that loads the editor into the modal.
- **New-tab mode:** Edit/View/Fill links get `target="_blank"`. Links followed via pjax (notifications, links in stream posts) are caught by `resources/js/humhub.onlyoffice.newTab.js`, a capture-phase click listener that sets `target="_blank"` before HumHub's pjax handler sees the link. `Events` registers that script on full page loads through its own bundle (`NewTabAssets`), because the main bundle (`Assets`) publishes all of `resources/` with `forceCopy`. `OpenController` also has a server-side fallback for pjax requests (a full-page `window.location.replace`).
- Create and Convert always use the modal.

### Frontend (`resources/humhub.onlyoffice.js`)

A single `humhub.module('onlyoffice')` with the widgets `Editor`, `Convert` and `Share`, plus `createSubmit`.

- `Editor` waits for `window.DocsAPI` (`api.js` from the Docs server). The DocEditor config and instance are kept at module level and overwritten by each `initEditor`, so the editor event handlers act on the most recently opened editor.
- Share's copy link uses the `clipboard` global from HumHub core's clipboard-polyfill (`humhub\assets\ClipboardJsAsset`, registered in `widgets/views/share.php`), the same way core's own copy action does. Unlike `navigator.clipboard`, it also works on non-HTTPS origins.
