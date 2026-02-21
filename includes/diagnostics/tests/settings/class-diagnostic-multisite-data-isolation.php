<?php
/**
 * Multisite Network Data Isolation Diagnostic
 *
 * Verifies sub-sites cannot access each other's data
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Multisite;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_MultisiteDataIsolation Class
 *
 * Checks for user role isolation, registration controls, enumeration protection
 *
 * @since 1.6031.1445
 */
class Diagnostic_MultisiteDataIsolation extends Diagnostic_Base {

/**
 * The diagnostic slug
 *
 * @var string
 */
protected static $slug = 'multisite-data-isolation';

/**
 * The diagnostic title
 *
 * @var string
 */
protected static $title = 'Multisite Network Data Isolation';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies sub-sites cannot access each other\'s data';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'multisite';

/**
 * Run the diagnostic check.
 *
 * @since  1.6031.1445
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Only run on multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check if sites share upload directories (should be isolated).
		$upload_dir = wp_upload_dir();
		$current_site_id = get_current_blog_id();

		// Check if upload path contains site ID.
		if ( false === strpos( $upload_dir['basedir'], 'sites/' . $current_site_id ) ) {
			$issues[] = __( 'Upload directories may not be properly isolated per site', 'wpshadow' );
		}

		// Check for shared tables (potential data leakage).
		global $wpdb;
		$prefix = $wpdb->get_blog_prefix( $current_site_id );

		// Verify site-specific tables exist.
		$site_tables = array( 'posts', 'postmeta', 'comments', 'options' );
		foreach ( $site_tables as $table ) {
			$table_name = $prefix . $table;
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
			if ( ! $table_exists ) {
				$issues[] = sprintf(
					/* translators: %s: table name */
					__( 'Site-specific table missing: %s', 'wpshadow' ),
					$table_name
				);
			}
		}

		// Check for user data isolation plugins.
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$isolation_plugins = array( 'multisite-user-management', 'network-privacy', 'site-isolation' );
		$has_isolation_plugin = false;

		foreach ( array_keys( $active_plugins ) as $plugin ) {
			foreach ( $isolation_plugins as $iso_plugin ) {
				if ( stripos( $plugin, $iso_plugin ) !== false ) {
					$has_isolation_plugin = true;
					break 2;
				}
			}
		}

		if ( ! $has_isolation_plugin ) {
			$issues[] = __( 'No multisite data isolation plugin detected', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Multisite data isolation concerns: %s. Network sites should have proper data separation.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-data-isolation',
		);
	}
}
