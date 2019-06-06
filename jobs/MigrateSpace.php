<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\ethereum\jobs;

use GuzzleHttp\Exception\GuzzleException;
use humhub\modules\ethereum\calls\Coin;
use humhub\modules\ethereum\calls\Space;
use humhub\modules\ethereum\component\Utils;
use humhub\modules\space\MemberEvent;
use humhub\modules\space\models\Space as BaseSpace;
use humhub\modules\queue\ActiveJob;
use humhub\modules\xcoin\models\Transaction;
use yii\base\Event;
use yii\base\Exception;
use yii\web\HttpException;

class MigrateSpace extends ActiveJob
{
    /**
     * @var int the space Id
     */
    public $spaceId;


    /**
     * Add space members to DAO & Mint coins for every transaction
     *
     * @throws GuzzleException
     * @throws Exception
     * @throws HttpException
     */
    public function run()
    {
        $space = BaseSpace::findOne(['id' => $this->spaceId]);

        if ($space == null) {
            return;
        }

        // add space members to space dao
        foreach ($space->getMemberships()->all() as $memberShip) {

            $memberShipEvent = new MemberEvent([
                'space' => $space, 'user' => $memberShip->getUser()->one()
            ]);

            Space::addMember($memberShipEvent);
        }

        $asset = Utils::issueSpaceAsset($space);

        $transactions = Transaction::findAll([
            'asset_id' => $asset->id,
        ]);

        foreach ($transactions as $transaction) {
            $transactionEvent = new Event(['sender' => $transaction]);
            if ($transaction->transaction_type == Transaction::TRANSACTION_TYPE_ISSUE) {
                // mint coins for each issue transaction of the space
                Coin::mintCoin($transactionEvent);
            } else {
                //transfer coins for each coin holder
                Coin::transferCoin($transactionEvent);
            }
        }

        $space->updateAttributes(['eth_status' => 2]);
    }
}
