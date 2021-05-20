<?php

use SimpleSAML\Metadata\MetaDataStorageHandler;
use SimpleSAML\Utils\HTTP;
use SimpleSAML\Configuration;
use SimpleSAML\Logger;
use SimpleSAML\Error\Exception;
use SimpleSAML\Module\perun\Disco;
use SimpleSAML\Module\perun\DiscoTemplate;
use SimpleSAML\Module\perun\model\WarningConfiguration;

/**
 * This is simple example of template for perun Discovery service
 *
 * Allow type hinting in IDE
 * @var DiscoTemplate $this
 */

$canContinue = false;

if (isset($_POST['continue'])) {
    $canContinue = true;
}

const URN_CESNET_PROXYIDP_FILTER = "urn:cesnet:proxyidp:filter:";
const URN_CESNET_PROXYIDP_EFILTER = "urn:cesnet:proxyidp:efilter:";
const URN_CESNET_PROXYIDP_IDPENTITYID = "urn:cesnet:proxyidp:idpentityid:";

$metadata = MetaDataStorageHandler::getMetadataHandler();
$idpmeta = $metadata->getMetaData('https://login.cesnet.cz/idp/', 'saml20-idp-hosted');

$filter = null;
$efilter = null;
$idpEntityId = null;
$authContextClassRef = null;
$defaultFilter = null;
$defaultEFilter = null;

$wayfConfig = $this->data[Disco::WAYF];
$warningAttributes = $this->data[Disco::WARNING_ATTRIBUTES];

if ($warningAttributes->isEnabled()) {
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

$this->data['jquery'] = ['core' => true, 'ui' => true, 'css' => true];
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

    HTTP::redirectTrustedURL($url);
    exit;
} else {
    $url = $this->getContinueUrlWithoutIdPEntityId();

    if ($warningAttributes->isEnabled()) {
        if ($warningAttributes->getType() === WarningConfiguration::WARNING_TYPE_INFO) {
            echo '<div class="alert alert-info">';
        } elseif ($warningAttributes->getType() === WarningConfiguration::WARNING_TYPE_WARNING) {
            echo '<div class="alert alert-warning">';
        } elseif ($warningAttributes->getType() === WarningConfiguration::WARNING_TYPE_ERROR) {
            echo '<div class="alert alert-danger">';
        }
        echo '<h4> <strong>' . $warningAttributes->getTitle() . '</strong> </h4>';
        echo $warningAttributes->getText();
        echo '</div>';
        if (in_array($warningAttributes->getType(),
            [WarningConfiguration::WARNING_TYPE_INFO, WarningConfiguration::WARNING_TYPE_WARNING], true)) {
            echo '<form method="POST">';
            echo '<input class="btn btn-lg btn-primary btn-block" type="submit" name="continue" value="Continue" />';
            echo '</form>';
        }
    } else {
        $canContinue = true;
    }

    if ($canContinue &&
        (!$warningAttributes->isEnabled() ||
            in_array($warningAttributes->getType(),
                [WarningConfiguration::WARNING_TYPE_INFO, WarningConfiguration::WARNING_TYPE_WARNING],
                true)
        )) {
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
            throw new Exception('cesnet:disco-tpl: Filter did not set. ');
        }
    }
}

$this->includeAtTemplateBase('includes/footer.php');
