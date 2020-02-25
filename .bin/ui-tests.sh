                            #!/bin/bash

set -a
source .env
set +a

for ARGUMENT in "$@"
do
    KEY=$(echo "${ARGUMENT}" | cut -f1 -d=)
    VALUE=$(echo "${ARGUMENT}" | cut -f2 -d=)

    case "${KEY}" in
            NGROK_URL)               NGROK_URL=${VALUE} ;;
            GIT_BRANCH)              GIT_BRANCH=${VALUE} ;;
            TRAVIS_PULL_REQUEST)     TRAVIS_PULL_REQUEST=${VALUE} ;;
            SHOP_VERSION)            SHOP_VERSION=${VALUE} ;;
            *)
    esac
done

SHOP_SYSTEM=prestashop

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

UI_TEST_ENV="-e SHOP_SYSTEM=${SHOP_SYSTEM}
              -e SHOP_URL=${NGROK_URL}
              -e SHOP_VERSION=${SHOP_VERSION}
              -e EXTENSION_VERSION=${GIT_BRANCH}
              -e DB_HOST=${DB_HOST}
              -e DB_PORT=${MYSQL_PORT_OUT}
              -e DB_NAME=${PRESTASHOP_DB_NAME}
              -e DB_USER=${PRESTASHOP_DB_USER}
              -e DB_PASSWORD=${DB_PASSWORD}"

docker run --rm -it --volume $(pwd):/app prooph/composer:7.2 require wirecard/shopsystem-ui-testsuite:dev-TPWDCEE-5876-configuration --no-dev

docker run -v "${PWD}/wirecardpaymentgateway/vendor/wirecard/shopsystem-ui-testsuite":/project "${UI_TEST_ENV}" codeception/codeception run acceptance -g "${TEST_GROUP}" -g "${SHOP_SYSTEM}"  --env ci --html --xml
