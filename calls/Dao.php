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
use humhub\modules\space\models\Space;
use humhub\modules\xcoin\models\Account;
use yii\web\HttpException;


/**
 * Class Dao
 */
class Dao
{
    /**
     * @param $event
     * @throws GuzzleException
     * @throws HttpException
     */
    public static function createDao($event)
    {
        $space = $event->sender;

        if (!$space instanceof Space or $space->dao_address) {
            return;
        }

        $coinName = Utils::getCapitalizedSpaceName($space->name);

        $defaultAccount = Account::findOne([
            'space_id' => $space->id,
            'account_type' => Account::TYPE_DEFAULT
        ]);

        BaseCall::__init();

        $response = BaseCall::$httpClient->request('POST', Endpoints::ENDPOINT_DAO, [
            RequestOptions::JSON => [
                'accountId' => $defaultAccount->guid,
                'spaceName' => $space->name,
                'descHash' => Utils::getDefaultDescHash(),
                'coinName' => $coinName,
                'coinSymbol' => Utils::getCoinSymbol($coinName),
                'coinDecimals' => Utils::COIN_DECIMALS
            ]
        ]);

        if ($response->getStatusCode() == HttpStatus::CREATED) {
            $body = json_decode($response->getBody()->getContents());
            $space->updateAttributes(['dao_address' => $body->daoAddress]);
            $space->updateAttributes(['coin_address' => $body->apps[1]->proxy]);
        } else {
            throw new HttpException($response->getStatusCode(), 'Could not create DAO for this space, will fix this ASAP !');
        }
    }
}
