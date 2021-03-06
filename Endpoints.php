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
    // rest api endpoints list
    const ENDPOINT_WALLET = '/wallet';
    const ENDPOINT_DAO = '/dao';
    const ENDPOINT_COIN_MINT = '/coin/mint';
    const ENDPOINT_COIN_TRANSFER = '/coin/transfer';
    const ENDPOINT_COIN_BALANCE = '/coin/balance';
    const ENDPOINT_COIN_INIT_TRANSFER_LISTENER = '/coin/setTransferEventListener';
    const ENDPOINT_SPACE_ADD_MEMBER = '/space/addMembers';
    const ENDPOINT_SPACE_REMOVE_MEMBER = '/space/removeMember';
    const ENDPOINT_SPACE_LEAVE_SPACE = '/space/leave';
    const ENDPOINT_SPACE_MIGRATE = '/migrate/space';
}
