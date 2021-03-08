<?php

/**
 * Script for updating IsCesnetEligible in Perun asynchronously
 *
 * @author Pavel Vyskocil <vyskocilpavel@muni.cz>
 */

use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Component\Checker;
use SimpleSAML\Configuration;
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

const CONFIG_FILE_NAME = 'challenges_config.php';

try {
    $config = Configuration::getConfig(CONFIG_FILE_NAME);
    $keyPub = $config->getString('updateIsCesnetEligible');
    $signatureAlg = $config->getString('signatureAlg', 'RS512');

    $algorithmManager = new AlgorithmManager(
        [
            ChallengeManager::getAlgorithm('Signature\\Algorithm', $signatureAlg)
        ]
    );
    $jwsVerifier = new JWSVerifier($algorithmManager);
    $jwk = JWKFactory::createFromKeyFile($keyPub);

    $serializerManager = new JWSSerializerManager([new CompactSerializer()]);
    $jws = $serializerManager->unserialize($token);

    $headerCheckerManager = new HeaderCheckerManager([new AlgorithmChecker([$signatureAlg])], [new JWSTokenSupport()]);
    $headerCheckerManager->check($jws, 0);

    $isVerified = $jwsVerifier->verifyWithKey($jws, $jwk, 0);

    if (!$isVerified) {
        die('The token signature is invalid!');
    }

    $claimCheckerManager = new ClaimCheckerManager(
        [
            new Checker\IssuedAtChecker(),
            new Checker\NotBeforeChecker(),
            new Checker\ExpirationTimeChecker(),
        ]
    );

    $claims = json_decode($jws->getPayload(), true);
    $claimCheckerManager->check($claims);

    $challenge = $claims['challenge'];
    $id = $claims['id'];

    $userId = $claims['data']['userId'];
    $isCesnetEligibleValue = $claims['data']['isCesnetEligibleValue'];
    $isCesnetEligibleLastSeenAttrName = $claims['data']['cesnetEligibleLastSeenAttrName'];

    $challengeManager = new ChallengeManager();

    $challengeDb = $challengeManager->readChallengeFromDb($id);
    $checkAccessSucceeded = $challengeManager->checkAccess($challenge, $challengeDb);
    $challengeSuccessfullyDeleted = $challengeManager->deleteChallengeFromDb($id);
} catch (Checker\InvalidClaimException | Checker\MissingMandatoryClaimException $ex) {
    Logger::error('cesnet:updateIsCesnetEligible: An error occurred when the token was verifying.');
    http_response_code(400);
    exit;
}

try {
    $cesnetEligibleLastSeenAttribute = $rpcConnector->get(
        'attributesManager',
        'getAttribute',
        ['user' => $userId, 'attributeName' => $isCesnetEligibleLastSeenAttrName,]
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
