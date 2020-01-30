<?php

/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Ghanmi Mortadha <mortadha.ghanmi56@gmail.com >
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace tests\codeception\unit\modules\ethereum;

use humhub\modules\ethereum\component\Utils;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\xcoin\models\Account;
use humhub\modules\xcoin\models\Asset;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Exception;

class UtilsTest extends HumHubDbTestCase
{
    public function testCapitalizedSpaceName()
    {

        $spaceName1 = 'my first Test space';
        $spaceName2 = 'my second test space Coin';

        $capitalizedSpaceName1 = Utils::getCapitalizedSpaceName($spaceName1);
        $capitalizedSpaceName2 = Utils::getCapitalizedSpaceName($spaceName2);

        $this->assertEquals('My First Test Space Coin', $capitalizedSpaceName1);
        $this->assertEquals('My Second Test Space Coin', $capitalizedSpaceName2);

    }

    public function testCoinSymbol()
    {

        $capitalizedSpaceName = 'My First Test Space Coin';

        $coinSymbol = Utils::getCoinSymbol($capitalizedSpaceName);

        $this->assertEquals('MFTSC', $coinSymbol);

    }

    public function testDefaultDescHash()
    {

        $descHash = Utils::getDefaultDescHash();

        $this->assertEquals('0x0000000000000000000000000000000000000000000000000000000000000000', $descHash);

    }

    public function testCreateDefaultAccount()
    {

        //----------- Space Default Account -----------//
        $space = Space::findOne(['id' => 1]);

        $account = Utils::createDefaultAccount($space);

        $this->assertEquals(Account::class, get_class($account));
        $this->assertEquals($account->title, 'Default');
        $this->assertEquals($account->space_id, $space->id);
        $this->assertEquals($account->account_type, Account::TYPE_DEFAULT);

        //----------- User Default Account -----------//
        $user = User::findOne(['id' => 1]);

        $account = Utils::createDefaultAccount($user);

        $this->assertEquals(Account::class, get_class($account));
        $this->assertEquals($account->title, 'Default');
        $this->assertEquals($account->user_id, $user->id);
        $this->assertEquals($account->account_type, Account::TYPE_DEFAULT);

    }

    public function testIssueSpaceAsset()
    {

        $space = Space::findOne(['id' => 4]);

        $asset = Utils::issueSpaceAsset($space);

        $this->assertEquals(Asset::class, get_class($asset));
        $this->assertEquals($asset->title, 'DEFAULT');
        $this->assertEquals($asset->space_id, $space->id);

    }

    public function testAccountGuidGeneration()
    {

        $account = Account::findOne(['id' => 1]);
        $account->updateAttributes(['guid' => null]);

        $this->assertNull($account->guid);

        try {
            Utils::generateAccountGuid($account);
        } catch (Exception $e) {
        }

        $this->assertNotNull($account->guid);

        $this->assertRegExp('/([a-z0-9]+\-){4}[a-z0-9]+/', $account->guid);

    }

    public function testIsXcoinModuleEnabled()
    {

        $space = Space::findOne(['id' => 2]);

        $space->enableModule('xcoin');
        $this->assertTrue(Utils::isXcoinEnabled($space));

        $space->disableModule('xcoin');
        $this->assertFalse(Utils::isXcoinEnabled($space));

        // when space->id = 1 , this Utils::isXcoinEnabled must return false even the module is enabled
        $space = Space::findOne(['id' => 1]);
        $space->enableModule('xcoin');
        $this->assertFalse(Utils::isXcoinEnabled($space));

    }

    public function testIsEthereumEnabled()
    {

        $space = Space::findOne(['id' => 2]);
        $space->enableModule('xcoin');

        $this->assertFalse(Utils::isEthereumEnabled($space));

        $space->updateAttributes(['eth_status' => Space::ETHEREUM_STATUS_ENABLED]);

        $this->assertTrue(Utils::isEthereumEnabled($space));

    }

    public function testCheckRequestBody()
    {

        $keys = ['a', 'b', 'c'];
        $data = ['a' => 'a', 'b' => 'b', 'c' => 'c'];

        $this->assertCount(0, Utils::checkRequestBody($keys, $data));

        $keys = ['a', 'b', 'c'];
        $data = ['a' => 'a', 'b' => 'b', 'test'];


        $this->assertCount(1, Utils::checkRequestBody($keys, $data));

    }

}
