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
use humhub\modules\user\models\User;
use humhub\modules\xcoin\models\Account;
use humhub\modules\space\models\Space as BaseSpace;
use yii\web\HttpException;

/**
 * Class Space
 */
class Space
{
    /**
     * @param $event
     * @throws GuzzleException
     * @throws HttpException
     */
    public static function details($event)
    {
        $space = $event->sender;

        if (!$space->dao_address) {
            return;
        }

        $spaceDefaultAccount = Account::findOne([
            'space_id' => $space->id,
            'account_type' => Account::TYPE_DEFAULT
        ]);

        $httpClient = new Client(['base_uri' => Endpoints::ENDPOINT_BASE_URI, 'http_errors' => false]);

        $response = $httpClient->request('GET', Endpoints::ENDPOINT_SPACE, [
            RequestOptions::JSON => [
                'accountId' => $spaceDefaultAccount->guid,
                'dao' => $space->dao_address,
            ]
        ]);

        if ($response->getStatusCode() == HttpStatus::OK) {
            $body = json_decode($response->getBody()->getContents());
            $space->updateAttributes(['coin_address' => $body->coin]);
        } else {
            throw new HttpException(
                $response->getStatusCode(),
                'Could not do get ethereum space details, will fix this ASAP !'
            );
        }
    }

    /**
     * @param $event
     * @throws GuzzleException
     * @throws HttpException
     */
    public static function addMember($event)
    {
        $space = $event->space;
        $member = $event->user;

        if (
            !$space instanceof BaseSpace ||
            !$member instanceof User ||
            !$space->isModuleEnabled('xcoin') ||
            $space->id == 1 // space with id = 1 is "Welcome Space" (this is the best way to check since it's the first space automatically created)
        ) {
            return;
        }

        $userDefaultAccount = Account::findOne([
            'user_id' => $member->id,
            'account_type' => Account::TYPE_DEFAULT,
            'space_id' => null
        ]);

        if (!$userDefaultAccount) {
            Utils::createDefaultAccount($member);
        }

        $spaceDefaultAccount = Account::findOne([
            'space_id' => $space->id,
            'account_type' => Account::TYPE_DEFAULT
        ]);

        if (!$spaceDefaultAccount) {
            Utils::createDefaultAccount($space);
        }

        $httpClient = new Client(['base_uri' => Endpoints::ENDPOINT_BASE_URI, 'http_errors' => false]);

        $response = $httpClient->request('POST', Endpoints::ENDPOINT_SPACE_ADD_MEMBER, [
            RequestOptions::JSON => [
                'accountId' => $spaceDefaultAccount->guid,
                'dao' => $space->dao_address,
                'members' => [$userDefaultAccount->ethereum_address]
            ]
        ]);

        if ($response->getStatusCode() != HttpStatus::CREATED) {
            throw new HttpException(
                $response->getStatusCode(),
                'Could not add member to this space, will fix this ASAP !'
            );
        }
    }

    /**
     * @param $event
     * @throws GuzzleException
     * @throws HttpException
     */
    public static function leaveSpace($event)
    {
        $space = $event->space;
        $member = $event->user;

        if (
            !$space instanceof BaseSpace ||
            !$member instanceof User ||
            !$space->isModuleEnabled('xcoin') ||
            $space->id == 1 // space with id = 1 is "Welcome Space" (this is the best way to check since it's the first space automatically created)
        ) {
            return;
        }

        $userDefaultAccount = Account::findOne([
            'user_id' => $member->id,
            'account_type' => Account::TYPE_DEFAULT,
            'space_id' => null
        ]);

        $httpClient = new Client(['base_uri' => Endpoints::ENDPOINT_BASE_URI, 'http_errors' => false]);

        $response = $httpClient->request('POST', Endpoints::ENDPOINT_SPACE_LEAVE_SPACE, [
            RequestOptions::JSON => [
                'accountId' => $userDefaultAccount->guid,
                'dao' => $space->dao_address,
            ]
        ]);

        if ($response->getStatusCode() != HttpStatus::CREATED) {
            throw new HttpException(
                $response->getStatusCode(),
                'Could not remove member from this space, will fix this ASAP !'
            );
        }
    }
}
