#!/bin/bash
rm -rf ./deploy
rm ./RELEASE.md

cwd=$(pwd)
git submodule update --init --recursive

mkdir -p ./deploy/onlyoffice
rsync -av --exclude='deploy' ./ ./deploy/onlyoffice
cd ./deploy/onlyoffice

rm -rf ./.github
rm -rf ./.git/
rm .gitignore
rm .gitmodules
rm -rf ./resources/formats/.git
rm -rf ./resources/templates/.git
rm ./pack.sh

mkdir docs

mv ./LICENCE.md ./docs/LICENCE.md
mv ./AUTHORS.md ./docs/AUTHORS.md
mv ./CHANGELOG.md ./docs/CHANGELOG.md

node pack.js
rm ./pack.js
rm ./README.md

cd $cwd
awk '/## [0-9]/{p++} p; /## [0-9]/{if (p > 1) exit}' CHANGELOG.md | awk 'NR>2 {print last} {last=$0}' > RELEASE.md