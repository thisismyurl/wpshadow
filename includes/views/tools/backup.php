<?php
/**
 * WPShadow Backup Tool
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

<?php
/**
 * Legacy Backup Tool Wrapper
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
$backup_retention_days   = get_option( 'wpshadow_backup_retention_days', 7 );
