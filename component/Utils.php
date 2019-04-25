<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\ethereum\component;

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
        return ucwords($spaceName) . ' ' . self::COIN_SUFFIX;
    }

    public static function getCoinSymbol($coinName)
    {
        $symbol = '';
        foreach (explode(' ', $coinName) as $word) {
            $symbol .= strtoupper($word[0]);
        }

        return $symbol;
    }

    public static function getDefaultDescHash()
    {
        return str_pad('0x', 66, "0", STR_PAD_RIGHT);
    }
}
