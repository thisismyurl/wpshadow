<?php
/**
 * Multisite Centralized Updates Diagnostic
 *
 * Multisite Centralized Updates misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.964.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Centralized Updates Diagnostic Class
 *
 * @since 1.964.0000
 */
class Diagnostic_MultisiteCentralizedUpdates extends Diagnostic_Base {

	protected static $slug = 'multisite-centralized-updates';
	protected static $title = 'Multisite Centralized Updates';
	protected static $description = 'Multisite Centralized Updates misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check 1: Network update permissions
		$allow_network_updates = get_site_option( 'allow_network_updates', 'no' );
		if ( 'no' === $allow_network_updates ) {
			$issues[] = __( 'Network updates disabled (fragmented versions)', 'wpshadow' );
		}

		// Check 2: Site-level plugin updates
		$site_updates = get_site_option( 'allow_site_plugin_updates', 'yes' );
		if ( 'yes' === $site_updates ) {
			$issues[] = __( 'Sites can update plugins (version conflicts)', 'wpshadow' );
		}

		// Check 3: Theme updates
		$theme_updates = get_site_option( 'allow_site_theme_updates', 'yes' );
		if ( 'yes' === $theme_updates ) {
			$issues[] = __( 'Sites can update themes (inconsistent styling)', 'wpshadow' );
		}

		// Check 4: Auto-updates
		$auto_updates = get_site_option( 'auto_update_plugins', array() );
		if ( empty( $auto_updates ) ) {
			$issues[] = __( 'No auto-updates configured (security lag)', 'wpshadow' );
		}

		// Check 5: Update notifications
		$notify_admins = get_site_option( 'notify_site_admins_of_updates', 'no' );
		if ( 'no' === $notify_admins ) {
			$issues[] = __( 'Site admins not notified (outdated software)', 'wpshadow' );
		}

		// Check 6: Version tracking
		global $wpdb;
		$version_variance = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_value) FROM {$wpdb->sitemeta}
			 WHERE meta_key = 'initial_db_version'"
		);

		if ( $version_variance > 3 ) {
			$issues[] = sprintf( __( '%d different WordPress versions (fragmentation)', 'wpshadow' ), $version_variance );
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
				__( 'Multisite updates have %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-centralized-updates',
		);
	}
}
