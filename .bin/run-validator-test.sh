#!/bin/bash
PHANTOM="phantomjs-2.1.1-linux-x86_64"
PHANTOM_ARCHIVE="${PHANTOM}.tar.bz2"

#bash .bin/generate-release-package.sh
export PACKAGE="wirecardpaymentgateway.zip"
export REPORT_FILE="$(pwd)/report.html"
mkdir wirecardpaymentgateway/tests/_data/
cp $(pwd)/${PACKAGE} wirecardpaymentgateway/tests/_data/

#get phantomjs
wget "https://bitbucket.org/ariya/phantomjs/downloads/${PHANTOM_ARCHIVE}"
tar x -f ${PHANTOM_ARCHIVE}

# starting phantom js
${PHANTOM}/bin/phantomjs --webdriver=4444 > /dev/null 2>&1 &
#make sure phantomjs is running
echo "console.log('Start PhantomJs');
phantom.exit();" > test.js
${PHANTOM}/bin/phantomjs test.js


#run validator  tests
cd wirecardpaymentgateway
vendor/bin/codecept run acceptance --env validator -g validator --steps

#process validator test results
cd ..
python .bin/process-validator-report.py