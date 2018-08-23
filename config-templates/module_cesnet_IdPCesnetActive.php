<?php

/**
 * This is example configuration of Ssspmod_cesnet_Auth_Process_IdPCesnetActive process filter.
 * Copy this file to default config directory and edit the properties.
 *
 * copy command (from SimpleSAML base dir)
 * cp modules/perun/config-templates/module_cesnet_IdPCesnetActive.php config/
 */
$config = array(

	/**
	 * hostname of perun ldap with ldap(s):// at the beginning.
	 */
	'ldap.hostname' => 'ldaps://perun.inside.cz',

	/**
	 * ldap credentials if ldap search is protected. If it is null or not set at all. No user is used for bind.
	 */
	'ldap.username' => 'user',
	'ldap.password' => 'password'

);