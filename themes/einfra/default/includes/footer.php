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
                <div class="col-md-4 logo">
                    <img src="<?php echo Module::getModuleUrl('cesnet/res/img/footer_logo.png'); ?>" >
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col col-sm-6">
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
                        <div class="col col-sm-6">
                            <h2><?php echo $this->t('{cesnet:einfra:footer_helpdesk}'); ?></h2>
                            TEL: +420 234 680 222<br>
                            GSM: +420 602 252 531<br>
                            <a href="mailto:support@e-infra.cz">support@e-infra.cz</a>
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
