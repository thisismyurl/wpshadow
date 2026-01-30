<?php
/**
 * Multisite Menu Sync Diagnostic
 *
 * Multisite Menu Sync misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.966.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Menu Sync Diagnostic Class
 *
 * @since 1.966.0000
 */
class Diagnostic_MultisiteMenuSync extends Diagnostic_Base {

	protected static $slug = 'multisite-menu-sync';
	protected static $title = 'Multisite Menu Sync';
	protected static $description = 'Multisite Menu Sync misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Menu sync enabled
		$sync_enabled = get_site_option( 'multisite_menu_sync_enabled', 'no' );
		if ( 'no' === $sync_enabled ) {
			return null; // Not using menu sync
		}
		
		// Check 2: Menu replication
		$replicate_menus = get_site_option( 'multisite_replicate_menus', 'no' );
		if ( 'no' === $replicate_menus ) {
			$issues[] = __( 'Manual menu creation (inconsistent navigation)', 'wpshadow' );
		}
		
		// Check 3: Menu location mapping
		$location_mapping = get_site_option( 'multisite_menu_location_mapping', array() );
		if ( empty( $location_mapping ) ) {
			$issues[] = __( 'No location mapping (broken menus)', 'wpshadow' );
		}
		
		// Check 4: Custom menu items
		$sync_custom_items = get_site_option( 'multisite_sync_custom_menu_items', 'yes' );
		if ( 'no' === $sync_custom_items ) {
			$issues[] = __( 'Custom items not synced (incomplete menus)', 'wpshadow' );
		}
		
		// Check 5: Menu cache
		global $wpdb;
		$sites = get_sites( array( 'number' => 100 ) );
		$outdated_caches = 0;
		
		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );
			$last_sync = get_option( 'multisite_menu_last_sync', 0 );
			if ( $last_sync > 0 && ( time() - $last_sync ) > 86400 ) {
				++$outdated_caches;
			}
			restore_current_blog();
		}
		
		if ( $outdated_caches > 0 ) {
			$issues[] = sprintf( __( '%d sites with stale menu cache', 'wpshadow' ), $outdated_caches );
		}
		
		// Check 6: Orphaned menu items
		$orphaned_items = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.meta_value = p.ID
			WHERE pm.meta_key = '_menu_item_object_id' AND p.ID IS NULL"
		);
		
		if ( $orphaned_items > 10 ) {
			$issues[] = sprintf( __( '%d orphaned menu items', 'wpshadow' ), $orphaned_items );
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
				/* translators: %s: list of menu sync issues */
				__( 'Multisite menu sync has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-menu-sync',
		);
	}
}
