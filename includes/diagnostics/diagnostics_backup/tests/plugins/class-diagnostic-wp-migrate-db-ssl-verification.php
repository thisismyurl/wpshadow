<?php
/**
 * WP Migrate DB SSL Verification Diagnostic
 *
 * WP Migrate DB SSL verification disabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.381.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Migrate DB SSL Verification Diagnostic Class
 *
 * @since 1.381.0000
 */
class Diagnostic_WpMigrateDbSslVerification extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-ssl-verification';
	protected static $title = 'WP Migrate DB SSL Verification';
	protected static $description = 'WP Migrate DB SSL verification disabled';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WPMDB_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$settings = get_option( 'wpmdb_settings', array() );
		
		// Check 1: SSL verification enabled
		$verify_ssl = isset( $settings['verify_ssl'] ) ? $settings['verify_ssl'] : 'yes';
		if ( 'no' === $verify_ssl ) {
			$issues[] = __( 'SSL verification disabled (MITM attack risk)', 'wpshadow' );
		}
		
		// Check 2: Certificate validation
		$verify_certificate = isset( $settings['verify_certificate'] ) ? $settings['verify_certificate'] : 'yes';
		if ( 'no' === $verify_certificate ) {
			$issues[] = __( 'Certificate validation disabled (untrusted connections)', 'wpshadow' );
		}
		
		// Check 3: Force HTTPS for remote connections
		$force_https = get_option( 'wpmdb_force_https', 'no' );
		if ( 'no' === $force_https ) {
			$issues[] = __( 'HTTP connections allowed (unencrypted data)', 'wpshadow' );
		}
		
		// Check 4: Connection key strength
		$connection_key = get_option( 'wpmdb_connection_key', '' );
		if ( strlen( $connection_key ) < 32 ) {
			$issues[] = __( 'Weak connection key (brute force risk)', 'wpshadow' );
		}
		
		// Check 5: Encryption for sensitive data
		$encrypt_data = get_option( 'wpmdb_encrypt_data', 'no' );
		if ( 'no' === $encrypt_data ) {
			$issues[] = __( 'Data not encrypted in transit (sensitive info exposed)', 'wpshadow' );
		}
		
		// Check 6: Connection timeout
		$timeout = isset( $settings['timeout'] ) ? $settings['timeout'] : 300;
		if ( $timeout > 600 ) {
			$issues[] = sprintf( __( '%d second timeout (long exposure)', 'wpshadow' ), $timeout );
		}
		
		// Check 7: Backup encryption
		$encrypt_backup = get_option( 'wpmdb_encrypt_backup', 'no' );
		if ( 'no' === $encrypt_backup ) {
			$issues[] = __( 'Backups not encrypted (data breach risk)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 65;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 80;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 73;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of SSL verification issues */
				__( 'WP Migrate DB has %d SSL security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-ssl-verification',
		);
	}
}
