<?php
/**
 * Class sspmod_cesnet_Auth_Process_IsCesnetEligible
 *
 * This class put the timestamp of last login with account that pass through the eduid filter
 * (https://www.eduid.cz/en/tech/userfiltering#include_filter) into list of Attributes
 *
 * @author Pavel Vyskocil <vyskocilpavel@muni.cz>
 */
class sspmod_cesnet_Auth_Process_IsCesnetEligible extends SimpleSAML_Auth_ProcessingFilter
{

	const CONFIG_FILE_NAME = 'module_cesnet_IsCesnetEligible.php';
	const ORGANIZATION_LDAP_BASE = 'ou=Organizations,o=eduID.cz,o=apps,dc=cesnet,dc=cz';

	const HOSTEL_ENTITY_ID = "https://idp.hostel.eduid.cz/idp/shibboleth";

	const INTERFACE_PROPNAME = "interface";
	const CESNET_ELIGIBLE_LAST_SEEN_ATTR = "cesnetEligibleLastSeenAttr";
	const DEFAULT_ATTR_NAME = 'isCesnetEligibleLastSeen';
	const LIST_OF_PERUN_ENTITY_IDS = 'listOfPerunEntityIds';

	private $cesnetEligibleLastSeen;
	private $cesnetEligibleLastSeenAttr;

	private $spEntityId;
	private $idpEntityId;
	private $eduPersonScopedAffiliation = array();
	private $listOfPerunEntityIds = array();

	/**
	 * @var sspmod_perun_LdapConnector
	 */
	private $cesnetLdapConnector;

	/**
	 * @var sspmod_perun_RpcConnector
	 */
	private $rpcConnector;

	public function __construct($config, $reserved)
	{
		parent::__construct($config, $reserved);

		if (!isset($config[self::CESNET_ELIGIBLE_LAST_SEEN_ATTR])) {
			throw new SimpleSAML_Error_Exception("cesnet:IsCesnetEligible - missing mandatory configuration option '" . self::CESNET_ELIGIBLE_LAST_SEEN_ATTR . "'.");
		}

		if (isset($config['attrName'])) {
			$this->attrName = $config['attrName'];
		} else {
			$this->attrName = self::DEFAULT_ATTR_NAME;
		}

		if (isset($config['listOfPerunEntityIds'])) {
			$this->listOfPerunEntityIds = $config['listOfPerunEntityIds'];
		}

		$this->cesnetEligibleLastSeenAttr = $config[self::CESNET_ELIGIBLE_LAST_SEEN_ATTR];

		$this->cesnetLdapConnector = (new sspmod_perun_AdapterLdap(self::CONFIG_FILE_NAME))->getConnector();
		$this->rpcConnector = (new sspmod_perun_AdapterRpc())->getConnector();

	}

	public function process(&$request)
	{
		assert('is_array($request)');
		$user = $request['perun']['user'];
		$this->spEntityId = $request['SPMetadata']['entityid'];
		$this->idpEntityId = $request['saml:sp:IdP'];

		if (isset($request['Attributes']['eduPersonScopedAffiliation'])) {
			$this->eduPersonScopedAffiliation = $request['Attributes']['eduPersonScopedAffiliation'];
		} else {
			SimpleSAML\Logger::error("cesnet:IsCesnetEligible - Attribute with name 'eduPersonScopedAffiliation' did not received from IdP!");
		}

		$isHostelVerified = false;
		if ($request['saml:sp:IdP'] === self::HOSTEL_ENTITY_ID && isset($request['Attributes']['loa'])
			&& $request['Attributes']['loa'][0] == 2) {
			$isHostelVerified = true;
			SimpleSAML\Logger::debug("cesnet:IsCesnetEligible - The user was verified by Hostel.");
		}

		try {
			if (!in_array($this->spEntityId, $this->listOfPerunEntityIds)) {
				$this->cesnetEligibleLastSeen = $this->rpcConnector->get('attributesManager', 'getAttribute', array(
					'user' => $user->getId(),
					'attributeName' => $this->cesnetEligibleLastSeenAttr,
				));
			}

			if ((!empty($this->eduPersonScopedAffiliation) && $this->isCesnetEligible()) || $isHostelVerified) {
				$this->cesnetEligibleLastSeen['value'] = date("Y-m-d H:i:s");
				if (!in_array($this->spEntityId, $this->listOfPerunEntityIds)) {
					$this->rpcConnector->post('attributesManager', 'setAttribute', array(
						'user' => $user->getId(),
						'attribute' => $this->cesnetEligibleLastSeen,
					));
				}
			}
		} catch (Exception $ex) {
			SimpleSAML\Logger::warning("cesnet:IsCesnetEligible - " . $ex->getMessage());
		}

		if ($this->cesnetEligibleLastSeen['value'] != null) {
			$request['Attributes'][$this->attrName] = array($this->cesnetEligibleLastSeen['value']);
		}
	}

	/**
	 * Returns true if one of user's affiliation is in allowed affiliations for this IdP , False if not
	 * @return bool
	 */
	private function isCesnetEligible() {

		$allowedAffiliations = $this->getAllowedAffiliations($this->idpEntityId);
		foreach ($this->eduPersonScopedAffiliation as $userAffiliation) {
			$userAffiliationWithoutScope = explode("@", $userAffiliation)[0];
			if (!is_null($userAffiliationWithoutScope) && !empty($userAffiliationWithoutScope)
				&& in_array($userAffiliationWithoutScope, $allowedAffiliations)) {
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
	private function getAllowedAffiliations($idpEntityId) {
		$allowedAffiliations = array();

		try {
			$affiliations = $this->cesnetLdapConnector->searchForEntity(self::ORGANIZATION_LDAP_BASE,'(entityIDofIdP=' . $idpEntityId . ')', array(
				'cesnetcustomeraffiliation'))['cesnetcustomeraffiliation'];
			foreach ($affiliations as $affiliation) {
				array_push($allowedAffiliations, $affiliation);
			}
		} catch (Exception $ex) {
			SimpleSAML\Logger::warning("cesnet:IsCesnetEligible - Unable to connect to LDAP!");
		}
		return $allowedAffiliations;
	}
}
