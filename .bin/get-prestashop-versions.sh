#!/bin/bash

curl -H "Authorization: token ${GITHUB_TOKEN}" https://api.github.com/repos/prestashop/prestashop/releases | jq -r '.[] | .tag_name' | egrep -v [a-zA-Z] | grep -v "^1.6" | head -n3 > tmp.txt

sort -nr tmp.txt > ${PRESTASHOP_RELEASES_FILE}

if [[ $(git diff HEAD ${PRESTASHOP_RELEASES_FILE}) != '' ]]; then
    git config --global user.name "Travis CI"
    git config --global user.email "wirecard@travis-ci.org"
    git add  ${PRESTASHOP_RELEASES_FILE}
    git commit -m "[skip ci] Update latest shop releases"
    git push --quiet https://${GITHUB_TOKEN}@github.com/${TRAVIS_REPO_SLUG} HEAD:master
fi