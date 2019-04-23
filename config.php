<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

use humhub\modules\xcoin\models\Account;
use yii\db\ActiveRecord;

return [
    'id' => 'ethereum',
    'class' => 'humhub\modules\ethereum\Module',
    'namespace' => 'humhub\modules\ethereum',
    'events' => [
        [
            'class' => Account::class,
            'event' => ActiveRecord::EVENT_AFTER_INSERT,
            'callback' => ['humhub\modules\ethereum\calls\Wallet', 'createWallet']
        ],
    ],
];

