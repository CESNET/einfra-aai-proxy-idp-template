<?php declare(strict_types=1);

use SimpleSAML\Module;

if (! empty($this->data['htmlinject']['htmlContentPost'])) {
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
                    <img src="<?php echo Module::getModuleUrl('cesnet/res/img/footer_logo.png') ?>" >
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col col-sm-6">
                            <h2> <?php echo $this->t('{cesnet:einfra:footer_other_links}'); ?></h2>
                            <ul>
                                <li><?php echo '<a href="' . $this->t('{cesnet:links:metacentrum_link}') . '">' .
                                        $this->t('{cesnet:links:metacentrum_name}') . '</a>'; ?></li>
                                <li><?php echo '<a href="' . $this->t('{cesnet:links:data_storage_link}') . '">' .
                                        $this->t('{cesnet:links:data_storage_name}') . '</a>'; ?></li>
                                <li><?php echo '<a href="' . $this->t('{cesnet:links:meetings_link}') . '">' .
                                        $this->t('{cesnet:links:meetings_name}') . '</a>'; ?></li>
                                <li><?php echo '<a href="' . $this->t('{cesnet:links:certificate_link}') . '">' .
                                        $this->t('{cesnet:links:certificate_name}') . '</a>'; ?></li>
                                <li><?php echo '<a href="' . $this->t('{cesnet:links:services_link}') . '">' .
                                        $this->t('{cesnet:links:services_name}') . '</a>'; ?></li>
                                <li><?php echo '<a href="' . $this->t('{cesnet:links:data_protection_link}') . '">' .
                                        $this->t('{cesnet:links:data_protection_name}') . '</a>'; ?></li>
                            </ul>
                        </div>
                        <div class="col col-sm-6">
                            <h2><?php echo $this->t('{cesnet:einfra:footer_helpdesk}'); ?></h2>
                            TEL: +420 234 680 222<br>
                            GSM: +420 602 252 531<br>
                            <a href="mailto:login@cesnet.cz">login@cesnet.cz</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col col-sm-12 copyright">
                    © 1991– <?php echo date('Y'); ?> | CESNET, z. s. p. o.
                </div>
            </div>
        </div>
    </footer>
</div><!-- #footer -->

</body>
</html>
