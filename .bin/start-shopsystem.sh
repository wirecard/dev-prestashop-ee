#!/bin/bash
#set -e # Exit with nonzero exit code if anything fails

export PRESTASHOP_CONTAINER_NAME=prestashop-web
export PRESTASHOP_CONTAINER_DOMAIN=localhost:8080
export PRESTASHOP_CONTAINER_SHOP_URL=localhost:8080

echo "PRESTASHOP_CONTAINER_NAME=prestashop-web" >> ../.env
echo "PRESTASHOP_CONTAINER_DOMAIN=localhost:8080" >> ../.env
echo "PRESTASHOP_CONTAINER_SHOP_URL=localhost:8080" >> ../.env

sudo docker-compose up --force-recreate

# wait for the host to startup
while ! $(curl --output /dev/null --silent --head --fail "http://${PRESTASHOP_CONTAINER_SHOP_URL}/backend/index.php"); do
    echo "Waiting for docker container to initialize"
    sleep 5
done

# install the plugin
sudo docker exec prestashop-web php /var/www/html/bin/console prestashop:module install wirecardpaymentgateway