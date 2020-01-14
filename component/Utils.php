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

    const DELAY_HALF_MINUTE = 60 / 2;
    const DELAY_ONE_MINUTE = 60 * 1;
    const DELAY_TWO_MINUTES = 60 * 2;
    const DELAY_THREE_MINUTES = 60 * 3;
    const DELAY_FOUR_MINUTES = 60 * 4;
    const DELAY_FIVE_MINUTES = 60 * 5;

    const REQUEST_DELAY = 15; // wait 15 seconds after requests specially with POST /wallet that need to be funded with 0.1 eth

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

    public static function createDefaultAccount($entity, $runValidation = true)
    {
        if ($entity instanceof Space) {

            $account = new Account();
            $account->title = 'Default';
            $account->space_id = $entity->id;
            $account->account_type = Account::TYPE_DEFAULT;
            $account->save($runValidation);

            Event::trigger(Account::class, Account::EVENT_DEFAULT_SPACE_ACCOUNT_CREATED, new Event(['sender' => $entity]));

            return $account;
        } else {
            $account = new Account();
            $account->title = 'Default';
            $account->user_id = $entity->id;
            $account->account_type = Account::TYPE_DEFAULT;
            $account->save($runValidation);

            return $account;
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

    static function isXcoinEnabled($space)
    {
        return
            $space instanceof Space &&
            $space->isModuleEnabled('xcoin') &&
            $space->id !== 1; // space with id = 1 is "Welcome Space" (this is the best way to check since it's the first space automatically created)
    }

    static function isEthereumEnabled($space)
    {
        return self::isXcoinEnabled($space) && $space->eth_status == Space::ETHEREUM_STATUS_ENABLED;
    }

    static function checkRequestBody(array $keys, array $data)
    {
        $errors = [];

        foreach ($keys as $key) {
            if (!isset($data[$key]) && !array_key_exists($key, $data)) {
                $errors[] = $key;
            }
        }

        return $errors;
    }

    static function formatAmount($amount)
    {
        return number_format($amount, 1, '.', '');
    }
}
