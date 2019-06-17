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
use humhub\modules\ethereum\calls\Space;
use humhub\modules\space\models\Space as BaseSpace;
use humhub\modules\queue\ActiveJob;
use Yii;

class MigrateSpace extends ActiveJob
{
    /**
     * @var int the space Id
     */
    public $spaceId;

    /**
     * @var array the space data to migrate on ethereum
     */
    public $data;

    /**
     * Add space members to DAO & Mint coins
     *
     * @throws GuzzleException
     */
    public function run()
    {
        $space = BaseSpace::findOne(['id' => $this->spaceId]);

        if ($space == null or !is_array($this->data)) {
            return;
        }

        try {
            Space::migrate($this->data);
        } catch (\Exception $exception) {
            Yii::warning("Exception when migrating space {$space->name} : {$exception->getMessage()}", 'cron');
        }

        $space->updateAttributes(['eth_status' => BaseSpace::ETHEREUM_STATUS_ENABLED]);

        Yii::warning("Ethereum enabling success for space : {$space->name}", 'cron');
    }
}
