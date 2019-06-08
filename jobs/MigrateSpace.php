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
use humhub\modules\xcoin\models\Transaction;
use Yii;
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
                'accountId' => $defaultAccount->guid,
                'balance' => (int)$defaultAccount->getAssetBalance($asset),
                'isMember' => true
            ];
        }

        foreach (Transaction::find()->where(['asset_id' => $asset->id])->distinct('to_account_id')->all() as $transaction) {

            $account = Account::findOne(['id' => $transaction->to_account_id]);

            if ($account && $account->account_type != Account::TYPE_ISSUE) {
                if (!$account->guid) {
                    Utils::generateAccountGuid($account);
                }

                $accounts [] = [
                    'address' => $account->ethereum_address,
                    'accountId' => $account->guid,
                    'balance' => (int)$account->getAssetBalance($asset),
                    'isMember' => false
                ];
            }
        }

        try {
            // create wallet only for accounts without eth_address
            Wallet::createWallets(array_column(array_filter($accounts, function ($account) {
                return !isset($account['address']);
            }), 'accountId'));
        } catch (\Exception $exception) {
            Yii::warning("Exception when creating wallets for space {$space} : {$exception->getMessage()}", 'cron');
        }

        // update eth_address for accounts without eth_address
        $accounts = array_map(function (&$element) {
            if (!isset($element['address'])) {
                $account = Account::findOne(['guid' => $element['accountId']]);
                $element['address'] = $account->ethereum_address;
            }

            return $element;
        }, $accounts);

        try {
            Space::migrate([
                'dao' => $space->dao_address,
                'accountId' => $spaceDefaultAccount->guid,
                'accounts' => $accounts
            ]);
        } catch (\Exception $exception) {
            Yii::warning("Exception when migrating space {$space} : {$exception->getMessage()}", 'cron');
        }

        $space->updateAttributes(['eth_status' => BaseSpace::ETHEREUM_STATUS_ENABLED]);

        Yii::warning("Ethereum enabling success for space : {$space->name}", 'cron');
    }
}
