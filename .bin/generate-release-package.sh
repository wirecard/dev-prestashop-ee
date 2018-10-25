#!/bin/bash

TARGET_DIRECTORY="wirecardpaymentgateway.zip"

composer install --no-dev
zip -r wirecardpaymentgateway.zip ${TARGET_DIRECTORY}
