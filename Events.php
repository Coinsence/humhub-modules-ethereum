<?php

namespace humhub\modules\ethereum;

use Yii;

/**
 * Class Events
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */
class Events
{
    public static function onBeforeRequest($event)
    {
        Yii::$app->urlManager->addRules([
            [
                'pattern' => 'ethereum/transaction/synchronize',
                'route' => 'ethereum/transaction/synchronize',
                'verb' => ['POST']
            ]
        ], true);
    }
}
