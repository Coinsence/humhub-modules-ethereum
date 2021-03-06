<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

use humhub\modules\space\models\Membership;
use humhub\modules\xcoin\controllers\EthereumController;
use humhub\modules\xcoin\models\Account;
use humhub\modules\xcoin\models\Transaction;
use yii\db\ActiveRecord;
use yii\web\Application;

return [
    'id' => 'ethereum',
    'class' => 'humhub\modules\ethereum\Module',
    'namespace' => 'humhub\modules\ethereum',
    'events' => [
        [
            'class' => Application::class,
            'event' => Application::EVENT_BEFORE_REQUEST,
            'callback' => ['\humhub\modules\ethereum\Events', 'onBeforeRequest']
        ],
        [
            'class' => Account::class,
            'event' => ActiveRecord::EVENT_BEFORE_VALIDATE,
            'callback' => ['humhub\modules\ethereum\calls\Wallet', 'createWallet']
        ],
        [
            'class' => Account::class,
            'event' => 'defaultSpaceAccountCreated',
            'callback' => ['humhub\modules\ethereum\calls\Dao', 'createDao']
        ],
        [
            'class' => Transaction::class,
            'event' => 'transactionTypeIssue',
            'callback' => ['humhub\modules\ethereum\calls\Coin', 'mintCoin']
        ],
        [
            'class' => Transaction::class,
            'event' => 'transactionTypeTransfer',
            'callback' => ['humhub\modules\ethereum\calls\Coin', 'transferCoin']
        ],
        [
            'class' => Membership::class,
            'event' => 'memberAdded',
            'callback' => ['humhub\modules\ethereum\calls\Space', 'addMember']
        ],
        [
            'class' => Membership::class,
            'event' => 'memberLeaved',
            'callback' => ['humhub\modules\ethereum\calls\Space', 'leaveSpace']
        ],
        [
            'class' => Membership::class,
            'event' => 'memberRemoved',
            'callback' => ['humhub\modules\ethereum\calls\Space', 'removeMember']
        ],
        [
            'class' => EthereumController::class,
            'event' => 'enableEthereum',
            'callback' => ['humhub\modules\ethereum\calls\Space', 'enable']
        ],
        [
            'class' => EthereumController::class,
            'event' => 'migrateMissingTransactions',
            'callback' => ['humhub\modules\ethereum\calls\Space', 'migrateMissingTransactions']
        ],
        [
            'class' => EthereumController::class,
            'event' => 'synchronizeBalances',
            'callback' => ['humhub\modules\ethereum\calls\Space', 'synchronizeBalances']
        ],
    ],
];

