# Change Log

## Added
- editor customization
- validation of server settings on the settings page
- keep intermediate versions when editing (forcesave)
- connect to the demo server
- trial period of 30 days for the demo server
- transfer user region in conversion

## 2.4.0
## Added
- certificate verification setting
- check unsaved changes before closing
- Turkish and Galician empty file templates

## Changed
- document server v6.0 and earlier is no longer supported
- Fixed opening file when maintenance mode
- Added Versioning Support (HumHub v1.10+) 

## 2.3.0
## Added
- support docxf and oform formats
- create blank docxf from creation menu
- "save as" in editor

## 2.2.2
## Fixed
- Fixed JWT

## 2.2.1
## Fixed
- Fixed French and Italian translations

## 2.2.0
## Added
- advanced server settings for specifying internal addresses
- Empty file templates added in multiple new languages
- access for groups to the application

## 2.1.4
## Fixed
- Missing `<?php` tag

## 2.1.3
## Fixed
- HumHub 1.6 compatibility (#17, #19)

## 2.1.2
## Fixed
- Fixed an issue that prevented plugin from removing (#14)


## 2.1.1
## Changed
- Improved migration failures (#12)

## Fixed
- Fixed module id (#12)


## 2.1.0
## Added
- Build script to split readme to according sections (#6)

## Changed
- Logo and screenshots for marketplace
- Updated empty files (v15)

## Fixed
- Fixed marketplace id
- Fixed HumHub minimum version (#5)


## 2.0.0
## Added
- JWT support
- Option to convert `.doc .odt .xls. ods .ppt .odp .txt .csv` files to Office Open XML
- `de`, `fi`, `fr`, `hr`, `hu`, `it`, `ja`, `nl`, `pl`, `pt_br`, `vi` translations

## Changed
- Editors will now use portal language if user language is null
- `author` and `created` fields are now passed correctly
- `firstname` and `lastname` fields are deprecated, using `name` instead
- Allow editing only for Office Open XML formats
- Issue with `/` at the end of Document Service url
- Better way to pass editors config
- Files properly marked as updated now
