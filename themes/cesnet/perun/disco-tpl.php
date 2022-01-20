<?php

declare(strict_types=1);

use SimpleSAML\Error\Exception;
use SimpleSAML\Metadata\MetaDataStorageHandler;
use SimpleSAML\Module\perun\Disco;
use SimpleSAML\Module\perun\DiscoTemplate;
use SimpleSAML\Module\perun\model\WarningConfiguration;
use SimpleSAML\Utils\HTTP;

/**
 * This is simple example of template for perun Discovery service.
 *
 * Allow type hinting in IDE
 *
 * @var DiscoTemplate $this
 */
$canContinue = false;

if (isset($_POST['continue'])) {
    $canContinue = true;
}

const URN_CESNET_PROXYIDP_FILTER = 'urn:cesnet:proxyidp:filter:';
const URN_CESNET_PROXYIDP_EFILTER = 'urn:cesnet:proxyidp:efilter:';
const URN_CESNET_PROXYIDP_IDPENTITYID = 'urn:cesnet:proxyidp:idpentityid:';

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

$this->data['jquery'] = [
    'core' => true,
    'ui' => true,
    'css' => true,
];
$this->includeAtTemplateBase('includes/header.php');

if (null !== $authContextClassRef) {
    foreach ($authContextClassRef as $value) {
        if (URN_CESNET_PROXYIDP_FILTER === substr($value, 0, strlen(URN_CESNET_PROXYIDP_FILTER))) {
            $filter = substr($value, strlen(URN_CESNET_PROXYIDP_FILTER), strlen($value));
        } elseif (URN_CESNET_PROXYIDP_EFILTER === substr($value, 0, strlen(URN_CESNET_PROXYIDP_EFILTER))) {
            $efilter = substr($value, strlen(URN_CESNET_PROXYIDP_EFILTER), strlen($value));
        } elseif (URN_CESNET_PROXYIDP_IDPENTITYID === substr($value, 0, strlen(URN_CESNET_PROXYIDP_IDPENTITYID))) {
            $idpEntityId = substr($value, strlen(URN_CESNET_PROXYIDP_IDPENTITYID), strlen($value));
        }
    }
}

if (null !== $idpEntityId) {
    $url = $this->getContinueUrl($idpEntityId);

    HTTP::redirectTrustedURL($url);
    exit;
}
    $url = $this->getContinueUrlWithoutIdPEntityId();

    if ($warningAttributes->isEnabled()) {
        if (WarningConfiguration::WARNING_TYPE_INFO === $warningAttributes->getType()) {
            echo '<div class="alert alert-info">';
        } elseif (WarningConfiguration::WARNING_TYPE_WARNING === $warningAttributes->getType()) {
            echo '<div class="alert alert-warning">';
        } elseif (WarningConfiguration::WARNING_TYPE_ERROR === $warningAttributes->getType()) {
            echo '<div class="alert alert-danger">';
        }
        echo '<h4> <strong>' . $warningAttributes->getTitle() . '</strong> </h4>';
        echo $warningAttributes->getText();
        echo '</div>';
        if (in_array(
            $warningAttributes->getType(),
            [WarningConfiguration::WARNING_TYPE_INFO, WarningConfiguration::WARNING_TYPE_WARNING],
            true
        )) {
            echo '<form method="POST">';
            echo '<input class="btn btn-lg btn-primary btn-block" type="submit" name="continue" value="Continue" />';
            echo '</form>';
        }
    } else {
        $canContinue = true;
    }

    if ($canContinue &&
        (
            ! $warningAttributes->isEnabled() ||
            in_array(
                $warningAttributes->getType(),
                [WarningConfiguration::WARNING_TYPE_INFO, WarningConfiguration::WARNING_TYPE_WARNING],
                true
            )
        )) {
        if (null !== $efilter) {
            header('Location: https://ds.eduid.cz/wayf.php' . $url . '&efilter=' . $efilter);
            exit;
        }
        if (null !== $filter) {
            header('Location: https://ds.eduid.cz/wayf.php' . $url . '&filter=' . $filter);
            exit;
        }
        if (isset($this->data['originalsp']['efilter'])) {
            $efilter = $this->data['originalsp']['efilter'];
            header('Location: https://ds.eduid.cz/wayf.php' . $url . '&efilter=' . $efilter);
            exit;
        }
        if (isset($this->data['originalsp']['filter'])) {
            $filter = $this->data['originalsp']['filter'];
            header('Location: https://ds.eduid.cz/wayf.php' . $url . '&filter=' . $filter);
            exit;
        }
        if (null !== $defaultEFilter) {
            header('Location: https://ds.eduid.cz/wayf.php' . $url . '&efilter=' . $defaultEFilter);
            exit;
        }
        if (null !== $defaultFilter) {
            header('Location: https://ds.eduid.cz/wayf.php' . $url . '&filter=' . $defaultFilter);
            exit;
        }
        throw new Exception('cesnet:disco-tpl: Filter did not set. ');
    }

$this->includeAtTemplateBase('includes/footer.php');
