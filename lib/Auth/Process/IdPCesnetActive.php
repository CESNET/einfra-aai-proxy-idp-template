<?php

/**
 * Class sspmod_cesnet_Auth_Process_IdPCesnetActive
 *
 * Filter get information, if IdP is CesnetActive and store it to attribute 'IdPCesnetActive' .
 * CesnetActive means, that IdP is CESNET customer
 *
 * @author Pavel Vyskocil <vyskocilpavel@muni.cz>
 */
class sspmod_cesnet_Auth_Process_IdPCesnetActive extends SimpleSAML_Auth_ProcessingFilter
{
	const CONFIG_FILE_NAME = 'module_cesnet_IdPCesnetActive.php';
	const IDP_CESNET_ACTIVE  = 'IdPCesnetActive';
	const ORGANIZATION_LDAP_BASE = 'ou=Organizations,o=eduID.cz,o=apps,dc=cesnet,dc=cz';

	/**
	 * @var sspmod_perun_LdapConnector
	 */
	private $connector;

	public function __construct($config, $reserved)
	{
		parent::__construct($config, $reserved);

		$this->connector = (new sspmod_perun_AdapterLdap(self::CONFIG_FILE_NAME))->getConnector();

	}

	public function process(&$request)
	{
		assert(is_array($request));
		$isCesnetActive = false;

		$idpEntityId = $request['saml:sp:IdP'];
		$organizationDn = $this->getOrganizationDn($idpEntityId);

		if (is_null($organizationDn)) {
			SimpleSAML\Logger::warning("cesnet:IdPCesnetActive - Failed to get organizationDn from LDAP!");
		} else {
			$isCesnetActive = $this->isCesnetActive($organizationDn);
		}

		SimpleSAML\Logger::info("cesnet:IdPCesnetActive - Attribute " . self::IDP_CESNET_ACTIVE . " has been stored into request.");

		$request['Attributes'][self::IDP_CESNET_ACTIVE] = array(var_export($isCesnetActive,true));

	}

	private function getOrganizationDn($idpEntityId)
	{
		$organizationDn = null;

		try {
			$perunAttr = $this->connector->searchForEntity(self::ORGANIZATION_LDAP_BASE,'(entityIDofIdP=' . $idpEntityId . ')', array('oPointer'))['oPointer'];
			if (!empty($perunAttr)) {
				$organizationDn = $perunAttr[0];
			}
		} catch (Exception $ex) {
			SimpleSAML\Logger::warning("cesnet:IdPCesnetActive - Unable to connect to LDAP!");
		}

		return $organizationDn;
	}

	private function isCesnetActive($organizationDn)
	{
		$isCesnetActive = false;

		try {
			$perunAttr = $this->connector->searchForEntity($organizationDn, '(objectClass=*)', array('cesnetActive'))['cesnetActive'];
			if (empty($perunAttr)){
				SimpleSAML\Logger::warning("cesnet:IdPCesnetActive - Failed to get information about organization from LDAP!");
			} else {
				if ($perunAttr[0] === 'TRUE' ) {
					$isCesnetActive = true;
				}
			}
		} catch (Exception $ex) {
			SimpleSAML\Logger::warning("cesnet:IdPCesnetActive - Unable to connect to LDAP!");
		}

		return $isCesnetActive;
	}

}
