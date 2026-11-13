# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

ONLYOFFICE connector module for HumHub (module id `onlyoffice`). It opens HumHub files in ONLYOFFICE Docs, which downloads the files from HumHub and posts saves back to it.

## Setup and commands

`git submodule update --init --recursive` is required at runtime: `resources/formats` holds `onlyoffice-docs-formats.json`, which `Module::formats()` reads, and `resources/templates` holds the per-language blank documents used for new files. Don't edit files inside the submodules; they belong to the ONLYOFFICE/document-formats and ONLYOFFICE/document-templates repos. Commit a submodule pointer change only when the task is to update it.

The module has no build step and no `composer.json`; its PHP dependencies (`firebase/php-jwt`, `yiisoft/yii2-httpclient`) come from HumHub core. To run it, place the repo as `onlyoffice` in a HumHub `moduleAutoloadPaths` directory and enable it.

```bash
npm ci && npm run lint     # eslint for resources/**/*.js
```

CI runs eslint and phpcs in `.github/workflows/lint.yml`; read it for the phpcs command, its standards and ignored paths, and the PHP and Node versions. Runtime behaviour needs a live ONLYOFFICE Docs instance that can reach HumHub; verify changes there.

## Branches and releases

- `develop` holds the lowest supported HumHub line. Each `feature/humhub-X.Y` branch supports a newer HumHub core and receives the previous line by merging (`develop` → `feature/humhub-<lowest>` → … → `feature/humhub-<newest>`). The supported core range of a branch is `humhub.minVersion`/`humhub.maxVersion` in its `module.json`.
- PRs target `develop` unless the change needs a newer core API; then it goes on the branch of the lowest core that has the API. Code that depends on a newer core API lives only on the branches that need it; diff the branches to see where they differ.
- `master` holds released code. Merging into `master` tags `v<module.json version>` and builds the GitHub release (`create-tag.yml`, `release.yml`); `marketplace.yml` uploads a tagged release to the HumHub Marketplace.
- Add user-visible changes to the top, unversioned `##` section of `CHANGELOG.md`, under `## Added` or `## Changed`. Don't bump `version` in `module.json` or fill in the header outside a release.
- Commit messages follow Conventional Commits (`fix:`, `feat:`, `ci:`, `chore:`, `refactor:` …). Branch names are `<type>/<short-kebab-description>`.

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
- **New-tab mode:** Edit/View/Fill links get `target="_blank"`. Links followed via pjax (notifications, links in stream posts) are sent to a new tab by `resources/js/humhub.onlyoffice.newTab.js`, with a server-side fallback in `OpenController`. That script loads on every page, so keep it in its own small bundle and out of the main bundle (`Assets`), which republishes all of `resources/` with `forceCopy`.
- Create and Convert always use the modal.

### Frontend (`resources/humhub.onlyoffice.js`)

A single `humhub.module('onlyoffice')` with the widgets `Editor`, `Convert` and `Share`, plus `createSubmit`.

- `Editor` waits for `window.DocsAPI` (`api.js` from the Docs server). The DocEditor config and instance are kept at module level and overwritten by each `initEditor`, so the editor event handlers act on the most recently opened editor.
- Share's copy link uses HumHub core's clipboard polyfill when the core provides it (see `widgets/views/share.php`), because `navigator.clipboard` fails on non-HTTPS origins.
- `resources/js/humhub.onlyoffice.cspViolation.js` is loaded before Docs' `api.js` and stops HumHub from reloading the page on the CSP violation that `api.js` triggers with its `eval` check.

## Rules

- `BackendController` routes are protected by two independent checks: the `doc` hash (signed with HumHub's `secret` setting) and the Docs JWT when a Docs secret is set. Any change to these routes must keep both. The `doc` hash has no expiry on purpose: Docs reuses one callback URL for the whole session.
- Build every URL handed to Docs the way `EditorWidget::getConfig()` does: with a `doc` hash and the `storageUrl` prefix when it's set. Send every request to Docs through `Module::request()`, `commandService()` or `convertService()`, so the internal Docs address, certificate setting, proxy options and JWT header apply.
- Never write a file's content without handling `onlyoffice_key`: clear it when the content changes outside an editing session, keep it (with `onlyoffice_key_lock`) during a forcesave.
- No inline JavaScript in views or controllers: HumHub's CSP uses a `script-src` nonce. Put JS in `resources/`, register it through an asset bundle, and pass data through `JsWidget::getData()`.
- `README.md` headings *Features*, *Installing ONLYOFFICE…*, *Configuring ONLYOFFICE…* and *How it works* are used by `.github/actions/pack` to build the Marketplace docs. Renaming them breaks the release.
- A new top-level dev file (tooling config, docs for developers) must be added to the excludes in `.github/actions/pack/action.yml`.
- A new setting needs: a property, rule and label in `models/ConfigureForm`, entries in `loadSettings()` and `save()`, the field in `views/admin/index.php`, and a getter in `Module`.
- A schema change needs a new migration in `migrations/` and, for removal on uninstall, a change in `migrations/uninstall.php`.

## Coding practices

- Before writing new code, check whether HumHub core already provides it through a public API (file handlers, HumHub's modal widgets, `ui.modal`, `client`, permissions). Use it and keep the change small.
- Write code for the lowest PHP version that the branch's `humhub.minVersion` supports. Every core API you use must exist in that version and must still exist in the newest supported one; check the core migration guide (`docs/develop/module-migrate-<version>.md` in humhub/humhub) for anything replaced or deprecated.
- Read and write file content only through the file storage API of the branch's core, as the existing controllers do. Don't add direct filesystem access: on newer cores the storage may not be local.
- Check access with HumHub's own methods, as `BaseFileController` does, in every controller action that takes a file reference from the request.
- Change an existing `File` record with `updateAttributes()`; `save()` is only for new records.
- Before relying on an ONLYOFFICE Docs API option, event or command, check in the Docs API documentation which Docs version added it. The oldest Docs version the module accepts is checked in `initEditor()` in `resources/humhub.onlyoffice.js`.
- Keep `Module::formats()` the only source of supported extensions; don't hardcode format lists.

## Conventions

- PHP and JS files, except `messages/` and `migrations/`, carry the Ascensio System SIA copyright header; files that came from HumHub also keep the HumHub header above it. Copy the header from an existing file onto any new one.
- PHP follows the standards the phpcs step in `lint.yml` checks. JS follows `eslint.config.mjs`. Frontend code is jQuery with HumHub's JS module system (`humhub.module`, `require('client')`, `Widget`, `data-action-*` attributes).
- Translatable strings use `Yii::t('OnlyofficeModule.base', …)`. Add each new string to every `messages/*/base.php` and translate it yourself; a human reviewer checks the translations. Strings that HumHub core already translates may use the core category. JS has no translations of its own: pass user-visible text to JS already translated, through the widget's `getData()`.
- Read module settings through the `Module` getters; only `ConfigureForm` reads and writes `->settings` directly. Keep event handler logic in `Events.php`.
- Comments are rare: one line, a formal impersonal sentence, only where the code can't show the intent. A workaround is described as "Stops X from Y by Z".
