<?php
/**
 * WP Migrate DB Connection Security Diagnostic
 *
 * WP Migrate DB connections not encrypted.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.379.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Migrate DB Connection Security Diagnostic Class
 *
 * @since 1.379.0000
 */
class Diagnostic_WpMigrateDbConnectionSecurity extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-connection-security';
	protected static $title = 'WP Migrate DB Connection Security';
	protected static $description = 'WP Migrate DB connections not encrypted';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WPMDB_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Connection encryption
		$encryption = get_option( 'wpmdb_connection_encryption_enabled', 0 );
		if ( ! $encryption ) {
			$issues[] = 'Connection encryption not enabled';
		}

		// Check 2: SSL verification
		$ssl = get_option( 'wpmdb_ssl_verification_enabled', 0 );
		if ( ! $ssl ) {
			$issues[] = 'SSL verification not enabled';
		}

		// Check 3: API key configuration
		$api_key = get_option( 'wpmdb_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = 'API key not configured';
		}

		// Check 4: Connection authentication
		$auth = get_option( 'wpmdb_connection_auth_enabled', 0 );
		if ( ! $auth ) {
			$issues[] = 'Connection authentication not enabled';
		}

		// Check 5: Data validation
		$validation = get_option( 'wpmdb_data_validation_enabled', 0 );
		if ( ! $validation ) {
			$issues[] = 'Data validation not enabled';
		}

		// Check 6: Connection timeout
		$timeout = absint( get_option( 'wpmdb_connection_timeout_seconds', 0 ) );
		if ( $timeout <= 0 ) {
			$issues[] = 'Connection timeout not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 55;
			$threat_multiplier = 6;
			$max_threat = 85;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d connection security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-connection-security',
			);
		}

		return null;
	}
}
