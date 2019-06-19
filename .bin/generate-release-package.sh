#!/bin/bash

TARGET_DIRECTORY="wirecardpaymentgateway"

composer install --no-dev

zip -qr ${TARGET_DIRECTORY}.zip ${TARGET_DIRECTORY} -x "*tests*" -x "*Test*" -x "*codeception*"
