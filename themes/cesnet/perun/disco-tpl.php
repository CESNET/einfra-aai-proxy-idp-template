<?php

/**
 * This is simple example of template for perun Discovery service
 *
 * Allow type hinting in IDE
 * @var sspmod_perun_DiscoTemplate $this
 */

$canContinue = false;

if(isset($_POST['continue'])) {
	$canContinue = true;
}

const URN_CESNET_PROXYIDP_FILTER = "urn:cesnet:proxyidp:filter:";
const URN_CESNET_PROXYIDP_EFILTER = "urn:cesnet:proxyidp:efilter:";
const URN_CESNET_PROXYIDP_IDPENTITYID = "urn:cesnet:proxyidp:idpentityid:";

const WARNING_CONFIG_FILE_NAME = 'config-warning.php';
const WARNING_IS_ON = 'isOn';
const WARNING_USER_CAN_CONTINUE = 'userCanContinue';
const WARNING_TITLE = 'title';
const WARNING_TEXT = 'text';

$metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
$idpmeta = $metadata->getMetaData('https://login.cesnet.cz/idp/', 'saml20-idp-hosted');

$filter = null;
$efilter = null;
$idpEntityId = null;
$authContextClassRef = null;
$defaultFilter = null;
$defaultEFilter = null;

$warningIsOn = false;
$warningUserCanContinue = null;
$warningTitle = null;
$warningText = null;
$config = null;

try {
	$config = SimpleSAML_Configuration::getConfig(WARNING_CONFIG_FILE_NAME);
} catch (Exception $ex) {
	SimpleSAML\Logger::warning("cesnet:disco-tpl: missing or invalid config-warning file");
}

if ($config != null) {
	try {
		$warningIsOn = $config->getBoolean(WARNING_IS_ON);
	} catch (Exception $ex) {
		SimpleSAML\Logger::warning("cesnet:disco-tpl: missing or invalid isOn parameter in config-warning file");
		$warningIsOn = false;
	}
}

if ($warningIsOn) {
	try {
		$warningUserCanContinue = $config->getBoolean(WARNING_USER_CAN_CONTINUE);
	} catch (Exception $ex) {
		SimpleSAML\Logger::warning("cesnet:disco-tpl: missing or invalid userCanContinue parameter in config-warning file");
		$warningUserCanContinue = true;
	}
	try {
		$warningTitle = $config->getString(WARNING_TITLE);
		$warningText = $config->getString(WARNING_TEXT);
		if (empty($warningTitle) || empty($warningText)) {
			throw new Exception();
		}
	} catch (Exception $ex) {
		SimpleSAML\Logger::warning("cesnet:disco-tpl: missing or invalid title or text in config-warning file");
		$canContinue = true;
		$warningIsOn = false;
	}
}

if($warningIsOn) {
	$this->data['header'] = $this->t('{cesnet:einfra:warning}');
}

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

	if ($warningIsOn) {
		if ($warningUserCanContinue) {
			echo '<div class="alert alert-warning">';
			echo '<h4> <strong>' . $warningTitle . '</strong> </h4>';
			echo $warningText;
			echo '</div>';
			echo '<form method="POST">';
			echo '<input class="btn btn-lg btn-primary btn-block" type="submit" name="continue" value="Continue" />';
			echo '</form>';
		} else {
			echo '<div class="alert alert-danger">';
			echo '<h4> <strong>' . $warningTitle . '</strong> </h4>';
			echo $warningText;
			echo '</div>';
		}
	} else {
		$canContinue = true;
	}

	if ($canContinue && (!$warningIsOn || $warningUserCanContinue)) {
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
}

$this->includeAtTemplateBase('includes/footer.php');
