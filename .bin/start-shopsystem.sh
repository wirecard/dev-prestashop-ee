#!/bin/bash
#set -e # Exit with nonzero exit code if anything fails

set -a
source .env
set +a

# remove http or https from the link
export PRESTASHOP_CONTAINER_DOMAIN=${NGROK_URL#*//}
export PRESTASHOP_CONTAINER_SHOP_URL=${PRESTASHOP_CONTAINER_DOMAIN}
export PRESTASHOP_CONTAINER_VERSION=${PRESTASHOP_VERSION}

# used to change the compatibility version to match the prestashop version
# has to be done before generate-release-package.sh is executed
replace="s/^\s*\$this->ps_versions_compliancy = array.*$/\$this->ps_versions_compliancy = array('min' => '${PRESTASHOP_CONTAINER_VERSION}', 'max' => '${PRESTASHOP_CONTAINER_VERSION}');/"
sed -i -e "$replace" "./wirecardpaymentgateway/wirecardpaymentgateway.php"

# generate release package
.bin/generate-release-package.sh

docker-compose build --no-cache --build-arg PRESTASHOP_CONTAINER_NAME=${PRESTASHOP_CONTAINER_NAME} \
                                --build-arg PRESTASHOP_CONTAINER_DOMAIN=${PRESTASHOP_CONTAINER_DOMAIN} \
                                --build-arg PRESTASHOP_CONTAINER_SHOP_URL=${PRESTASHOP_CONTAINER_SHOP_URL} \
                                --build-arg PRESTASHOP_CONTAINER_VERSION=${PRESTASHOP_CONTAINER_VERSION} \
                                prestashop.web
docker-compose up --force-recreate -d

# wait for the host to startup
while ! $(curl --output /dev/null --silent --head --fail "http://${PRESTASHOP_CONTAINER_SHOP_URL}/backend/index.php"); do
    echo "Waiting for docker container to initialize"
    ((c++)) && ((c==50)) && break
    sleep 5
done

# install the plugin
docker exec ${PRESTASHOP_CONTAINER_NAME} /var/www/html/bin/console prestashop:module install wirecardpaymentgateway

#configure enable credit card settings
#reserve means authorization
docker exec --env PRESTASHOP_DB_PASSWORD=${PRESTASHOP_DB_PASSWORD} \
            --env PRESTASHOP_DB_SERVER=${PRESTASHOP_DB_SERVER} \
            --env PRESTASHOP_DB_NAME=${PRESTASHOP_DB_NAME} \
            --env GATEWAY=${GATEWAY} \
            ${PRESTASHOP_CONTAINER_NAME} bash -c "cd /var/www/html/_data && php configure_payment_method_db.php creditcard reserve"
