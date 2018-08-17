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
	const UNIVERSITY = "university";
	const AVCR = "avcr";
	const LIBRARY = "library";
	const HOSPITAL = "hospital";
	const OTHER = "other";
	const EDUID_IDP_GROUP = "http://eduid.cz/uri/idp-group/";

	const HOSTEL_ENTITY_ID = "https://idp.hostel.eduid.cz/idp/shibboleth";

	const INTERFACE_PROPNAME = "interface";
	const CESNET_ELIGIBLE_LAST_SEEN_ATTR = "cesnetEligibleLastSeenAttr";
	const DEFAULT_ATTR_NAME = 'isCesnetEligibleLastSeen';

	private $cesnetEligibleLastSeen;
	private $cesnetEligibleLastSeenAttr;

	private $metadata;
	private $entityCategory;
	private $eduPersonScopedAffiliation = array();


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

		$this->cesnetEligibleLastSeenAttr = $config[self::CESNET_ELIGIBLE_LAST_SEEN_ATTR];
	}

	public function process(&$request)
	{
		assert('is_array($request)');
		$user = $request['perun']['user'];

		$this->metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
		$sourceIdpMeta = $this->metadata->getMetaData( $request['saml:sp:IdP'], 'saml20-idp-remote');

		if (isset($sourceIdpMeta['EntityAttributes']['http://macedir.org/entity-category'])) {
			$entityCategoryAttributes = $sourceIdpMeta['EntityAttributes']['http://macedir.org/entity-category'];
		} else {
			SimpleSAML\Logger::error("cesnet:IsCesnetEligible - There are no element with name 'EntityAttributes' "
				. "and subelement with name 'http://macedir.org/entity-category' in metadata for IdP with entityId "
				. $request['saml:sp:IdP'] . "!");
			$entityCategoryAttributes = array();
		}

		if (isset($request['Attributes']['eduPersonScopedAffiliation'])) {
			$this->eduPersonScopedAffiliation = $request['Attributes']['eduPersonScopedAffiliation'];
		} else {
			SimpleSAML\Logger::error("cesnet:IsCesnetEligible - Attribute with name 'eduPersonScopedAffiliation' did not received from IdP!");
		}

		foreach ($entityCategoryAttributes as $entityCategoryAttribute) {
			if (substr($entityCategoryAttribute, 0, strlen(self::EDUID_IDP_GROUP)) === self::EDUID_IDP_GROUP) {
				$this->entityCategory = substr($entityCategoryAttribute, strlen(self::EDUID_IDP_GROUP), strlen($entityCategoryAttribute) - strlen(self::EDUID_IDP_GROUP));
			}
		}

		$isHostelVerified = false;
		if ($request['saml:sp:IdP'] === self::HOSTEL_ENTITY_ID && isset($request['Attributes']['loa'])
			&& $request['Attributes']['loa'][0] == 2) {
			$isHostelVerified = true;
			SimpleSAML\Logger::debug("cesnet:IsCesnetEligible - The user was verified by Hostel.");
		}

		try {
			$rpcConnector = (new sspmod_perun_AdapterRpc())->getConnector();
			$this->cesnetEligibleLastSeen = $rpcConnector->get('attributesManager', 'getAttribute', array(
				'user' => $user->getId(),
				'attributeName' => $this->cesnetEligibleLastSeenAttr,
			));

			if ((!empty($this->eduPersonScopedAffiliation) && !is_null($this->entityCategory) && $this->isCesnetEligible())
				|| $isHostelVerified) {
				$this->cesnetEligibleLastSeen['value'] = date("Y-m-d H:i:s");
				$rpcConnector->post('attributesManager', 'setAttribute', array(
					'user' => $user->getId(),
					'attribute' => $this->cesnetEligibleLastSeen,
				));
			}
		} catch (Exception $ex) {
			SimpleSAML\Logger::warning("cesnet:IsCesnetEligible - " . $ex->getMessage());
		}

		if ($this->cesnetEligibleLastSeen['value'] != null) {
			$request['Attributes'][$this->attrName] = array($this->cesnetEligibleLastSeen['value']);
		}
	}

	/**
	 * Return true if combination of user attributes and IdP metadata attributes pass through the eduid filter, False if not
	 * @return bool True if combination of attributes pass through the filter, else False
	 */
	private function isCesnetEligible() {
		if ($this->entityCategory === self::UNIVERSITY) {
			foreach ($this->eduPersonScopedAffiliation as $affiliation) {
				if (preg_match("/(^employee@.+\.cz$)|(^faculty@.+\.cz$)|(^member@.+\.cz$)|(^student@.+\.cz$)|(^staff@.+\.cz$)/", $affiliation, $matches)) {
					return true;
				}
			}
		} elseif ($this->entityCategory === self::AVCR) {
			foreach ($this->eduPersonScopedAffiliation as $affiliation) {
				if (preg_match("/(^member@.+\.cz$)|(^staff@.+\.cz$)/", $affiliation, $matches)) {
					return true;
				}
			}
		} elseif ($this->entityCategory === self::LIBRARY) {
			foreach ($this->eduPersonScopedAffiliation as $affiliation) {
				if (preg_match("/(^employee@.+\.cz$)|(^staff@.+\.cz$)/", $affiliation, $matches)) {
					return true;
				}
			}
		} elseif ($this->entityCategory === self::HOSPITAL) {
			foreach ($this->eduPersonScopedAffiliation as $affiliation) {
				if (preg_match("/(^employee@.+\.cz$)|(^staff@.+\.cz$)/", $affiliation, $matches)) {
					return true;
				}
			}
		} elseif ($this->entityCategory === self::OTHER) {
			foreach ($this->eduPersonScopedAffiliation as $affiliation) {
				if (preg_match("/(^employee@.+\.cz$)|(^member@.+\.cz$)|(^employee@bbmri-eric\.eu$)|(^member@bbmri-eric\.eu$)/", $affiliation, $matches)) {
					return true;
				}
			}
		}
		return false;
	}
}
