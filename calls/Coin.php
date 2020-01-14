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
use humhub\components\Event;
use humhub\modules\ethereum\component\HttpStatus;
use humhub\modules\ethereum\component\Utils;
use humhub\modules\ethereum\Endpoints;
use humhub\modules\space\models\Space;
use humhub\modules\xcoin\models\Account;
use humhub\modules\xcoin\models\Asset;
use humhub\modules\xcoin\models\Transaction;
use yii\base\Exception;
use yii\web\HttpException;


/**
 * Class Coin
 */
class Coin
{
    /**
     * @param $event
     * @throws GuzzleException
     * @throws HttpException
     */
    public static function mintCoin($event)
    {
        $transaction = $event->sender;

        if (!$transaction instanceof Transaction) {
            return;
        }

        $asset = Asset::findOne(['id' => $transaction->asset_id]);
        $space = Space::findOne(['id' => $asset->space_id]);

        if (!$space->dao_address) {
            return;
        }

        $recipientAccount = Account::findOne(['id' => $transaction->to_account_id,]);
        $defaultAccount = Account::findOne([
            'space_id' => $space->id,
            'account_type' => Account::TYPE_DEFAULT
        ]);

        BaseCall::__init();

        $response = BaseCall::$httpClient->request('POST', Endpoints::ENDPOINT_COIN_MINT, [
            RequestOptions::JSON => [
                'accountId' => $defaultAccount->guid,
                'dao' => $space->dao_address,
                'recipient' => $recipientAccount->ethereum_address,
                'amount' => Utils::formatAmount($transaction->amount),
            ]
        ]);

        if ($response->getStatusCode() != HttpStatus::CREATED) {
            throw new HttpException(
                $response->getStatusCode(),
                'Could not mint coins for this space, will fix this ASAP !'
            );
        }
    }

    /**
     * @param $event
     * @throws GuzzleException
     * @throws HttpException
     * @throws Exception
     */
    public static function transferCoin($event)
    {
        $transaction = $event->sender;

        if (!$transaction instanceof Transaction) {
            return;
        }

        $asset = Asset::findOne(['id' => $transaction->asset_id]);
        $space = Space::findOne(['id' => $asset->space_id]);

        if (!$space->dao_address) {
            return;
        }

        $recipientAccount = Account::findOne(['id' => $transaction->to_account_id,]);
        $senderAccount = Account::findOne([$transaction->from_account_id,]);

        if ($recipientAccount->account_type == Account::TYPE_ISSUE || $senderAccount->account_type == Account::TYPE_ISSUE) {
            return;
        }

        if (!$recipientAccount->ethereum_address) {
            Wallet::createWallet(new Event(['sender' => $recipientAccount]));
            sleep(Utils::REQUEST_DELAY);
        }


        if (!$senderAccount->ethereum_address) {
            Wallet::createWallet(new Event(['sender' => $senderAccount]));
            sleep(Utils::REQUEST_DELAY);
        }

        BaseCall::__init();

        $response = BaseCall::$httpClient->request('POST', Endpoints::ENDPOINT_COIN_TRANSFER, [
            RequestOptions::JSON => [
                'accountId' => $senderAccount->guid,
                'dao' => $space->dao_address,
                'to' => $recipientAccount->ethereum_address,
                'amount' => Utils::formatAmount($transaction->amount),
            ]
        ]);

        if ($response->getStatusCode() == HttpStatus::CREATED) {
            $body = json_decode($response->getBody()->getContents());
            $transaction->updateAttributes(['eth_hash' => $body->hash]);
        } else {
            throw new HttpException(
                $response->getStatusCode(),
                'Could not do transfer coins, will fix this ASAP !'
            );
        }
    }

    /**
     * @param $account
     * @param $space
     * @return string
     * @throws GuzzleException
     * @throws HttpException
     */
    public static function getBalance($account, $space)
    {
        BaseCall::__init();

        $response = BaseCall::$httpClient->request('GET', Endpoints::ENDPOINT_COIN_BALANCE, [
            RequestOptions::JSON => [
                'owner' => $account->ethereum_address,
                'accountId' => $account->guid,
                'dao' => $space->dao_address,
            ]
        ]);

        if ($response->getStatusCode() == HttpStatus::CREATED) {
            $body = json_decode($response->getBody()->getContents());

            return hexdec($body->balance);
        } else {
            throw new HttpException(
                $response->getStatusCode(),
                'Could not get balance for giving account, will fix this ASAP !'
            );
        }
    }

    /**
     * @param $space
     * @throws GuzzleException
     * @throws HttpException
     */
    public static function initTransferListener($space)
    {
        if (!$space instanceof Space) {
            return;
        }

        $defaultAccount = Account::findOne([
            'space_id' => $space->id,
            'account_type' => Account::TYPE_DEFAULT
        ]);

        BaseCall::__init();

        $response = BaseCall::$httpClient->request('POST', Endpoints::ENDPOINT_COIN_INIT_TRANSFER_LISTENER, [
            RequestOptions::JSON => [
                'accountId' => $defaultAccount->guid,
                'dao' => $space->dao_address,
            ]
        ]);

        if ($response->getStatusCode() != HttpStatus::CREATED) {
            throw new HttpException(
                $response->getStatusCode(),
                'Could not start transfer listener for space coin, will fix this ASAP !'
            );
        }
    }
}
