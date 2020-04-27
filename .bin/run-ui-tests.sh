#!/bin/bash
set -e # Exit with nonzero exit code if anything fails

set -a
source .env
set +a

for ARGUMENT in "$@"; do
  KEY=$(echo "${ARGUMENT}" | cut -f1 -d=)
  VALUE=$(echo "${ARGUMENT}" | cut -f2 -d=)

  case "${KEY}" in
  NGROK_URL) NGROK_URL=${VALUE} ;;
  GIT_BRANCH) GIT_BRANCH=${VALUE} ;;
  TRAVIS_PULL_REQUEST) TRAVIS_PULL_REQUEST=${VALUE} ;;
  SHOP_SYSTEM) SHOP_SYSTEM=${VALUE} ;;
  SHOP_VERSION) SHOP_VERSION=${VALUE} ;;
  BROWSERSTACK_USER) BROWSERSTACK_USER=${VALUE} ;;
  BROWSERSTACK_ACCESS_KEY) BROWSERSTACK_ACCESS_KEY=${VALUE} ;;
  *) ;;
  esac
done

# if tests triggered by PR, use different Travis variable to get branch name
if [ "${TRAVIS_PULL_REQUEST}" != "false" ]; then
  export GIT_BRANCH="${TRAVIS_PULL_REQUEST_BRANCH}"
fi

# find out test group to be run
if [[ $GIT_BRANCH =~ ${PATCH_RELEASE} ]]; then
  TEST_GROUP="${PATCH_RELEASE}"
elif [[ $GIT_BRANCH =~ ${MINOR_RELEASE} ]]; then
  TEST_GROUP="${MINOR_RELEASE}"
# run all tests in nothing else specified
else
  TEST_GROUP="${MAJOR_RELEASE}"
fi
# run tests
composer require wirecard/shopsystem-ui-testsuite:dev-master

docker-compose run \
  -e SHOP_SYSTEM="${SHOP_SYSTEM}" \
  -e SHOP_URL="${NGROK_URL}" \
  -e SHOP_VERSION="${SHOP_VERSION}" \
  -e EXTENSION_VERSION="${GIT_BRANCH}" \
  -e DB_HOST="${PS_DB_SERVER}" \
  -e DB_NAME="${PS_DB_NAME}" \
  -e DB_USER="${PS_DB_USER}" \
  -e DB_PASSWORD="${PS_DB_PASSWORD}" \
  -e BROWSERSTACK_USER="${BROWSERSTACK_USER}" \
  -e BROWSERSTACK_ACCESS_KEY="${BROWSERSTACK_ACCESS_KEY}" \
  codecept run acceptance \
  -g "${TEST_GROUP}" -g "${SHOP_SYSTEM}" \
  --env ci --html --xml
