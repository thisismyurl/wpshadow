<?php
/**
 * Wp Sync Db Compatibility Diagnostic
 *
 * Wp Sync Db Compatibility issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1065.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Sync Db Compatibility Diagnostic Class
 *
 * @since 1.1065.0000
 */
class Diagnostic_WpSyncDbCompatibility extends Diagnostic_Base {

	protected static $slug = 'wp-sync-db-compatibility';
	protected static $title = 'Wp Sync Db Compatibility';
	protected static $description = 'Wp Sync Db Compatibility issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for WP Sync DB
		$has_sync_db = class_exists( 'WPSDB_Plugin' ) || defined( 'WPSDB_VERSION' );
		if ( ! $has_sync_db ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: WordPress version compatibility
		$wp_version = get_bloginfo( 'version' );
		$min_version = get_option( 'wpsdb_min_wp_version', '5.0' );
		
		if ( version_compare( $wp_version, $min_version, '<' ) ) {
			$issues[] = sprintf(
				/* translators: 1: current version, 2: minimum version */
				__( 'WordPress %1$s may not be compatible (requires %2$s+)', 'wpshadow' ),
				$wp_version,
				$min_version
			);
		}
		
		// Check 2: Table prefix conflicts
		global $wpdb;
		$prefix = $wpdb->prefix;
		
		if ( 'wp_' === $prefix ) {
			$issues[] = __( 'Using default table prefix (sync confusion risk)', 'wpshadow' );
		}
		
		// Check 3: Multisite compatibility
		if ( is_multisite() ) {
			$multisite_support = get_option( 'wpsdb_multisite_tools', false );
			if ( ! $multisite_support ) {
				$issues[] = __( 'Multisite detected but multisite tools not enabled', 'wpshadow' );
			}
		}
		
		// Check 4: SSL verification
		$verify_ssl = get_option( 'wpsdb_verify_ssl', true );
		if ( ! $verify_ssl ) {
			$issues[] = __( 'SSL verification disabled (man-in-the-middle risk)', 'wpshadow' );
		}
		
		// Check 5: Plugin conflict detection
		$conflicting_plugins = array(
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
			'wp-super-cache/wp-cache.php' => 'WP Super Cache',
		);
		
		foreach ( $conflicting_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$issues[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s active (sync conflicts possible)', 'wpshadow' ),
					$name
				);
				break;
			}
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
				/* translators: %s: list of compatibility issues */
				__( 'WP Sync DB has %d compatibility issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-sync-db-compatibility',
		);
	}
}
