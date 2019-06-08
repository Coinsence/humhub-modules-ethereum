<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\ethereum\component;

use humhub\components\Event;
use humhub\libs\UUID;
use humhub\modules\space\models\Space;
use humhub\modules\xcoin\models\Account;
use humhub\modules\xcoin\models\Asset;
use yii\base\Exception;

/**
 * Class Utils
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */
class Utils
{
    const COIN_SUFFIX = 'Coin';
    const COIN_DECIMALS = 18;

    public static function getCapitalizedSpaceName($spaceName)
    {
        if (preg_match('/coin$/', strtolower($spaceName))) {
            return ucwords($spaceName);
        }

        return ucwords($spaceName) . ' ' . self::COIN_SUFFIX;
    }

    public static function getCoinSymbol($coinName)
    {
        $symbol = '';
        foreach (explode(' ', $coinName) as $word) {
            if (!empty($word)) {
                $symbol .= strtoupper($word[0]);
            }
        }

        return $symbol;
    }

    public static function getDefaultDescHash()
    {
        return str_pad('0x', 66, "0", STR_PAD_RIGHT);
    }

    public static function createDefaultAccount($entity)
    {
        if ($entity instanceof Space) {

            $account = new Account();
            $account->title = 'Default';
            $account->space_id = $entity->id;
            $account->account_type = Account::TYPE_DEFAULT;
            $account->save();

            Event::trigger(Account::class, Account::EVENT_DEFAULT_SPACE_ACCOUNT_CREATED, new Event(['sender' => $entity]));
        } else {
            $account = new Account();
            $account->title = 'Default';
            $account->user_id = $entity->id;
            $account->account_type = Account::TYPE_DEFAULT;
            $account->save();
        }
    }

    public static function issueSpaceAsset(Space $space)
    {
        if (!$asset = Asset::findOne(['space_id' => $space->id])) {
            $asset = new Asset();
            $asset->title = 'DEFAULT';
            $asset->space_id = $space->id;
            $asset->save();
        }

        return $asset;
    }

    /**
     * @param Account $account
     * @throws Exception
     */
    public static function generateAccountGuid(Account $account)
    {
        $account->updateAttributes(['guid' => UUID::v4()]);
    }
}
