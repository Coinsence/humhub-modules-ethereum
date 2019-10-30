<?php

declare(strict_types=1);

namespace humhub\modules\ethereum\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Class BaseRestController
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */
class BaseRestController extends Controller
{
    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return parent::beforeAction($action);
    }

    public function jsonResponse(int $code, string $message, array $data)
    {
        return [
            'code' => $code,
            'message' => $message,
            'response' => $data
        ];
    }
}
