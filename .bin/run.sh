#!/bin/bash
set -e # Exit with nonzero exit code if anything fails
SHOP_SYSTEM="prestashop"
NGROK_SUBDOMAIN="${RANDOM}${TIMESTAMP}-${SHOP_SYSTEM}-${SHOP_VERSION}"
export NGROK_URL="http://${NGROK_SUBDOMAIN}.ngrok.io"

bash .bin/start-ngrok.sh SUBDOMAIN="${NGROK_SUBDOMAIN}"

echo "NGROK_URL in the run script: ${NGROK_URL}"
#start shopsystem and demoshop
bash .bin/start-shopsystem.sh NGROK_URL="${NGROK_URL}" \
                            SHOP_VERSION="${PRESTASHOP_VERSION}" \
                            IS_LATEST_EXTENSION_RELEASE="${IS_LATEST_EXTENSION_RELEASE}" \
                            LATEST_RELEASED_SHOP_EXTENSION_VERSION="${LATEST_RELEASED_SHOP_EXTENSION_VERSION}"


bash .bin/ui-tests.sh NGROK_URL="${NGROK_URL}" \
                      SHOP_SYSTEM="${SHOP_SYSTEM}" \
                      SHOP_VERSION="${PRESTASHOP_VERSION}" \
                      GIT_BRANCH="${TRAVIS_BRANCH}" \
                      TRAVIS_PULL_REQUEST="${TRAVIS_PULL_REQUEST}" \
                      BROWSERSTACK_USER="${BROWSERSTACK_USER}" \
                      BROWSERSTACK_ACCESS_KEY="${BROWSERSTACK_ACCESS_KEY}"
