#!/bin/bash
#set -e # Exit with nonzero exit code if anything fails

#move this to its own script
#

export PRESTASHOP_CONTAINER_NAME=prestashop-web
export PRESTASHOP_CONTAINER_DOMAIN=localhost:8080
export PRESTASHOP_CONTAINER_SHOP_URL=localhost:8080

#echo "PRESTASHOP_CONTAINER_NAME=prestashop-web" >> ../.env
#echo "PRESTASHOP_CONTAINER_DOMAIN=localhost:8080" >> ../.env
#echo "PRESTASHOP_CONTAINER_SHOP_URL=localhost:8080" >> ../.env

docker-compose build --no-cache --build-arg PRESTASHOP_CONTAINER_NAME=${PRESTASHOP_CONTAINER_NAME} --build-arg PRESTASHOP_CONTAINER_DOMAIN=${PRESTASHOP_CONTAINER_DOMAIN} --build-arg PRESTASHOP_CONTAINER_SHOP_URL=${PRESTASHOP_CONTAINER_SHOP_URL} prestashop.web
docker-compose up --force-recreate -d

# wait for the host to startup
while ! $(curl --output /dev/null --silent --head --fail "http://${PRESTASHOP_CONTAINER_SHOP_URL}/backend/index.php"); do
    echo "Waiting for docker container to initialize"
    sleep 5
done

# install the plugin
docker exec prestashop-web php /var/www/html/bin/console prestashop:module install wirecardpaymentgateway