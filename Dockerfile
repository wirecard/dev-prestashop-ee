FROM prestashop/prestashop:1.7-7.2-apache

COPY --chown=www-data:www-data ./wirecardpaymentgateway.zip /tmp
RUN unzip /tmp/wirecardpaymentgateway.zip -d /var/www/html/modules/

# Alternative base image
#FROM prestashop/prestashop-git
# We might have to build prestashop via cli/use the other docker image because there arent all versions available on prestashop/prestashop
# http://doc.prestashop.com/display/PS16/Installing+PrestaShop+using+the+command-line+script