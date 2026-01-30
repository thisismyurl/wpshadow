<?php
/**
 * WP Migrate DB Compatibility Mode Diagnostic
 *
 * WP Migrate DB compatibility settings wrong.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.382.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Migrate DB Compatibility Mode Diagnostic Class
 *
 * @since 1.382.0000
 */
class Diagnostic_WpMigrateDbCompatibilityMode extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-compatibility-mode';
	protected static $title = 'WP Migrate DB Compatibility Mode';
	protected static $description = 'WP Migrate DB compatibility settings wrong';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WPMDB_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Compatibility mode
		$compat_mode = get_option( 'wpmdb_compatibility_plugin_list', array() );
		if ( empty( $compat_mode ) ) {
			$issues[] = __( 'No compatibility plugins listed', 'wpshadow' );
		}

		// Check 2: Replace strings validation
		$replace_old = get_option( 'wpmdb_replace_old', array() );
		$replace_new = get_option( 'wpmdb_replace_new', array() );

		if ( count( $replace_old ) !== count( $replace_new ) ) {
			$issues[] = __( 'Mismatched find/replace strings', 'wpshadow' );
		}

		// Check 3: Backup before migrate
		$backup = get_option( 'wpmdb_backup_before_migrate', '0' );
		if ( '0' === $backup ) {
			$issues[] = __( 'No backup before migrate (data loss risk)', 'wpshadow' );
		}

		// Check 4: SSL verification
		$verify_ssl = get_option( 'wpmdb_verify_ssl', '1' );
		if ( '0' === $verify_ssl ) {
			$issues[] = __( 'SSL verification disabled (MITM risk)', 'wpshadow' );
		}

		// Check 5: Connection key
		$connection_key = get_option( 'wpmdb_key', '' );
		if ( ! empty( $connection_key ) && strlen( $connection_key ) < 32 ) {
			$issues[] = __( 'Weak connection key (brute force risk)', 'wpshadow' );
		}

		// Check 6: Delay between requests
		$delay = get_option( 'wpmdb_delay_between_requests', 0 );
		if ( $delay === 0 ) {
			$issues[] = __( 'No delay (server overload risk)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'WP Migrate DB has %d compatibility issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-compatibility-mode',
		);
	}
}
