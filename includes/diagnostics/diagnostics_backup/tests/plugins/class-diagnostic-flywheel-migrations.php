<?php
/**
 * Flywheel Migrations Diagnostic
 *
 * Flywheel Migrations needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1004.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flywheel Migrations Diagnostic Class
 *
 * @since 1.1004.0000
 */
class Diagnostic_FlywheelMigrations extends Diagnostic_Base {

	protected static $slug = 'flywheel-migrations';
	protected static $title = 'Flywheel Migrations';
	protected static $description = 'Flywheel Migrations needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check if on Flywheel hosting
		$is_flywheel = defined( 'FLYWHEEL_CONFIG_DIR' ) || isset( $_SERVER['FLYWHEEL_SITE'] );
		if ( ! $is_flywheel ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check for migration lock
		$migration_lock = get_option( 'flywheel_migration_lock', false );
		if ( $migration_lock ) {
			$issues[] = 'migration_lock_active';
			$threat_level += 15;
		}

		// Check database prefix
		global $wpdb;
		if ( $wpdb->prefix !== 'wp_' ) {
			$issues[] = 'non_standard_db_prefix';
			$threat_level += 10;
		}

		// Check for conflicting cache plugins
		$conflicting_plugins = array(
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-super-cache.php',
		);
		foreach ( $conflicting_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$issues[] = 'conflicting_cache_plugin';
				$threat_level += 15;
				break;
			}
		}

		// Check for staging environment indicators
		$home_url = get_option( 'home' );
		if ( strpos( $home_url, 'staging' ) !== false || strpos( $home_url, 'dev' ) !== false ) {
			$issues[] = 'staging_urls_detected';
			$threat_level += 10;
		}

		// Check Flywheel cache status
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			$issues[] = 'cache_not_properly_configured';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of migration issues */
				__( 'Flywheel migration has configuration issues: %s. This can cause migration failures and site functionality problems.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/flywheel-migrations',
			);
		}
		
		return null;
	}
}
