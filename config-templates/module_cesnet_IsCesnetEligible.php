<?php

declare(strict_types=1);

/**
 * This is example configuration of SimpleSAMLphp cesnet module IsCecsnetEligible. Copy this file to default config
 * directory and edit the properties.
 *
 * copy command (from SimpleSAML base dir) cp modules/perun/module_cesnet_IsCesnetEligible.php config/
 */
$config = [
    /**
     * hostname of CESNET ldap with ldap(s):// at the beginning.
     */
    'ldap.hostname' => '',

    /**
     * ldap credentials if ldap search is protected. If it is null or not set at all. No user is used for bind.
     */
    'ldap.username' => '',
    'ldap.password' => '',
    'ldap.base' => '',
];
