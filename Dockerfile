FROM prestashop/prestashop:1.7-7.2-apache

ARG PRESTASHOP_CONTAINER_NAME=0
ARG PRESTASHOP_CONTAINER_DOMAIN=0
ARG PRESTASHOP_CONTAINER_SHOP_URL=0

ENV PRESTASHOP_CONTAINER_NAME=$PRESTASHOP_CONTAINER_NAME
ENV PRESTASHOP_CONTAINER_DOMAIN=$PRESTASHOP_CONTAINER_DOMAIN
ENV PRESTASHOP_CONTAINER_SHOP_URL=$PRESTASHOP_CONTAINER_SHOP_URL

COPY --chown=www-data:www-data ./wirecardpaymentgateway.zip /tmp
RUN unzip /tmp/wirecardpaymentgateway.zip -d /var/www/html/modules/

# Alternative base image
#FROM prestashop/prestashop-git
# We might have to build prestashop via cli/use the other docker image because there arent all versions available on prestashop/prestashop
# http://doc.prestashop.com/display/PS16/Installing+PrestaShop+using+the+command-line+script