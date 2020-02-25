#!/bin/bash
#set -e # Exit with nonzero exit code if anything fails

set -a
source .env

for ARGUMENT in "$@"
do
    KEY=$(echo "${ARGUMENT}" | cut -f1 -d=)
    VALUE=$(echo "${ARGUMENT}" | cut -f2 -d=)

    case "${KEY}" in
            NGROK_URL)                              NGROK_URL=${VALUE} ;;
            SHOP_VERSION)                           PS_VERSION=${VALUE} ;;
            IS_LATEST_EXTENSION_RELEASE)            IS_LATEST_EXTENSION_RELEASE=${VALUE} ;;
            LATEST_RELEASED_SHOP_EXTENSION_VERSION) LATEST_RELEASED_SHOP_EXTENSION_VERSION=${VALUE} ;;
            *)
    esac
done


#set +a

# remove http or https from the link
export PS_CONTAINER_DOMAIN=${NGROK_URL#*//}
export PS_CONTAINER_SHOP_URL=${PS_CONTAINER_DOMAIN}


if [[ ${IS_LATEST_EXTENSION_RELEASE}  == "1" ]]; then
    # switch to desired version if we are testing specific release
    git checkout tags/${LATEST_RELEASED_SHOP_EXTENSION_VERSION}
fi
# used to change the compatibility version to match the prestashop version
# has to be done before generate-release-package.sh is executed
replace="s/^\s*\$this->ps_versions_compliancy = array.*$/\$this->ps_versions_compliancy = array('min' => '${PS_VERSION}', 'max' => '${PS_VERSION}');/"
sed -i -e "$replace" "./wirecardpaymentgateway/wirecardpaymentgateway.php"

# generate release package
.bin/generate-release-package.sh
docker-compose build --no-cache --build-arg PS_CONTAINER_NAME="${PS_CONTAINER_NAME}" \
                                --build-arg PS_CONTAINER_DOMAIN="${PS_CONTAINER_DOMAIN}" \
                                --build-arg PS_CONTAINER_SHOP_URL="${PS_CONTAINER_SHOP_URL}" \
                                --build-arg PS_VERSION="${PS_VERSION}" \
                                prestashop.web

docker-compose up -d prestashop.database
sleep 15
docker-compose ps
docker-compose up -d prestashop.web
docker-compose ps

# wait for the host to startup
while ! $(curl --output /dev/null --silent  --head --fail "http://${PS_CONTAINER_SHOP_URL}/backend/index.php"); do
    echo "Waiting for docker container to initialize"
    ((c++)) && ((c==50)) && break
    sleep 5
done

# install the plugin
docker exec "${PS_CONTAINER_NAME}" /var/www/html/bin/console prestashop:module install wirecardpaymentgateway

##configure enable payment method settings
#for paymentMethod in $(jq -r 'keys | .[]' wirecardpaymentgateway/tests/_data/PaymentMethodData.json); do
#    docker exec --env PS_DB_PASSWORD="${PS_DB_PASSWORD}" \
#                --env PS_DB_SERVER="${PS_DB_SERVER}" \
#                --env PS_DB_NAME="${PS_DB_NAME}" \
#                --env GATEWAY="${GATEWAY}" \
#                "${PS_CONTAINER_NAME}" bash -c "cd /var/www/html/_data && php configure_payment_method_db.php '$paymentMethod' pay"
#done

