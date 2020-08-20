#!/bin/bash

TARGET_DIRECTORY="wirecardpaymentgateway"
AUTOINDEX_DIRECTORY="$PWD/autoindex"
CURRENT_DIRECTORY=$PWD

composer install --no-dev

git clone https://github.com/jmcollin/autoindex.git ${AUTOINDEX_DIRECTORY}
cp ${PWD}/wirecardpaymentgateway/index.php ${AUTOINDEX_DIRECTORY}/sources/index.php
cd ${AUTOINDEX_DIRECTORY}
php index.php ${CURRENT_DIRECTORY}/wirecardpaymentgateway
cd ${CURRENT_DIRECTORY}

zip -qr ${TARGET_DIRECTORY}.zip ${TARGET_DIRECTORY} -x "*test*" -x "*Test*" -x "*codeception*"
