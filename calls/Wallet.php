<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */


namespace humhub\modules\ethereum\calls;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use humhub\modules\ethereum\component\HttpStatus;
use humhub\modules\ethereum\Endpoints;
use humhub\modules\xcoin\models\Account;

/**
 * Class Wallet
 */
class Wallet
{
    const WALLET_CREATION_SUCCESS = 'walletCreationSuccess';

    /**
     * @param $event
     * @throws GuzzleException
     */
    public static function createWallet($event)
    {
        $account = $event->sender;

        if ($account instanceof Account) {
            $httpClient = new Client(['base_uri' => Endpoints::ENDPOINT_BASE_URI]);

            $response = $httpClient->request('POST', Endpoints::ENDPOINT_WALLET, [
                RequestOptions::JSON => ['accountId' => $account->guid]
            ]);

            if ($response->getStatusCode() == HttpStatus::CREATED) {
                $body = json_decode($response->getBody()->getContents());
                $account->updateAttributes(['ethereum_address' => $body->address]);
                $account->mnemonic = $body->mnemonic;
            }
        }
    }
}