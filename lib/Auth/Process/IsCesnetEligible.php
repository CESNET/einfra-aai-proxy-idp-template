<?php

declare(strict_types=1);

namespace SimpleSAML\Module\cesnet\Auth\Process;

use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Configuration;
use SimpleSAML\Error\Exception;
use SimpleSAML\Logger;
use SimpleSAML\Module;
use SimpleSAML\Module\perun\Adapter;
use SimpleSAML\Module\perun\AdapterLdap;
use SimpleSAML\Module\perun\AdapterRpc;
use SimpleSAML\Module\perun\ChallengeManager;
use SimpleSAML\Module\perun\LdapConnector;
use SimpleSAML\Module\perun\model\User;

/**
 * Class IsCesnetEligible
 *
 * This class put the timestamp of last login into list of Attributes, when at least one value of attribute
 * 'eduPersonScopedAffiliation' is marked as isCesnetEligible in CESNET LDAP
 */
class IsCesnetEligible extends ProcessingFilter
{
    public const CONFIG_FILE_NAME = 'module_cesnet_IsCesnetEligible.php';

    public const ORGANIZATION_LDAP_BASE = 'ou=Organizations,o=eduID.cz,o=apps,dc=cesnet,dc=cz';

    public const INTERFACE_PROPNAME = 'interface';

    public const ATTR_NAME = 'attrName';

    public const RPC_ATTRIBUTE_NAME = 'RPC.attributeName';

    public const LDAP_ATTRIBUTE_NAME = 'LDAP.attributeName';

    public const DEFAULT_ATTR_NAME = 'isCesnetEligibleLastSeen';

    public const LDAP = 'LDAP';

    public const RPC = 'RPC';

    public const SCRIPT_NAME = 'updateIsCesnetEligible';

    public const PERUN_USER_AFFILIATIONS_ATTR_NAME = 'perunUserAffiliationsAttrName';

    public const PERUN_USER_SPONSORING_ORGANIZATIONS_ATTR_NAME = 'perunUserSponsoringOrganizationsAttrName';

    private $cesnetEligibleLastSeenValue;

    private $interface = self::RPC;

    private $rpcAttrName;

    private $ldapAttrName;

    private $returnAttrName = self::DEFAULT_ATTR_NAME;

    private $userAffiliationsAttrName;

    private $userSponsoringOrganizationsAttrName;

    private $idpEntityId;

    private $eduPersonScopedAffiliation = [];

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
        if (! isset($config[self::RPC_ATTRIBUTE_NAME]) || empty($config[self::RPC_ATTRIBUTE_NAME])) {
            throw new Exception(
                'cesnet:IsCesnetEligible - missing mandatory configuration option \'' .
                self::RPC_ATTRIBUTE_NAME . '\'.'
            );
        }

        $this->rpcAttrName = $config[self::RPC_ATTRIBUTE_NAME];

        $this->cesnetLdapConnector = (new AdapterLdap(self::CONFIG_FILE_NAME))->getConnector();
        $this->rpcAdapter = Adapter::getInstance(Adapter::RPC);

        if (isset($config[self::ATTR_NAME]) && ! empty($config[self::ATTR_NAME])) {
            $this->returnAttrName = $config['attrName'];
        }

        if (isset($config[self::INTERFACE_PROPNAME], $config[self::LDAP_ATTRIBUTE_NAME]) &&
            $config[self::INTERFACE_PROPNAME] === self::LDAP && ! empty($config[self::LDAP_ATTRIBUTE_NAME])) {
            $this->interface = $config[self::INTERFACE_PROPNAME];
            $this->ldapAttrName = $config[self::LDAP_ATTRIBUTE_NAME];
            $this->adapter = Adapter::getInstance(Adapter::LDAP);
        } else {
            Logger::warning(
                'cesnet:IsCesnetEligible - One of ' . self::INTERFACE_PROPNAME . self::LDAP_ATTRIBUTE_NAME .
                ' is missing or empty. RPC interface will be used'
            );
            $this->adapter = Adapter::getInstance(Adapter::RPC);
        }

        $this->userSponsoringOrganizationsAttrName =
            $conf->getString(self::PERUN_USER_SPONSORING_ORGANIZATIONS_ATTR_NAME, null);
        $this->userAffiliationsAttrName = $conf->getString(self::PERUN_USER_AFFILIATIONS_ATTR_NAME, null);

        if (! isset($this->userAffiliationsAttrName, $this->userSponsoringOrganizationsAttrName)) {
            Logger::warning(
                'cesnet:IsCesnetEligible - One of attributes [' . $this->userAffiliationsAttrName . ', ' .
                $this->userSponsoringOrganizationsAttrName . '] wasn\'t set!'
            );
        }
    }

    public function process(&$request)
    {
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

        if (! empty($user)) {
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

        if (! empty($this->eduPersonScopedAffiliation) && $this->isCesnetEligible($user)) {
            $this->cesnetEligibleLastSeenValue = date('Y-m-d H:i:s');

            if (! empty($user)) {
                // Update attribute 'isCesnetEligible' in Perun

                $id = uniqid('', true);

                $challengeManager = new ChallengeManager();

                $data = [
                    'userId' => $user->getId(),
                    'isCesnetEligibleValue' => $this->cesnetEligibleLastSeenValue,
                    'cesnetEligibleLastSeenAttrName' => $this->rpcAttrName,
                ];

                $token = $challengeManager->generateToken($id, self::SCRIPT_NAME, $data);

                $cmd = 'curl -X POST -H "Content-Type: application/json" -d \'' . json_encode($token) . '\' ' .
                    Module::getModuleURL('cesnet/updateIsCesnetEligible.php') . ' > /dev/null &';

                exec($cmd);
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
        if (($this->cesnetEligibleLastSeenValue !== null) && $this->cesnetEligibleLastSeenValue > date(
            'Y-m-d H:i:s',
            strtotime('-1 year')
        )) {
            $request['Attributes']['isCesnetEligible'] = ['true'];
            Logger::debug('cesnet:IsCesnetEligible - Attribute isCesnetEligible was set to true.');
        }
    }

    /**
     * Returns true if one of user's affiliation is in allowed affiliations for this IdP , False if not
     *
     * @param User $user or Null
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
                        json_encode($perunUserAffiliations) . ', ' . $this->userSponsoringOrganizationsAttrName .
                        ':' . json_encode($perunUserSponsoringOrganizations) . '] has empty value!'
                    );
                    return false;
                }

                $allowedSponsoredAffiliations = $this->getAllowedAffiliations($perunUserSponsoringOrganizations);
                return $this->compareAffiliations($perunUserAffiliations, $allowedSponsoredAffiliations);
            }
        } catch (\Exception $exception) {
            Logger::error(
                'cesnet:IsCesnetEligible - Exception ' . $exception .
                ' during computing isCesnetEligible by sponsoring in some organizations'
            );
        }

        return false;
    }

    /**
     * Return list of allowed affiliations for IdP from CESNET LDAP
     *
     * @param array $idpEntityIds of entityId of IdPs
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
     *
     * @param array $userAffiliations of user scoped affiliations
     * @param array $allowedAffiliations of allowed unscoped affiliations
     */
    private function compareAffiliations($userAffiliations, $allowedAffiliations): bool
    {
        $result = array_intersect($userAffiliations, $allowedAffiliations);
        if (! empty($result)) {
            return true;
        }
        return false;
    }
}
