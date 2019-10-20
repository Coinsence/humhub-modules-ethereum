<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\ethereum\controllers;

use humhub\modules\ethereum\component\HttpStatus;
use humhub\modules\space\models\Space;
use humhub\modules\xcoin\models\Asset;
use Yii;
use humhub\modules\ethereum\component\Utils;
use humhub\modules\xcoin\models\Account;
use humhub\modules\xcoin\models\Transaction;
use yii\base\InvalidConfigException;
use yii\filters\VerbFilter;
use yii\rest\Serializer;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class TransactionController extends BaseRestController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'synchronize' => ['post']
                ],
            ],
        ];
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     */
    public function actionSynchronize()
    {
        $transactionData = Yii::$app->getRequest()->getBodyParams();
        $errors = Utils::checkRequestBody(['fromAddress', 'toAddress', 'coinAddress', 'amount', 'txHash'], $transactionData);

        if (count($errors)) {
            throw new BadRequestHttpException(sprintf('Missing parameters : [%s]', implode(',', $errors)));
        }

        if (Transaction::findOne(['eth_hash' => $transactionData['txHash']])) {
            throw new ForbiddenHttpException('transaction already exists');
        }

        if (
            !($fromAccount = Account::findOne(['ethereum_address' => $transactionData['fromAddress']])) ||
            !($toAccount = Account::findOne(['ethereum_address' => $transactionData['toAddress']])) ||
            !($space = Space::findOne(['coin_address' => $transactionData['coinAddress']])) ||
            $transactionData['amount'] <= 0
        ) {
            throw new ForbiddenHttpException('Invalid transaction data');
        }

        $transaction = new Transaction();

        $transaction->from_account_id = $fromAccount->id;
        $transaction->to_account_id = $toAccount->id;
        $transaction->asset_id = (Asset::findOne(['space_id' => $space->id]))->id;
        $transaction->eth_hash = $transactionData['txHash'];
        $transaction->amount = (int)$transactionData['amount'];
        $transaction->transaction_type = Transaction::TRANSACTION_TYPE_TRANSFER;
        $transaction->comment = 'Mobile app transfer';


        if (!$transaction->save()) {
            $message = implode(' ', array_map(function ($errors) {
                return implode(' ', $errors);
            }, $transaction->getErrors()));

            throw new ServerErrorHttpException(sprintf('Could not save transaction due to : [%s]', $message));
        };

        return $this->jsonResponse(
            HttpStatus::CREATED,
            'Transaction stored successfully',
            ['transaction' => (new Serializer())->serialize($transaction)]
        );
    }
}
