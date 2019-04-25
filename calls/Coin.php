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
use humhub\modules\ethereum\component\Utils;
use humhub\modules\ethereum\Endpoints;
use humhub\modules\space\models\Space;
use humhub\modules\xcoin\models\Account;
use humhub\modules\xcoin\models\Transaction;
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
    public static function issueCoin($event)
    {
        $space = $event->sender;

        if ($space instanceof Space) {

            $defaultAccount = Account::findOne([
                'space_id' => $space->id,
                'account_type' => Account::TYPE_DEFAULT
            ]);

            $coinName = Utils::getCapitalizedSpaceName($space->name);

            $httpClient = new Client(['base_uri' => Endpoints::ENDPOINT_BASE_URI, 'http_errors' => false]);

            $response = $httpClient->request('POST', Endpoints::ENDPOINT_COIN_ISSUE, [
                RequestOptions::JSON => [
                    'accountId' => $defaultAccount->guid,
                    'dao' => $space->dao_address,
                    'name' => $coinName,
                    'symbol' => Utils::getCoinSymbol($coinName),
                    'decimals' => Utils::COIN_DECIMALS
                ]
            ]);

            if ($response->getStatusCode() != HttpStatus::CREATED) {
                throw new HttpException(
                    $response->getStatusCode(),
                    'Could not create ethereum coin for this space, will fix this ASAP !'
                );
            }
        }
    }

    /**
     * @param $event
     * @throws GuzzleException
     * @throws HttpException
     */
    public static function mintCoin($event)
    {
        $transaction = $event->sender;

        if ($transaction instanceof Transaction) {

            $space = Space::findOne([
                'id' => $transaction->getAsset()->one()->id
            ]);

            $recipientAccount = Account::findOne([
                'id' => $transaction->to_account_id,
            ]);

            $defaultAccount = Account::findOne([
                'space_id' => $space->id,
                'account_type' => Account::TYPE_DEFAULT
            ]);

            $httpClient = new Client(['base_uri' => Endpoints::ENDPOINT_BASE_URI, 'http_errors' => false]);

            $response = $httpClient->request('POST', Endpoints::ENDPOINT_COIN_MINT, [
                RequestOptions::JSON => [
                    'accountId' => $defaultAccount->guid,
                    'dao' => $space->dao_address,
                    'recipient' => $recipientAccount->ethereum_address,
                    'amount' => $transaction->amount,
                ]
            ]);

            if ($response->getStatusCode() != HttpStatus::CREATED) {
                throw new HttpException(
                    $response->getStatusCode(),
                    'Could not mint coins for this space, will fix this ASAP !'
                );
            }
        }
    }
}
