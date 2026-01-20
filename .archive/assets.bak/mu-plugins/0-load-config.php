<?php
/**
 * MU Plugin: Load WordPress Configuration Extra
 *
 * This plugin is automatically loaded before regular plugins.
 * It includes the wp-config-extra.php file which contains environment-specific configuration.
 *
 * @package wpshadow
 */

// Load the extra configuration file if it exists
$config_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/wp-config-extra.php';
if ( file_exists( $config_file ) ) {
	require_once $config_file;
}
