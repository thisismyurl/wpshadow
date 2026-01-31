<?php
/**
 * Wp Migrate Db Pro Cli Diagnostic
 *
 * Wp Migrate Db Pro Cli issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1062.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Migrate Db Pro Cli Diagnostic Class
 *
 * @since 1.1062.0000
 */
class Diagnostic_WpMigrateDbProCli extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-pro-cli';
	protected static $title = 'Wp Migrate Db Pro Cli';
	protected static $description = 'Wp Migrate Db Pro Cli issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WPMDB_Pro_CLI' ) && ! defined( 'WPMDB_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: WP-CLI available
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			$issues[] = 'WP-CLI not available (CLI addon cannot function)';
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WP Migrate DB Pro CLI issues: ' . implode( ', ', $issues ),
				'severity'    => 40,
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-pro-cli',
			);
		}

		// Check 2: CLI addon enabled
		$cli_enabled = get_option( 'wpmdb_cli_enabled', '0' );
		if ( '0' === $cli_enabled ) {
			$issues[] = 'CLI addon installed but not enabled';
		}

		// Check 3: Automated migration security
		$require_confirm = get_option( 'wpmdb_cli_require_confirm', '1' );
		if ( '0' === $require_confirm ) {
			$issues[] = 'CLI migrations run without confirmation (dangerous)';
		}

		// Check 4: CLI migration logging
		$cli_logging = get_option( 'wpmdb_cli_logging', '1' );
		if ( '0' === $cli_logging ) {
			$issues[] = 'CLI operations not logged';
		}

		// Check 5: Scheduled migrations
		$scheduled = wp_get_scheduled_event( 'wpmdb_cli_migration' );
		if ( $scheduled ) {
			$backup_before = get_option( 'wpmdb_cli_backup_before', '1' );
			if ( '0' === $backup_before ) {
				$issues[] = 'scheduled migrations without automatic backups';
			}
		}

		// Check 6: Error notifications
		$error_email = get_option( 'wpmdb_cli_error_email', '' );
		if ( ! empty( $cli_enabled ) && empty( $error_email ) ) {
			$issues[] = 'no email configured for CLI errors';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WP Migrate DB Pro CLI issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-pro-cli',
			);
		}

		return null;
	}
}
