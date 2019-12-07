<?php

/**
 * Script for updating IsCesnetEligible in Perun asynchronously
 *
 * @author Pavel Vyskocil <vyskocilpavel@muni.cz>
 */

use SimpleSAML\Error\Exception;
use SimpleSAML\Logger;
use SimpleSAML\Module\perun\AdapterRpc;

$rpcConnector = (new AdapterRpc())->getConnector();

$entityBody = file_get_contents('php://input');
$body = json_decode($entityBody, true);

$userId = $body['userId'];
$isCesnetEligibleValue = $body['isCesnetEligibleValue'];
$isCesnetEligibleAttrName = $body['cesnetEligibleLastSeenAttrName'];

try {
    $cesnetEligibleLastSeenAttribute = $rpcConnector->get(
        'attributesManager',
        'getAttribute',
        ['user' => $userId, 'attributeName' => $isCesnetEligibleAttrName,]
    );

    $cesnetEligibleLastSeenAttribute['value'] = $isCesnetEligibleValue;

    $rpcConnector->post(
        'attributesManager',
        'setAttribute',
        ['user' => $userId, 'attribute' => $cesnetEligibleLastSeenAttribute,]
    );

    Logger::debug(
        'cesnet:updateIsCesnetEligible - Value of attribute isCesnetEligibleLastSeen was updated to ' .
        $isCesnetEligibleValue . ' for user with userId: ' . $userId . ' in Perun system.'
    );
} catch (Exception $ex) {
    Logger::warning('cesnet:updateIsCesnetEligible - ' . $ex->getMessage());
}
