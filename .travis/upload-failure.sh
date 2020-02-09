#!/usr/bin/env sh

# -e = exit when one command returns != 0, -v print each command before executing
set -ev

# find _output folder and add to zip
find tests/ -type d -name "_output" -exec zip -r --exclude="*.gitignore" failure.zip {} +

# add logs to failure.zip
zip -ur coinsence-ethereum-travis-${TRAVIS_JOB_NUMBER}.zip ${HUMHUB_PATH}/protected/runtime/logs || true
zip -ur coinsence-ethereum-travis-${TRAVIS_JOB_NUMBER}.zip /tmp/phpserver.log || true

# upload file
curl -F "file=@coinsence-ethereum-travis-${TRAVIS_JOB_NUMBER}.zip" -s -w "\n"  https://file.io

# delete zip
rm coinsence-ethereum-travis-${TRAVIS_JOB_NUMBER}.zip
