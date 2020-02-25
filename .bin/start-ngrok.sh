#!/bin/bash
set -e # Exit with nonzero exit code if anything fails

for ARGUMENT in "$@"
do
    KEY=$(echo "${ARGUMENT}" | cut -f1 -d=)
    VALUE=$(echo "${ARGUMENT}" | cut -f2 -d=)

    case "${KEY}" in
            SHOP_VERSION)              SHOP_VERSION=${VALUE} ;;
            GATEWAY)                   GATEWAY=${VALUE} ;;
            *)
    esac
done

NGROK_ARCHIVE_LINK="https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-amd64.zip"
JQ_LINK="http://stedolan.github.io/jq/download/linux64/jq"

# download and install ngrok
curl -s "${NGROK_ARCHIVE_LINK}" > ngrok.zip
unzip ngrok.zip
chmod +x $PWD/ngrok
# Download json parser for determining ngrok tunnel
curl -sO ${JQ_LINK}
chmod +x "${PWD}"/jq

# Open ngrok tunnel
"${PWD}"/ngrok authtoken "${NGROK_TOKEN}"
TIMESTAMP=$(date +%s)
"${PWD}"/ngrok http 8080 -subdomain="${RANDOM}${TIMESTAMP}${GATEWAY}-presta-${SHOP_VERSION}" > /dev/null &
NGROK_URL_HTTPS=$(curl -s localhost:4040/api/tunnels/command_line | jq --raw-output .public_url)

# allow ngrok to initialize
while [ ! "${NGROK_URL_HTTPS}" ] || [ "${NGROK_URL_HTTPS}" = 'null' ];  do
    echo "Waiting for ngrok to initialize"
    NGROK_URL_HTTPS=$(curl -s localhost:4040/api/tunnels/command_line | jq --raw-output .public_url)

    # replace https with http
    export NGROK_URL=${NGROK_URL_HTTPS//https/http}
    sleep 1
done
