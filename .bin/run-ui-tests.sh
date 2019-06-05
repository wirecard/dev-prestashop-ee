#!/bin/bash
set -e # Exit with nonzero exit code if anything fails
#get version
export VERSION=`jq .[0].release SHOPVERSIONS`

#start shopsystem and demoshop
bash .bin/start-shopsystem.sh

# download and install BrowserStackLocal
wget https://www.browserstack.com/browserstack-local/BrowserStackLocal-linux-x64.zip
unzip BrowserStackLocal-linux-x64.zip
./BrowserStackLocal --key ${BROWSERSTACK_ACCESS_KEY} > /dev/null &
sleep 5

composer require --dev $COMPOSER_ARGS codeception/codeception:^2.5

#run tests
cd wirecardpaymentgateway && vendor/bin/codecept run acceptance --env ui_test -g ui_test --html --xml