<?php

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
                    <a href="http://www.cesnet.cz/">
                        <img src="<?php echo Module::getModuleUrl('cesnet/res/img/logo-cesnet.png') ?>"
                             width="250px">
                    </a>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col col-sm-6">
                            <h2> <?php echo $this->t('{cesnet:einfra:footer_other_project}'); ?></h2>
                            <ul>
                                <li>
                                    <a href=
                                       "http://www.cesnet.cz/wp-content/uploads/2014/04/CzechLight-family_Posp%C3%ADchal.pdf">
                                        CzechLight
                                    </a>
                                </li>
                                <li><a href="http://www.ultragrid.cz/en">UltraGrid</a></li>
                                <li><a href="http://www.4kgateway.com/">4k Gateway</a></li>
                                <li><a href="http://shongo.cesnet.cz/">Shongo</a></li>
                                <li>
                                    <a href=
                                       "http://www.cesnet.cz/sluzby/sledovani-provozu-site/sledovani-infrastruktury/">
                                        FTAS a G3
                                    </a>
                                </li>
                                <li><a href="https://www.liberouter.org/">Librerouter</a></li>
                            </ul>
                        </div>
                        <div class="col col-sm-6">
                            <h2><?php echo $this->t('{cesnet:einfra:footer_helpdesk}'); ?></h2>
                            TEL: +420 224 352 994<br>
                            GSM: +420 602 252 531<br>
                            FAX: +420 224 313 211<br>
                            <a href="mailto:login@cesnet.cz">login@cesnet.cz</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col col-sm-12 copyright">
                    © 1991– <?php echo date("Y"); ?> | CESNET, z. s. p. o.
                </div>
            </div>
        </div>
    </footer>
</div><!-- #footer -->

</body>
</html>
