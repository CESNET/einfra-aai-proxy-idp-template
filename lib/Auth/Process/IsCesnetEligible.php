<?php

namespace SimpleSAML\Module\cesnet\Auth\Process;

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
class IsCesnetEligible extends \SimpleSAML\Auth\ProcessingFilter
{
    const CONFIG_FILE_NAME = 'module_cesnet_IsCesnetEligible.php';
    const ORGANIZATION_LDAP_BASE = 'ou=Organizations,o=eduID.cz,o=apps,dc=cesnet,dc=cz';

    const HOSTEL_ENTITY_ID = "https://idp.hostel.eduid.cz/idp/shibboleth";

    const INTERFACE_PROPNAME = "interface";
    const CESNET_ELIGIBLE_LAST_SEEN_ATTR = "cesnetEligibleLastSeenAttr";
    const DEFAULT_ATTR_NAME = 'isCesnetEligibleLastSeen';

    private $cesnetEligibleLastSeen;
    private $cesnetEligibleLastSeenAttr;

    private $spEntityId;
    private $idpEntityId;
    private $eduPersonScopedAffiliation = array();

    /**
     * @var LdapConnector
     */
    private $cesnetLdapConnector;

    /**
     * @var RpcConnector
     */
    private $rpcConnector;

    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);

        if (!isset($config[self::CESNET_ELIGIBLE_LAST_SEEN_ATTR])) {
            throw new Exception(
                "cesnet:IsCesnetEligible - missing mandatory configuration option '" .
                self::CESNET_ELIGIBLE_LAST_SEEN_ATTR . "'."
            );
        }

        if (isset($config['attrName'])) {
            $this->attrName = $config['attrName'];
        } else {
            $this->attrName = self::DEFAULT_ATTR_NAME;
        }

        $this->cesnetEligibleLastSeenAttr = $config[self::CESNET_ELIGIBLE_LAST_SEEN_ATTR];

        $this->cesnetLdapConnector = (new AdapterLdap(self::CONFIG_FILE_NAME))->getConnector();
        $this->rpcConnector = (new AdapterRpc())->getConnector();
    }

    public function process(&$request)
    {
        assert('is_array($request)');

        if (isset($request['perun']) && isset($request['perun']['user'])) {
            $user = $request['perun']['user'];
        } else {
            Logger::debug(
                "cesnet:IsCesnetEligible - " .
                "Request doesn't contain User, so attribute 'isCesnetEligible' won't be stored."
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
                "cesnet:IsCesnetEligible - Attribute with name 'eduPersonScopedAffiliation' did not received from IdP!"
            );
        }

        $isHostelVerified = false;
        if ($request['saml:sp:IdP'] === self::HOSTEL_ENTITY_ID &&
            isset($request['Attributes']['loa'])
            && $request['Attributes']['loa'][0] == 2
        ) {
            $isHostelVerified = true;
            Logger::debug("cesnet:IsCesnetEligible - The user was verified by Hostel.");
        }

        try {
            if (!empty($user)) {
                $this->cesnetEligibleLastSeen = $this->rpcConnector->get(
                    'attributesManager',
                    'getAttribute',
                    array('user' => $user->getId(), 'attributeName' => $this->cesnetEligibleLastSeenAttr,)
                );
            }

            if ((!empty($this->eduPersonScopedAffiliation) && $this->isCesnetEligible())
                || $isHostelVerified
            ) {
                $this->cesnetEligibleLastSeen['value'] = date("Y-m-d H:i:s");

                if (!empty($user)) {
                    $this->rpcConnector->post(
                        'attributesManager',
                        'setAttribute',
                        array('user' => $user->getId(), 'attribute' => $this->cesnetEligibleLastSeen,)
                    );
                }
            }
        } catch (Exception $ex) {
            Logger::warning("cesnet:IsCesnetEligible - " . $ex->getMessage());
        }

        if ($this->cesnetEligibleLastSeen['value'] != null) {
            $request['Attributes'][$this->attrName] = array($this->cesnetEligibleLastSeen['value']);
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
            $userAffiliationWithoutScope = explode("@", $userAffiliation)[0];
            if (!is_null($userAffiliationWithoutScope) &&
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
        $allowedAffiliations = array();

        try {
            $affiliations = $this->cesnetLdapConnector->searchForEntity(
                self::ORGANIZATION_LDAP_BASE,
                '(entityIDofIdP=' . $idpEntityId . ')',
                array('cesnetcustomeraffiliation')
            )['cesnetcustomeraffiliation'];

            if (empty($affiliations)) {
                Logger::debug("cesnet:IsCesnetEligible - Received empty response from LDAP, entityId "
                    . $idpEntityId . " was probably not found.");
            } else {
                foreach ($affiliations as $affiliation) {
                    array_push($allowedAffiliations, $affiliation);
                }
            }
        } catch (Exception $ex) {
            Logger::warning("cesnet:IsCesnetEligible - Unable to connect to LDAP!");
        }

        return $allowedAffiliations;
    }
}
