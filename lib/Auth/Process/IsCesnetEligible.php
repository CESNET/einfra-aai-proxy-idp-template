<?php

namespace SimpleSAML\Module\cesnet\Auth\Process;

use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Module;
use SimpleSAML\Module\perun\Adapter;
use SimpleSAML\Module\perun\LdapConnector;
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

    private $cesnetEligibleLastSeenValue;
    private $interface = self::RPC;
    private $rpcAttrName;
    private $ldapAttrName;
    private $returnAttrName = self::DEFAULT_ATTR_NAME;

    private $spEntityId;
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

    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);

        if (!isset($config[self::RPC_ATTRIBUTE_NAME]) || empty($config[self::RPC_ATTRIBUTE_NAME])) {
            throw new Exception(
                'cesnet:IsCesnetEligible - missing mandatory configuration option \'' .
                self::RPC_ATTRIBUTE_NAME . '\'.'
            );
        }

        $this->rpcAttrName = $config[self::RPC_ATTRIBUTE_NAME];

        $this->cesnetLdapConnector = (new AdapterLdap(self::CONFIG_FILE_NAME))->getConnector();

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
    }

    public function process(&$request)
    {
        assert('is_array($request)');

        if (isset($request['perun']) && isset($request['perun']['user'])) {
            $user = $request['perun']['user'];
        } else {
            Logger::debug(
                'cesnet:IsCesnetEligible - ' .
                'Request doesn\'t contain User, so attribute \'isCesnetEligible\' won\'t be stored.'
            );
            $user = null;
        }
        $this->spEntityId = $request['SPMetadata']['entityid'];
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

        if ($isHostelVerified || (!empty($this->eduPersonScopedAffiliation) && $this->isCesnetEligible())) {
            $this->cesnetEligibleLastSeenValue = date('Y-m-d H:i:s');

            if (!empty($user)) {
                // Update attribute 'isCesnetEligible' in Perun
                $data= [
                    'userId' => $user->getId(),
                    'isCesnetEligibleValue' => $this->cesnetEligibleLastSeenValue,
                    'cesnetEligibleLastSeenAttrName' => $this->rpcAttrName
                ];

                $cmd = 'curl -X POST -H "Content-Type: application/json" -d \'' . json_encode($data) . '\' ' .
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
    }

    /**
     * Returns true if one of user's affiliation is in allowed affiliations for this IdP , False if not
     * @return bool
     */
    private function isCesnetEligible()
    {
        $allowedAffiliations
            = $this->getAllowedAffiliations($this->idpEntityId);
        foreach ($this->eduPersonScopedAffiliation as $userAffiliation) {
            $userAffiliationWithoutScope = explode('@', $userAffiliation)[0];
            if ($userAffiliationWithoutScope !== null &&
                !empty($userAffiliationWithoutScope) &&
                in_array($userAffiliationWithoutScope, $allowedAffiliations)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return list of allowed affiliations for IdP from CESNET LDAP
     * @param $idpEntityId entityId of IdP
     * @return array of allowed affiliations
     */
    private function getAllowedAffiliations($idpEntityId)
    {
        $allowedAffiliations = [];

        try {
            $organization = $this->cesnetLdapConnector->searchForEntity(
                self::ORGANIZATION_LDAP_BASE,
                '(entityIDofIdP=' . $idpEntityId . ')',
                ['cesnetcustomeraffiliation']
            );

            if (empty($organization)) {
                Logger::debug('cesnet:IsCesnetEligible - Received empty response from LDAP, entityId '
                    . $idpEntityId . ' was probably not found.');
            } elseif (count($organization) > 1) {
                Logger::error('cesnet:IsCesnetEligible - Received more record from LDAP with entityId '
                              . $idpEntityId . '.');
            } else {
                $affiliations = $organization['cesnetcustomeraffiliation'];
                foreach ($affiliations as $affiliation) {
                    array_push($allowedAffiliations, $affiliation);
                }
            }
        } catch (Exception $ex) {
            Logger::warning('cesnet:IsCesnetEligible - Unable to connect to LDAP!');
        }

        return $allowedAffiliations;
    }
}
