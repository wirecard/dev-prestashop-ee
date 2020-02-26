#!/bin/bash
set -e # Exit with nonzero exit code if anything fails

bash .bin/start-ngrok.sh  SHOP_VERSION="${PRESTASHOP_VERSION}"

#start shopsystem and demoshop
bash .bin/start-shopsystem.sh NGROK_URL="${NGROK_URL}" \
                            SHOP_VERSION="${PRESTASHOP_VERSION}" \
                            IS_LATEST_EXTENSION_RELEASE="${IS_LATEST_EXTENSION_RELEASE}" \
                            LATEST_RELEASED_SHOP_EXTENSION_VERSION="${LATEST_RELEASED_SHOP_EXTENSION_VERSION}"


bash .bin/ui-tests.sh NGROK_URL="${NGROK_URL}" \
                      SHOP_SYSTEM="prestashop" \
                      SHOP_VERSION="${PRESTASHOP_VERSION}" \
                      GIT_BRANCH="${TRAVIS_BRANCH}" \
                      TRAVIS_PULL_REQUEST="${TRAVIS_PULL_REQUEST}"
