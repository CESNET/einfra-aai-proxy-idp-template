<?php

/**
 * This is simple example of template for perun Discovery service
 *
 * Allow type hinting in IDE
 * @var sspmod_perun_DiscoTemplate $this
 */

const URN_CESNET_PROXYIDP_FILTER = "urn:cesnet:proxyidp:filter:";
const URN_CESNET_PROXYIDP_EFILTER = "urn:cesnet:proxyidp:efilter:";
const URN_CESNET_PROXYIDP_IDPENTITYID = "urn:cesnet:proxyidp:idpentityid:";

$metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
$idpmeta = $metadata->getMetaData('https://login.cesnet.cz/idp/', 'saml20-idp-hosted');

$filter = null;
$efilter = null;
$idpEntityId = null;
$authContextClassRef = null;
$defaultFilter = null;
$defaultEFilter = null;

if (isset($this->data['AuthnContextClassRef'])) {
	$authContextClassRef = $this->data['AuthnContextClassRef'];
}

if (isset($idpmeta['defaultFilter'])) {
	$defaultFilter = $idpmeta['defaultFilter'];
}
if (isset($idpmeta['defaultEFilter'])) {
	$defaultEFilter = $idpmeta['defaultEFilter'];
}


$this->data['jquery'] = array('core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->includeAtTemplateBase('includes/header.php');

if ($authContextClassRef != null) {
	foreach ($authContextClassRef as $value) {
		if (substr($value, 0, strlen(URN_CESNET_PROXYIDP_FILTER)) === URN_CESNET_PROXYIDP_FILTER) {
			$filter = substr($value, strlen(URN_CESNET_PROXYIDP_FILTER), strlen($value));
		} elseif (substr($value, 0, strlen(URN_CESNET_PROXYIDP_EFILTER)) === URN_CESNET_PROXYIDP_EFILTER) {
			$efilter = substr($value, strlen(URN_CESNET_PROXYIDP_EFILTER), strlen($value));
		} elseif (substr($value, 0, strlen(URN_CESNET_PROXYIDP_IDPENTITYID)) === URN_CESNET_PROXYIDP_IDPENTITYID) {
			$idpEntityId = substr($value, strlen(URN_CESNET_PROXYIDP_IDPENTITYID), strlen($value));
		}
	}
}

if ($idpEntityId != null) {
	$url = $this->getContinueUrl($idpEntityId);

	SimpleSAML\Utils\HTTP::redirectTrustedURL($url);
	exit;
} else {
	$url = $this->getContinueUrlWithoutIdPEntityId();
	if ($efilter != null) {
		header('Location: https://ds.eduid.cz/wayf.php' . $url . '&efilter=' . $efilter);
		exit;
	} elseif ($filter != null) {
		header('Location: https://ds.eduid.cz/wayf.php' . $url . '&filter=' . $filter);
		exit;
	} elseif (isset($this->data['originalsp']['efilter'])) {
		$efilter = $this->data['originalsp']['efilter'];
		header('Location: https://ds.eduid.cz/wayf.php' . $url . '&efilter=' . $efilter);
		exit;
	} elseif (isset($this->data['originalsp']['filter'])) {
		$filter = $this->data['originalsp']['filter'];
		header('Location: https://ds.eduid.cz/wayf.php' . $url . '&filter=' . $filter);
		exit;
	} elseif ($defaultEFilter != null) {
		header('Location: https://ds.eduid.cz/wayf.php' . $url . '&efilter=' . $defaultEFilter);
		exit;
	} elseif ($defaultFilter != null) {
		header('Location: https://ds.eduid.cz/wayf.php' . $url . '&filter=' . $defaultFilter);
		exit;
	} else {
		throw new SimpleSAML_Error_Exception("cesnet:disco-tpl: Filter did not set. ");
	}
}

$this->includeAtTemplateBase('includes/footer.php');
