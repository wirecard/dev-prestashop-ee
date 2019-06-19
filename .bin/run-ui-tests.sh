#!/bin/bash
set -e # Exit with nonzero exit code if anything fails
#get version
export VERSION=`jq .[0].release SHOPVERSIONS`

# download and install ngrok
curl -s https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-amd64.zip > ngrok.zip
unzip ngrok.zip
chmod +x $PWD/ngrok
# Download json parser for determining ngrok tunnel
curl -sO http://stedolan.github.io/jq/download/linux64/jq
chmod +x $PWD/jq


# Open ngrok tunnel
$PWD/ngrok authtoken $NGROK_TOKEN
TIMESTAMP=$(date +%s)
$PWD/ngrok http 8080 -subdomain="${TIMESTAMP}${GATEWAY}-${PRESTASHOP_RELEASE_VERSION}" > /dev/null &
NGROK_URL=$(curl -s localhost:4040/api/tunnels/command_line | jq --raw-output .public_url)

# allow ngrok to initialize
while [ ! ${NGROK_URL} ] || [ ${NGROK_URL} = 'null' ];  do
    echo "Waiting for ngrok to initialize"
    export NGROK_URL=$(curl -s localhost:4040/api/tunnels/command_line | jq --raw-output .public_url)
    sleep 1
done


#start shopsystem and demoshop
bash .bin/start-shopsystem.sh

## download and install BrowserStackLocal
#wget https://www.browserstack.com/browserstack-local/BrowserStackLocal-linux-x64.zip
#unzip BrowserStackLocal-linux-x64.zip
#./BrowserStackLocal --key ${BROWSERSTACK_ACCESS_KEY} > /dev/null &
#sleep 5

composer require --dev $COMPOSER_ARGS codeception/codeception:^2.5

#run tests
cd wirecardpaymentgateway && vendor/bin/codecept run acceptance --env ui_test -g ui_test --html --xml