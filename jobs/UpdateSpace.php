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
use humhub\modules\ethereum\calls\Dao;
use humhub\modules\space\models\Space as BaseSpace;
use humhub\modules\queue\ActiveJob;
use humhub\modules\xcoin\models\Account;
use Yii;

class UpdateSpace extends ActiveJob
{
    /**
     * @var int the space Id
     */
    public $spaceId;

    /**
     * @var int the space default account Guid
     */
    public $accountGuid;

    /**
     * Update space dao address & coin address
     * @throws GuzzleException
     */
    public function run()
    {
        $space = BaseSpace::findOne(['id' => $this->spaceId]);
        $account = Account::findOne(['guid' => $this->accountGuid]);

        if ($space == null) {
            return;
        }

        try {
            Dao::getDetails($space->id, $account->guid);
        } catch (\Exception $exception) {
            Yii::warning("Exception when updating space {$space->name} details : {$exception->getMessage()}", 'cron');
        }

        Yii::warning("Space {$space->name} details updated successfully", 'cron');
    }
}
