<?php

/**
 * This is simple example of template for perun Discovery service
 *
 * Allow type hinting in IDE
 * @var sspmod_perun_DiscoTemplate $this
 */

$this->data['jquery'] = array('core' => TRUE, 'ui' => TRUE, 'css' => TRUE);


$this->includeAtTemplateBase('includes/header.php');

$url = $this->getContinueUrlWithoutIdPEntityId();

if (isset($this->data['originalsp']['filter'])){
	$filter = $this->data['originalsp']['filter'] ;
} else {
	$filter = 'eyJ2ZXIiOiIyIiwiYWxsb3dGZWVkcyI6eyJlZHVHQUlOIjp7fX19';
}

header('Location: https://ds.eduid.cz/wayf.php' . $url . '&filter=' . $filter);

exit;

$this->includeAtTemplateBase('includes/footer.php');
