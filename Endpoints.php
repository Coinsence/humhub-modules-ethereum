<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\ethereum;

/**
 * Class Endpoints
 *
 * This class contains all exposed endpoints in the nodejs rest api
 * responsible for humhub <-> ethereum interactions.
 */
class Endpoints
{
    const ENDPOINT_BASE_URI = 'http://localhost:3000';

    // rest api endpoints list
    const ENDPOINT_WALLET = '/wallet';
    const ENDPOINT_DAO = '/dao';
    const ENDPOINT_COIN_ISSUE = '/coin/issue';
    const ENDPOINT_COIN_MINT = '/coin/mint';
    const ENDPOINT_COIN_TRANSFER = '/coin/transfer';
}
