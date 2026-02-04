<?php
/**
 * Legacy Backup Tool Wrapper
 *
 * Delegates to the WPShadow Vault Light tool view.
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( WPSHADOW_PATH . 'includes/views/tools/vault-light.php' ) ) {
	require WPSHADOW_PATH . 'includes/views/tools/vault-light.php';
}
