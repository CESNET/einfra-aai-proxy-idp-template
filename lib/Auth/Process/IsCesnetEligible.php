<?php

namespace SimpleSAML\Module\cesnet\Auth\Process;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Configuration;
use SimpleSAML\Module;
use SimpleSAML\Module\perun\Adapter;
use SimpleSAML\Module\perun\ChallengeManager;
use SimpleSAML\Module\perun\LdapConnector;
use SimpleSAML\Module\perun\model\User;
use SimpleSAML\Module\perun\RpcConnector;
use SimpleSAML\Module\perun\AdapterLdap;
use SimpleSAML\Module\perun\AdapterRpc;
use SimpleSAML\Logger;
use SimpleSAML\Error\Exception;

/**
 * Class IsCesnetEligible
 *
 * This class put the timestamp of last login into list of Attributes, when at least one value of attribute
 * 'eduPersonScopedAffiliation' is marked as isCesnetEligible in CESNET LDAP
 *
 * @author Pavel Vyskocil <vyskocilpavel@muni.cz>
 */
class IsCesnetEligible extends ProcessingFilter
{
    const CONFIG_FILE_NAME = 'module_cesnet_IsCesnetEligible.php';
    const ORGANIZATION_LDAP_BASE = 'ou=Organizations,o=eduID.cz,o=apps,dc=cesnet,dc=cz';

    const HOSTEL_ENTITY_ID = 'https://idp.hostel.eduid.cz/idp/shibboleth';

    const INTERFACE_PROPNAME = 'interface';
    const ATTR_NAME = 'attrName';
    const RPC_ATTRIBUTE_NAME = 'RPC.attributeName';
    const LDAP_ATTRIBUTE_NAME = 'LDAP.attributeName';
    const DEFAULT_ATTR_NAME = 'isCesnetEligibleLastSeen';
    const LDAP = 'LDAP';
    const RPC = 'RPC';
    const SCRIPT_NAME = 'updateIsCesnetEligible';
    const PATH_TO_KEY = 'pathToKey';
    const SIGNATURE_ALG = 'signatureAlg';

    const PERUN_USER_AFFILIATIONS_ATTR_NAME = 'perunUserAffiliationsAttrName';
    const PERUN_USER_SPONSORING_ORGANIZATIONS_ATTR_NAME = 'perunUserSponsoringOrganizationsAttrName';

    private $cesnetEligibleLastSeenValue;
    private $interface = self::RPC;
    private $rpcAttrName;
    private $ldapAttrName;
    private $returnAttrName = self::DEFAULT_ATTR_NAME;

    private $userAffiliationsAttrName;
    private $userSponsoringOrganizationsAttrName;

    private $idpEntityId;
    private $eduPersonScopedAffiliation = [];

    private $pathToKey;
    private $signatureAlg;

    /**
     * @var LdapConnector
     */
    private $cesnetLdapConnector;

    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var AdapterRpc
     */
    private $rpcAdapter;

    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);
        $conf = Configuration::loadFromArray($config);
        if (!isset($config[self::RPC_ATTRIBUTE_NAME]) || empty($config[self::RPC_ATTRIBUTE_NAME])) {
            throw new Exception(
                'cesnet:IsCesnetEligible - missing mandatory configuration option \'' .
                self::RPC_ATTRIBUTE_NAME . '\'.'
            );
        }

        if (!isset($config[self::PATH_TO_KEY])) {
            throw new Exception(
                'cesnet:isCesnetEligible: missing mandatory configuration option \'pathToKey\'.'
            );
        }

        $this->rpcAttrName = $config[self::RPC_ATTRIBUTE_NAME];
        $this->pathToKey = $config[self::PATH_TO_KEY];

        $this->cesnetLdapConnector = (new AdapterLdap(self::CONFIG_FILE_NAME))->getConnector();
        $this->rpcAdapter = Adapter::getInstance(Adapter::RPC);

        if (isset($config[self::ATTR_NAME]) && !empty($config[self::ATTR_NAME])) {
            $this->returnAttrName = $config['attrName'];
        }

        if (isset($config[self::INTERFACE_PROPNAME], $config[self::LDAP_ATTRIBUTE_NAME]) &&
            $config[self::INTERFACE_PROPNAME] === self::LDAP && !empty($config[self::LDAP_ATTRIBUTE_NAME])) {
            $this->interface = $config[self::INTERFACE_PROPNAME];
            $this->ldapAttrName = $config[self::LDAP_ATTRIBUTE_NAME];
            $this->adapter = Adapter::getInstance(Adapter::LDAP);
        } else {
            Logger::warning(
                'cesnet:IsCesnetEligible - One of ' . self::INTERFACE_PROPNAME . self::LDAP_ATTRIBUTE_NAME .
                ' is missing or empty. RPC interface will be used'
            );
            $this->adapter =Adapter::getInstance(Adapter::RPC);
        }

        if (isset($config[self::SIGNATURE_ALG])) {
            $this->signatureAlg = (array)$config[self::SIGNATURE_ALG];
        } else {
            $this->signatureAlg = 'RS512';
        }

        $this->userSponsoringOrganizationsAttrName =
            $conf->getString(self::PERUN_USER_SPONSORING_ORGANIZATIONS_ATTR_NAME, null);
        $this->userAffiliationsAttrName = $conf->getString(self::PERUN_USER_AFFILIATIONS_ATTR_NAME, null);

        if (!isset($this->userAffiliationsAttrName, $this->userSponsoringOrganizationsAttrName)) {
            Logger::warning(
                'cesnet:IsCesnetEligible - One of attributes [' . $this->userAffiliationsAttrName . ', ' .
                $this->userSponsoringOrganizationsAttrName . '] wasn\'t set!'
            );
        }
    }

    public function process(&$request)
    {
        assert('is_array($request)');

        if (isset($request['perun']['user'])) {
            $user = $request['perun']['user'];
        } else {
            Logger::debug(
                'cesnet:IsCesnetEligible - ' .
                'Request doesn\'t contain User, so attribute \'isCesnetEligible\' won\'t be stored.'
            );
            $user = null;
        }
        $this->idpEntityId = $request['saml:sp:IdP'];

        if (isset($request['Attributes']['eduPersonScopedAffiliation'])) {
            $this->eduPersonScopedAffiliation
                = $request['Attributes']['eduPersonScopedAffiliation'];
        } else {
            Logger::error(
                'cesnet:IsCesnetEligible - ' .
                'Attribute with name \'eduPersonScopedAffiliation\' did not received from IdP!'
            );
        }

        $isHostelVerified = false;
        if ($request['saml:sp:IdP'] === self::HOSTEL_ENTITY_ID &&
            isset($request['Attributes']['loa'])
            && (integer)$request['Attributes']['loa'][0] === 2
        ) {
            $isHostelVerified = true;
            Logger::debug('cesnet:IsCesnetEligible - The user was verified by Hostel.');
        }

        if (!empty($user)) {
            if ($this->interface === self::LDAP) {
                $attrs = $this->adapter->getUserAttributes($user, [$this->ldapAttrName]);
                if (isset($attrs[$this->ldapAttrName][0])) {
                    $this->cesnetEligibleLastSeenValue = $attrs[$this->ldapAttrName][0];
                }
            } else {
                $this->cesnetEligibleLastSeenValue = $this->adapter->getUserAttributes(
                    $user,
                    [$this->rpcAttrName]
                )['value'];
            }
        }

        if ($isHostelVerified || (!empty($this->eduPersonScopedAffiliation) && $this->isCesnetEligible($user))) {
            $this->cesnetEligibleLastSeenValue = date('Y-m-d H:i:s');

            if (!empty($user)) {
                // Update attribute 'isCesnetEligible' in Perun

                $id = uniqid();

                $dataChallenge = [
                    'id' => $id,
                    'scriptName' => self::SCRIPT_NAME
                ];

                $json = json_encode($dataChallenge);

                $curlChallenge = curl_init();
                curl_setopt($curlChallenge, CURLOPT_POSTFIELDS, $json);
                curl_setopt($curlChallenge, CURLOPT_URL, Module::getModuleURL('perun/getChallenge.php'));
                curl_setopt($curlChallenge, CURLOPT_RETURNTRANSFER, true);

                $challenge = curl_exec($curlChallenge);

                if (curl_errno($curlChallenge)) {
                    $error_msg = curl_error($curlChallenge);
                    Logger::error('cesnet:IsCesnetEligible - ' . $error_msg);
                }

                curl_close($curlChallenge);

                if (!empty($challenge)) {
                    $jwk = JWKFactory::createFromKeyFile($this->pathToKey);
                    $algorithmManager = new AlgorithmManager(
                        [
                            ChallengeManager::getAlgorithm('Signature\\Algorithm', $this->signatureAlg)
                        ]
                    );
                    $jwsBuilder = new JWSBuilder($algorithmManager);

                    $data= [
                        'userId' => $user->getId(),
                        'isCesnetEligibleValue' => $this->cesnetEligibleLastSeenValue,
                        'cesnetEligibleLastSeenAttrName' => $this->rpcAttrName
                    ];

                    $payload = json_encode([
                        'iat' => time(),
                        'nbf' => time(),
                        'exp' => time() + 3600,
                        'challenge' => $challenge,
                        'id' => $id,
                        'data' => $data
                    ]);

                    $jws = $jwsBuilder
                        ->create()
                        ->withPayload($payload)
                        ->addSignature($jwk, ['alg' => $this->signatureAlg])
                        ->build();

                    $serializer = new CompactSerializer();
                    $token = $serializer->serialize($jws, 0);

                    $cmd = 'curl -X POST -H "Content-Type: application/json" -d \'' . json_encode($token) . '\' ' .
                        Module::getModuleURL('cesnet/updateIsCesnetEligible.php') . ' > /dev/null &';
                    exec($cmd);
                } else {
                    Logger::error('cesnet:IsCesnetEligible - Retrieving the challenge was not successful.');
                }
            }
        }

        if ($this->cesnetEligibleLastSeenValue !== null) {
            $request['Attributes'][$this->returnAttrName] = [$this->cesnetEligibleLastSeenValue];
            Logger::debug(
                'cesnet:IsCesnetEligible - Attribute ' . $this->returnAttrName . ' was set to value ' .
                $this->cesnetEligibleLastSeenValue
            );
        }


        $request['Attributes']['isCesnetEligible'] = ['false'];
        if (($this->cesnetEligibleLastSeenValue !== null) && $this->cesnetEligibleLastSeenValue > date('Y-m-d H:i:s', strtotime('-1 year'))) {
            $request['Attributes']['isCesnetEligible'] = ['true'];
            Logger::debug(
                'cesnet:IsCesnetEligible - Attribute isCesnetEligible was set to true.' );
        }


    }

    /**
     * Returns true if one of user's affiliation is in allowed affiliations for this IdP , False if not
     * @param $user User or Null
     * @return bool
     */
    private function isCesnetEligible($user): bool
    {
        $allowedAffiliations = $this->getAllowedAffiliations([$this->idpEntityId]);
        if ($this->compareAffiliations($this->eduPersonScopedAffiliation, $allowedAffiliations)) {
            return true;
        }

        # Check if user has isCesnetEligible by sponsoring in some organization
        try {
            if (isset($user, $this->userAffiliationsAttrName, $this->userSponsoringOrganizationsAttrName)) {
                $userAttributes = $this->rpcAdapter->getUserAttributesValues(
                    $user,
                    [$this->userAffiliationsAttrName, $this->userSponsoringOrganizationsAttrName]
                );

                $perunUserAffiliations = $userAttributes[$this->userAffiliationsAttrName] ?? [];
                $perunUserSponsoringOrganizations = $userAttributes[$this->userSponsoringOrganizationsAttrName] ?? [];

                if (empty($perunUserAffiliations) || empty($perunUserSponsoringOrganizations)) {
                    Logger::debug(
                        'cesnet:IsCesnetEligible - One of attributes [' . $this->userAffiliationsAttrName . ':' .
                        json_encode($perunUserAffiliations) . ', ' . $this->userSponsoringOrganizationsAttrName.
                        ':' . json_encode($perunUserSponsoringOrganizations) . '] has empty value!'
                    );
                    return false;
                }

                $allowedSponsoredAffiliations = $this->getAllowedAffiliations($perunUserSponsoringOrganizations);
                return $this->compareAffiliations($perunUserAffiliations, $allowedSponsoredAffiliations);
            }
        } catch (\Exception $exception) {
            Logger::error(
                'cesnet:IsCesnetEligible - Exception '. $exception .
                ' during computing isCesnetEligible by sponsoring in some organizations'
            );
        }

        return false;
    }

    /**
     * Return list of allowed affiliations for IdP from CESNET LDAP
     * @param $idpEntityIds array of entityId of IdPs
     * @return array of allowed affiliations
     */
    private function getAllowedAffiliations($idpEntityIds): array
    {
        $allowedAffiliations = [];
        try {
            $filter = '(|';
            foreach ($idpEntityIds as $idpEntityId) {
                $filter .= '(entityIDofIdP=' . $idpEntityId . ')';
            }
            $filter .= ')';

            $results = $this->cesnetLdapConnector->searchForEntities(
                self::ORGANIZATION_LDAP_BASE,
                $filter,
                ['cesnetcustomeraffiliation', 'eduIDczScope']
            );

            if (empty($results)) {
                Logger::debug('cesnet:IsCesnetEligible - Received empty response from LDAP for filter'
                    . $filter . '.');
            } else {
                foreach ($results as $result) {
                    $affiliations = $result['cesnetcustomeraffiliation'] ?? [];
                    $scopes = $result['eduIDczScope'] ?? [];

                    foreach ($scopes as $scope) {
                        foreach ($affiliations as $affiliation) {
                            $allowedAffiliations[] = trim($affiliation) . '@' . trim($scope);
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            Logger::warning('cesnet:IsCesnetEligible - Unable to connect to LDAP!');
        }

        return $allowedAffiliations;
    }

    /**
     * Compare two lists of affiliations and returns true if one of affiliations without scope is in booth lists.
     * @param $userAffiliations array of user scoped affiliations
     * @param $allowedAffiliations array of allowed unscoped affiliations
     * @return bool
     */
    private function compareAffiliations($userAffiliations, $allowedAffiliations): bool
    {
        $result = array_intersect($userAffiliations, $allowedAffiliations);
        if (!empty($result)) {
            return true;
        }
        return false;
    }
}
