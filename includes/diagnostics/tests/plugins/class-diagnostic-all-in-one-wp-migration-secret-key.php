<?php
/**
 * All-in-One WP Migration Secret Key Diagnostic
 *
 * AIO WP Migration secret key weak.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.390.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All-in-One WP Migration Secret Key Diagnostic Class
 *
 * @since 1.390.0000
 */
class Diagnostic_AllInOneWpMigrationSecretKey extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-migration-secret-key';
	protected static $title = 'All-in-One WP Migration Secret Key';
	protected static $description = 'AIO WP Migration secret key weak';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AI1WM_PLUGIN_NAME' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Secret key strength
		$secret_key = get_option( 'ai1wm_secret_key', '' );
		if ( ! empty( $secret_key ) && strlen( $secret_key ) < 32 ) {
			$issues[] = sprintf( __( 'Secret key only %d characters (weak)', 'wpshadow' ), strlen( $secret_key ) );
		}
		
		// Check 2: Default secret key
		if ( 'CHANGE_THIS_SECRET_KEY' === $secret_key ) {
			$issues[] = __( 'Using default secret key (critical vulnerability)', 'wpshadow' );
		}
		
		// Check 3: Public downloads
		$public_downloads = get_option( 'ai1wm_public_downloads', 'no' );
		if ( 'yes' === $public_downloads ) {
			$issues[] = __( 'Public downloads enabled (data exposure)', 'wpshadow' );
		}
		
		// Check 4: Download expiration
		$download_expiry = get_option( 'ai1wm_download_expiry', 0 );
		if ( $download_expiry === 0 || $download_expiry > 86400 ) {
			$issues[] = __( 'Download links never expire (security risk)', 'wpshadow' );
		}
		
		// Check 5: IP restriction
		$ip_restriction = get_option( 'ai1wm_ip_restriction', 'no' );
		if ( 'no' === $ip_restriction ) {
			$issues[] = __( 'No IP restriction (unauthorized access)', 'wpshadow' );
		}
		
		// Check 6: Storage location
		$storage_path = get_option( 'ai1wm_storage_path', '' );
		if ( strpos( $storage_path, ABSPATH ) === 0 ) {
			$issues[] = __( 'Backups in web root (direct access possible)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 75;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 87;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 81;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'All-in-One WP Migration has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-migration-secret-key',
		);
	}
}
