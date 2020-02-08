<?php

/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Ghanmi Mortadha <mortadha.ghanmi56@gmail.com >
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace ethereum;

use GuzzleHttp\Exception\GuzzleException;
use humhub\components\Event;
use humhub\modules\ethereum\calls\Coin;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\xcoin\helpers\AccountHelper;
use humhub\modules\xcoin\models\Account;
use humhub\modules\xcoin\models\Asset;
use humhub\modules\xcoin\models\Transaction;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends \FunctionalTester
{
    use _generated\FunctionalTesterActions;

    /**
     * Define custom actions here
     */

    public function enableEthereumModule()
    {

        $this->amOnPage(['admin/module']);
        $this->see('Ethereum');
        $this->sendAjaxPostRequest('/index-test.php?r=/admin/module/enable&moduleId=ethereum');
        $this->amOnPage(['admin/module']);
        $this->see('Ethereum Activated');

    }

    public function disableEthereumModule()
    {

        $this->amOnPage(['admin/module']);
        $this->see('Ethereum Activated');
        $this->sendAjaxPostRequest('/index-test.php?r=/admin/module/disable&moduleId=ethereum');
        $this->amOnPage(['admin/module']);
        $this->dontSee('Ethereum Activated');

    }

    public function enableSpaceModule($spaceId = null, $moduleId)
    {

        if ($spaceId == null) {
            return;
        }

        $space = Space::findOne(['id' => $spaceId]);
        $space->enableModule($moduleId);
        \Yii::$app->moduleManager->flushCache();

    }

    public function enableUserModule($userId = null, $moduleId)
    {

        if ($userId == null) {
            return;
        }

        $user = User::findOne(['id' => $userId]);
        if (!$user->isModuleEnabled($moduleId)) {
            $user->enableModule($moduleId);
        }
        \Yii::$app->moduleManager->flushCache();

    }

    public function enableSpaceEthereum($spaceId = null)
    {

        if ($spaceId == null) {
            return;
        }

        $space = Space::findOne(['id' => $spaceId]);
        $this->sendAjaxGetRequest($space->createUrl('/xcoin/ethereum/enable'));
        $this->dontSeeRecord(Space::class, [
            'id' => 1,
            'dao_address' => Null,
            'coin_address' => Null,
            'eth_status' => 0
        ]);
        $this->dontSeeRecord(Account::class, [
            'id' => 1,
            'ethereum_address' => Null,
        ]);

    }

    /**
     * @param $space
     * @param $amount
     * @throws Exception
     * @throws GuzzleException
     * @throws HttpException
     */
    public function mintCoins($space, $amount)
    {

        if (!$space instanceof Space || !is_float($amount)) {
            return;
        }

        if(!$asset = Asset::findOne(['space_id' => $space->id])){
            return;
        };

        $issueAccount = AccountHelper::getIssueAccount($asset->space);
        $defaultAccount = Account::findOne(['space_id' => $space->id, 'account_type' => Account::TYPE_DEFAULT]);

        $transaction = new Transaction();
        $transaction->transaction_type = Transaction::TRANSACTION_TYPE_ISSUE;
        $transaction->asset_id = $asset->id;
        $transaction->from_account_id = $issueAccount->id;
        $transaction->to_account_id = $defaultAccount->id;
        $transaction->amount = $amount;

        Coin::mintCoin(new Event(['sender' => $transaction]));

        $this->dontSeeRecord(Transaction::class, [
            'asset_id' => $asset->id,
            'from_account_id' =>  $issueAccount->id,
            'to_account_id' => $defaultAccount->id,
            'amount' => $amount,
            'eth_hash' => Null
        ]);

    }
}
