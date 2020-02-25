FROM prestashop/base:7.2-apache

# Set default value in case there is not any available version in release file, if there is, this value will be overwritten
ARG PS_VERSION=1.7.5.2
ENV PS_VERSION=$PS_VERSION

ADD "https://www.prestashop.com/download/old/prestashop_$PS_VERSION.zip" /tmp/prestashop.zip

RUN mkdir -p /tmp/data-ps \
	&& unzip -q /tmp/prestashop.zip -d /tmp/data-ps/ \
	&& bash /tmp/ps-extractor.sh /tmp/data-ps \
	&& rm /tmp/prestashop.zip

RUN docker-php-ext-install mysqli
ARG PS_CONTAINER_NAME=0
ARG PS_CONTAINER_DOMAIN=0
ARG PS_CONTAINER_SHOP_URL=0

ENV PS_CONTAINER_NAME=$PS_CONTAINER_NAME
ENV PS_CONTAINER_DOMAIN=$PS_CONTAINER_DOMAIN
ENV PS_CONTAINER_SHOP_URL=$PS_CONTAINER_SHOP_URL

COPY --chown=www-data:www-data ./wirecardpaymentgateway.zip /tmp
RUN unzip -q /tmp/wirecardpaymentgateway.zip -d /var/www/html/modules/
ADD wirecardpaymentgateway/tests/_data/ /var/www/html/_data
