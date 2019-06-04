<?php

namespace humhub\modules\ethereum\calls;

use GuzzleHttp\Client;
use humhub\modules\ethereum\Endpoints;
use Yii;

/**
 * Class BaseCall
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */
class BaseCall
{
    /**
     * @var Client
     */
    static $httpClient;

    public static function __init()
    {
        return self::$httpClient = new Client([
            'base_uri' => Endpoints::ENDPOINT_BASE_URI,
            'http_errors' => false,
            'headers' => [
                'Authorization' => "Basic ". base64_encode(Yii::$app->params['apiCredentials'])
            ]
        ]);
    }
}
