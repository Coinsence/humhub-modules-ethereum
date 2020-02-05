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

use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\xcoin\models\Account;

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
        if($spaceId == null) {
            return;
        }

        $space = Space::findOne(['id' => $spaceId]);
        $space->enableModule($moduleId);
        \Yii::$app->moduleManager->flushCache();
    }

    public function enableUserModule($userId = null, $moduleId)
    {
        if($userId == null) {
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

        if($spaceId == null) {
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

}
