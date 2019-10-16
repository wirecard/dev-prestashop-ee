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
$PWD/ngrok http 8080 -subdomain="${RANDOM}${TIMESTAMP}${GATEWAY}-presta-${PRESTASHOP_RELEASE_VERSION}" > /dev/null &
NGROK_URL_HTTPS=$(curl -s localhost:4040/api/tunnels/command_line | jq --raw-output .public_url)

# allow ngrok to initialize
while [ ! ${NGROK_URL_HTTPS} ] || [ ${NGROK_URL_HTTPS} = 'null' ];  do
    echo "Waiting for ngrok to initialize"
    NGROK_URL_HTTPS=$(curl -s localhost:4040/api/tunnels/command_line | jq --raw-output .public_url)

    # replace https with http
    export NGROK_URL=$(sed 's/https/http/g' <<< "$NGROK_URL_HTTPS")
    echo $NGROK_URL
    sleep 1
done

#start shopsystem and demoshop
bash .bin/start-shopsystem.sh

composer require --dev $COMPOSER_ARGS codeception/codeception:^2.5

#run tests
cd wirecardpaymentgateway && vendor/bin/codecept run acceptance --env ui_test -g ui_test --html --xml
