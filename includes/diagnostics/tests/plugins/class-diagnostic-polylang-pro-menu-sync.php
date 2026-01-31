<?php
/**
 * Polylang Pro Menu Sync Diagnostic
 *
 * Polylang Pro Menu Sync misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1149.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Polylang Pro Menu Sync Diagnostic Class
 *
 * @since 1.1149.0000
 */
class Diagnostic_PolylangProMenuSync extends Diagnostic_Base {

	protected static $slug = 'polylang-pro-menu-sync';
	protected static $title = 'Polylang Pro Menu Sync';
	protected static $description = 'Polylang Pro Menu Sync misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'POLYLANG_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Menu sync enabled
		$menu_sync = get_option( 'polylang_menu_sync_enabled', 0 );
		if ( ! $menu_sync ) {
			$issues[] = 'Menu synchronization not enabled';
		}

		// Check 2: Menu translation enabled
		$menu_translate = get_option( 'polylang_menu_translation_enabled', 0 );
		if ( ! $menu_translate ) {
			$issues[] = 'Menu translation not enabled';
		}

		// Check 3: Sync schedule configured
		$sync_schedule = get_option( 'polylang_menu_sync_schedule', '' );
		if ( empty( $sync_schedule ) ) {
			$issues[] = 'Menu sync schedule not configured';
		}

		// Check 4: Conflict resolution
		$conflict_res = get_option( 'polylang_menu_conflict_resolution', '' );
		if ( empty( $conflict_res ) ) {
			$issues[] = 'Menu conflict resolution not configured';
		}

		// Check 5: Item linking
		$item_linking = get_option( 'polylang_menu_item_linking', 0 );
		if ( ! $item_linking ) {
			$issues[] = 'Menu item linking not enabled';
		}

		// Check 6: Fallback configuration
		$fallback = get_option( 'polylang_menu_fallback_config', '' );
		if ( empty( $fallback ) ) {
			$issues[] = 'Menu fallback configuration not set';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d menu sync issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/polylang-pro-menu-sync',
			);
		}

		return null;
	}
}
