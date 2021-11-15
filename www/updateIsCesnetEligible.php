<?php

declare(strict_types=1);

/**
 * Script for updating IsCesnetEligible in Perun asynchronously
 */

use SimpleSAML\Error\Exception;
use SimpleSAML\Logger;
use SimpleSAML\Module\perun\AdapterRpc;
use SimpleSAML\Module\perun\ChallengeManager;

$rpcConnector = (new AdapterRpc())->getConnector();

$entityBody = file_get_contents('php://input');
$token = json_decode($entityBody, true);

$userId = null;
$isCesnetEligibleValue = null;
$isCesnetEligibleLastSeenAttrName = null;
$id = null;

try {
    $challengeManager = new ChallengeManager();
    $claims = $challengeManager->decodeToken($token);

    $userId = $claims['data']['userId'];
    $isCesnetEligibleValue = $claims['data']['isCesnetEligibleValue'];
    $isCesnetEligibleLastSeenAttrName = $claims['data']['cesnetEligibleLastSeenAttrName'];
} catch (\Exception $ex) {
    Logger::error('cesnet:updateIsCesnetEligible: An error occurred when the token was verifying.');
    http_response_code(400);
    exit;
}

try {
    $cesnetEligibleLastSeenAttribute = $rpcConnector->get(
        'attributesManager',
        'getAttribute',
        [
            'user' => $userId,
            'attributeName' => $isCesnetEligibleLastSeenAttrName,
        ]
    );

    $cesnetEligibleLastSeenAttribute['value'] = $isCesnetEligibleValue;

    $rpcConnector->post(
        'attributesManager',
        'setAttribute',
        [
            'user' => $userId,
            'attribute' => $cesnetEligibleLastSeenAttribute,
        ]
    );

    Logger::debug(
        'cesnet:updateIsCesnetEligible - Value of attribute isCesnetEligibleLastSeen was updated to ' .
        $isCesnetEligibleValue . ' for user with userId: ' . $userId . ' in Perun system.'
    );
} catch (Exception $ex) {
    Logger::warning('cesnet:updateIsCesnetEligible - ' . $ex->getMessage());
}
