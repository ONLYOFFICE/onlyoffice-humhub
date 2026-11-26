# Maintaining

Release steps for the ONLYOFFICE module for HumHub. The workflows live in `.github/workflows` and run on GitHub Actions.

## Releasing a version

`master` holds the released code of the lowest supported HumHub line, which is developed on `develop`.

1. On `develop`, set `version` in `module.json`. Replace the empty `##` header at the top of `CHANGELOG.md` with `## <version>`.
2. Open a `Release/<version>` PR from `develop` into `master`. `artifact.yml` builds the module package for the PR; download it to check what will ship.
3. Merge the PR. `create-tag.yml` tags the merge commit `v<version>`. It doesn't run when the push changes only `.github/`, `AUTHORS.md`, `LICENCE.md` or `README.md`.
4. The tag starts `release.yml`. It builds `onlyoffice.zip` with `.github/actions/pack` and fails if the tag doesn't match the `module.json` version. It then creates the GitHub release, using the first `## <version>` section of `CHANGELOG.md` as the release notes.
5. To publish to the HumHub Marketplace, run *Publish to HumHub Marketplace* (`marketplace.yml`) manually with the tag, e.g. `v3.3.0`. It uploads `onlyoffice.zip` from that GitHub release. It needs the `HUMHUB_MARKETPLACE_API_KEY` secret in the `marketplace` environment.

## Package contents

`.github/actions/pack` copies the repository into the package, except the files it excludes. Add any new top-level development file to its excludes.

It splits `README.md` into the Marketplace docs by heading:

| Headings | Marketplace file |
| --- | --- |
| `# ONLYOFFICE module for HumHub`, `## Features` | `docs/README.md` |
| `## Installing ONLYOFFICE…`, `## Configuring ONLYOFFICE…` | `docs/INSTALLATION.md` |
| `## How it works` | `docs/DEVELOPER.md` |

If any of these headings is renamed, the matching file comes out empty and the build fails.
