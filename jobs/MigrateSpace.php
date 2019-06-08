<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\ethereum\jobs;

use GuzzleHttp\Exception\GuzzleException;
use humhub\modules\ethereum\calls\Space;
use humhub\modules\ethereum\calls\Wallet;
use humhub\modules\ethereum\component\Utils;
use humhub\modules\space\models\Space as BaseSpace;
use humhub\modules\queue\ActiveJob;
use humhub\modules\xcoin\models\Account;
use yii\base\Exception;

class MigrateSpace extends ActiveJob
{
    /**
     * @var int the space Id
     */
    public $spaceId;


    /**
     * Add space members to DAO & Mint coins for every transaction
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function run()
    {
        $space = BaseSpace::findOne(['id' => $this->spaceId]);

        if ($space == null) {
            return;
        }

        $spaceDefaultAccount = Account::findOne([
            'space_id' => $space->id,
            'account_type' => Account::TYPE_DEFAULT
        ]);

        $asset = Utils::issueSpaceAsset($space);

        $accounts = array();

        foreach ($space->getMemberships()->all() as $memberShip) {

            $defaultAccount = Account::findOne([
                'user_id' => $memberShip->getUser()->one()->id,
                'account_type' => Account::TYPE_DEFAULT,
                'space_id' => null
            ]);

            if (!$defaultAccount->guid) {
                Utils::generateAccountGuid($defaultAccount);
            }

            $accounts [] = [
                'address' => $defaultAccount->ethereum_address,
                'account_id' => $defaultAccount->guid,
                'balance' => (int)$defaultAccount->getAssetBalance($asset),
                'is_member' => true
            ];
        }

        foreach (Account::findAll(['space_id' => $space->id]) as $account) {
            if (!in_array($account->account_type, [Account::TYPE_ISSUE, Account::TYPE_DEFAULT])) {
                if (!$account->guid) {
                    Utils::generateAccountGuid($account);
                }

                $accounts [] = [
                    'address' => $account->ethereum_address,
                    'account_id' => $account->guid,
                    'balance' => (int)$account->getAssetBalance($asset),
                    'is_member' => false
                ];
            }
        }

        // create wallet only for accounts without eth_address
        Wallet::createWallets(array_column(array_filter($accounts, function ($account) {
            return !isset($account['address']);
        }), 'account_id'));

        // update eth_address for accounts without eth_address
        $accounts = array_map(function (&$element) {
            if (!isset($element['address'])) {
                $account = Account::findOne(['guid' => $element['account_id']]);
                $element['address'] = $account->ethereum_address;
            }
        }, $accounts);

        Space::migrate([
            'dao' => $space->dao_address,
            'accountId' => $spaceDefaultAccount->guid,
            'accounts' => array_filter($accounts, function ($account) {
                return $account['balance'] > 0;
            })
        ]);

        $space->updateAttributes(['eth_status' => BaseSpace::ETHEREUM_STATUS_ENABLED]);
    }
}