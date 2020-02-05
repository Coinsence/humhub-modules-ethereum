<?php

/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Ghanmi Mortadha <mortadha.ghanmi56@gmail.com >
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace ethereum\functional;

use ethereum\FunctionalTester;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\xcoin\models\Account;

class EthereumCest
{
    public function testModuleEnabling(FunctionalTester $I)
    {
        $I->wantTo('ensure that ethereum module enabling works');
        $I->amAdmin();

        $I->amOnPage(['admin/module']);
        $I->see('Ethereum');
        $I->sendAjaxPostRequest('/index-test.php?r=/admin/module/enable&moduleId=ethereum');
        $I->amOnPage(['admin/module']);
        $I->see('Ethereum Activated');

    }

    public function testModuleDeactivation(FunctionalTester $I)
    {
        $I->wantTo('ensure that ethereum module deactivation works');
        $I->amAdmin();

        $I->amOnPage(['admin/module']);
        $I->see('Ethereum');
        $I->sendAjaxPostRequest('/index-test.php?r=/admin/module/enable&moduleId=ethereum');
        $I->amOnPage(['admin/module']);
        $I->see('Ethereum Activated');
        $I->sendAjaxPostRequest('/index-test.php?r=/admin/module/disable&moduleId=ethereum');
        $I->amOnPage(['admin/module']);
        $I->dontSee('Ethereum Activated');

    }

    public function testEthereumSpaceEnabling(FunctionalTester $I)
    {
        $I->wantTo('ensure that ethereum enabling works for spaces');
        $I->amAdmin();

        $I->enableEthereumModule();

        $I->enableSpaceModule(1, 'xcoin');

        $space = Space::findOne(['id' => 1]);

        $I->sendAjaxGetRequest($space->createUrl('/xcoin/ethereum/enable'));
        $I->dontSeeRecord(Space::class, [
            'id' => 1,
            'dao_address' => Null,
            'coin_address' => Null,
            'eth_status' => 0
        ]);
        $I->dontSeeRecord(Account::class, [
            'id' => 1,
            'ethereum_address' => Null,
        ]);

    }

    public function testEthereumMigration(FunctionalTester $I)
    {
        $I->wantTo('ensure that ethereum migration process works');
        $I->amAdmin();

        $I->enableEthereumModule();

        $I->enableSpaceModule(1, 'xcoin');

        $space = Space::findOne(['id' => 1]);

        $I->enableSpaceEthereum(1);

        $I->sendAjaxGetRequest($space->createUrl('/xcoin/ethereum/migrate-transactions'));
        // TODO: add some db record asserts


    }

    public function testEthereumBalancesSynchronisation(FunctionalTester $I)
    {
        $I->wantTo('ensure that ethereum synchronize balances process works');
        $I->amAdmin();

        $I->enableEthereumModule();

        $I->enableSpaceModule(1, 'xcoin');

        $space = Space::findOne(['id' => 1]);

        $I->enableSpaceEthereum(1);

        $I->sendAjaxGetRequest($space->createUrl('/xcoin/ethereum/synchronize-balances'));
        // TODO: add some db record asserts


    }

    public function testLoadingEthereumPrivateKey(FunctionalTester $I)
    {
        $I->wantTo('ensure that ethereum private key loading works');
        $I->amAdmin();

        $I->enableEthereumModule();

        $I->enableSpaceModule(1, 'xcoin');
        $I->enableUserModule(1, 'xcoin');

        $owner = User::findOne(['id' => 1]);

        $I->enableSpaceEthereum(1);

        $owner_default_account_id = 4;

        $I->sendAjaxPostRequest($owner->createUrl('/xcoin/ethereum/load-private-key', [
            'accountId' => $owner_default_account_id,
        ]), [
            'DynamicModel[currentPassword]' => 'test'
        ]);
        $I->seeResponseCodeIsSuccessful();

    }

}
