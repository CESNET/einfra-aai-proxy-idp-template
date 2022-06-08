<?php declare(strict_types=1);

use SimpleSAML\Module;

if (!empty($this->data['htmlinject']['htmlContentPost'])) {
    foreach ($this->data['htmlinject']['htmlContentPost'] as $c) {
        echo $c;
    }
}

?>

</div><!-- #content -->
</div><!-- #wrap -->

<div id="footer">
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-push-4 col-xs-12">
                    <div class="row">
                        <div class="col col-sm-6 col-xs-12">
                            <h2> <?php echo $this->t('{cesnet:einfra:footer_other_links}'); ?></h2>
                            <ul>
                                <li><?php echo '<a href="' . $this->t('{cesnet:einfra_links:einfra_link}') . '">' .
                                        $this->t('{cesnet:einfra_links:einfra_name}') . '</a>'; ?></li>
                                <li><?php echo '<a href="' . $this->t('{cesnet:einfra_links:cesnet_link}') . '">' .
                                        $this->t('{cesnet:einfra_links:cesnet_name}') . '</a>'; ?></li>
                                <li><?php echo '<a href="' . $this->t('{cesnet:einfra_links:ceritsc_link}') . '">' .
                                        $this->t('{cesnet:einfra_links:ceritsc_name}') . '</a>'; ?></li>
                                <li><?php echo '<a href="' . $this->t('{cesnet:einfra_links:it4i_link}') . '">' .
                                        $this->t('{cesnet:einfra_links:it4i_name}') . '</a>'; ?></li>
                            </ul>
                        </div>
                        <div class="col col-sm-6 col-xs-12">
                            <h2><?php echo $this->t('{cesnet:einfra:footer_helpdesk}'); ?></h2>
                            TEL: +420 234 680 222<br>
                            GSM: +420 602 252 531<br>
                            <a href="mailto:support@e-infra.cz">support@e-infra.cz</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-md-pull-8 col-xs-12">
                    <div class="row">
                        <div id="footer-logo-cesnet" class="col-md-12 col-sm-4 col-xs-12 footer-logo-wrapper">
                            <img src="<?php echo Module::getModuleUrl('cesnet/res/img/cesnet.svg') ?>"/>
                        </div>
                        <div id="footer-logo-cerit" class="col-md-12 col-sm-4 col-xs-12 footer-logo-wrapper">
                            <img src="<?php echo Module::getModuleUrl('cesnet/res/img/cerit.svg') ?>"/>
                        </div>
                        <div id="footer-logo-it4i" class="col-md-12 col-sm-4 col-xs-12 footer-logo-wrapper">
                            <img src="<?php echo Module::getModuleUrl('cesnet/res/img/it4i.svg') ?>"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col col-sm-12 copyright">
                    © <?php echo date('Y'); ?> | <a href="https://www.e-infra.cz">e-INFRA CZ</a>
                </div>
            </div>
        </div>
    </footer>
</div><!-- #footer -->

</body>
</html>
