#!/bin/bash
#set -e # Exit with nonzero exit code if anything fails

export PRESTASHOP_CONTAINER_NAME=prestashop-web
export PRESTASHOP_CONTAINER_DOMAIN=localhost:8080
export PRESTASHOP_CONTAINER_SHOP_URL=localhost:8080
export PRESTASHOP_CONTAINER_VERSION=1.7

# used to change the compatibility version to match the prestashop version
# has to be done before generate-release-package.sh is executed
replace="s/^\s*\$this->ps_versions_compliancy = array.*$/\$this->ps_versions_compliancy = array('min' => '${PRESTASHOP_CONTAINER_VERSION}', 'max' => '${PRESTASHOP_CONTAINER_VERSION}');/"
sed -i -e "$replace" "./wirecardpaymentgateway/wirecardpaymentgateway.php"
# in case you want to use a different language for the replace, a positive lookbehind can be used so you just have to replace the content of array()
# (?<=\$this->ps_versions_compliancy = array).*

# generate release package
. .bin/generate-release-package.sh
#

docker-compose build --no-cache --build-arg PRESTASHOP_CONTAINER_NAME=${PRESTASHOP_CONTAINER_NAME} --build-arg PRESTASHOP_CONTAINER_DOMAIN=${PRESTASHOP_CONTAINER_DOMAIN} --build-arg PRESTASHOP_CONTAINER_SHOP_URL=${PRESTASHOP_CONTAINER_SHOP_URL} --build-arg PRESTASHOP_CONTAINER_VERSION={$PRESTASHOP_CONTAINER_VERSION} prestashop.web
docker-compose up --force-recreate -d

# wait for the host to startup
while ! $(curl --output /dev/null --silent --head --fail "http://${PRESTASHOP_CONTAINER_SHOP_URL}/backend/index.php"); do
    echo "Waiting for docker container to initialize"
    ((c++)) && ((c==50)) && break
    sleep 5
done

# install the plugin
docker exec prestashop-web php /var/www/html/bin/console prestashop:module install wirecardpaymentgateway