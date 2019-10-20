<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\ethereum;

use \humhub\components\Module as BaseModule;
use Yii;
use yii\web\JsonParser;

class Module extends BaseModule
{
    public function disable()
    {
        return parent::disable();
    }

    public function enable()
    {
        return parent::enable();
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = 'json';
        Yii::$app->request->setBodyParams(null);
        Yii::$app->request->parsers['application/json'] = JsonParser::class;
        return parent::beforeAction($action);
    }

}
