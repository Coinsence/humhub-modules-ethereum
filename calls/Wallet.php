<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */


namespace humhub\modules\ethereum\calls;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use humhub\modules\ethereum\component\HttpStatus;
use humhub\modules\ethereum\component\Utils;
use humhub\modules\ethereum\Endpoints;
use humhub\modules\xcoin\models\Account;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * Class Wallet
 */
class Wallet
{
    /**
     * @param $event
     * @throws GuzzleException
     * @throws Exception
     */
    public static function createWallet($event)
    {
        $account = $event->sender;

        if (!$account instanceof Account or $account->account_type == Account::TYPE_ISSUE) {
            return;
        }

        if (!$account->guid) {
            Utils::generateAccountGuid($account);
        }

        BaseCall::__init();

        $response = BaseCall::$httpClient->request('POST', Endpoints::ENDPOINT_WALLET, [
            RequestOptions::JSON => ['accountsIds' => [$account->guid]]
        ]);

        if ($response->getStatusCode() == HttpStatus::CREATED) {
            $body = json_decode($response->getBody()->getContents());
            $account->updateAttributes(['ethereum_address' => reset($body)->address]);
        } else {
            $account->addError(
                'ethereum_address',
                "Sorry, we're facing some problems while creating you're ethereum wallet. We will fix this ASAP !"
            );
        }
    }

    /**
     * @param array $accountsGuids
     * @return bool|void
     * @throws GuzzleException
     */
    public static function createWallets(array $accountsGuids)
    {
        if (!is_array($accountsGuids)) {
            return;
        }

        BaseCall::__init();

        $response = BaseCall::$httpClient->request('POST', Endpoints::ENDPOINT_WALLET, [
            RequestOptions::JSON => ['accountsIds' => $accountsGuids]
        ]);

        if ($response->getStatusCode() == HttpStatus::CREATED) {
            $wallets = json_decode($response->getBody()->getContents());
            foreach ($wallets as $wallet) {
                $account = Account::findOne(['guid' => $wallet->accountId]);
                if ($account) {
                    $account->updateAttributes(['ethereum_address' => $wallet->address]);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param $account
     * @return string
     * @throws GuzzleException
     * @throws HttpException
     */
    public static function getWallet($account)
    {
        BaseCall::__init();

        $response = BaseCall::$httpClient->request('GET', Endpoints::ENDPOINT_WALLET, [
            RequestOptions::JSON => [
                'accountId' => $account->guid,
            ]
        ]);

        if ($response->getStatusCode() == HttpStatus::CREATED) {
            $body = json_decode($response->getBody()->getContents());

            return $body->privateKey;
        } else {
            throw new HttpException(
                $response->getStatusCode(),
                'Could not get wallet PK, will fix this ASAP !'
            );
        }
    }
}
