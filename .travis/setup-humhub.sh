#!/usr/bin/env sh

# -e = exit when one command returns != 0, -v print each command before executing
set -ev

old=$(pwd)

cd ..
git clone --depth 1 https://github.com/Coinsence/humhub-modules-xcoin

mkdir ${HUMHUB_PATH}
cd ${HUMHUB_PATH}

git clone --depth 1 https://github.com/Coinsence/humhub.git .
composer install --prefer-dist --no-interaction
composer require --dev php-coveralls/php-coveralls

npm install
grunt build-assets

cd ${HUMHUB_PATH}/protected/humhub/tests

set +v
sed -i -e "s|'installed' => true,|'installed' => true,\n\t'moduleAutoloadPaths' => ['$(dirname $old)'],\n\t'defaultAssetName' => '$DEFAULTASSETNAME',\n\t'apiCredentials' => '$APICREDENTIALS',\n\t'ethereum_api_base_uri' => '$ETHEREUMAPIBASEURI'|g" config/common.php
set -v
#cat config/common.php

mysql -e 'CREATE DATABASE humhub_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
php codeception/bin/yii migrate/up --includeModuleMigrations=1 --interactive=0
mysql -e 'USE humhub_test; INSERT INTO module_enabled (module_id) VALUES ("xcoin");'
php codeception/bin/yii migrate/up --includeModuleMigrations=1 --interactive=0
php codeception/bin/yii installer/auto
php codeception/bin/yii search/rebuild

php ${HUMHUB_PATH}/protected/vendor/bin/codecept build
