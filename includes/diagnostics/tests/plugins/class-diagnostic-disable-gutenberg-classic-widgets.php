<?php
/**
 * Disable Gutenberg Classic Widgets Diagnostic
 *
 * Disable Gutenberg Classic Widgets issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1437.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable Gutenberg Classic Widgets Diagnostic Class
 *
 * @since 1.1437.0000
 */
class Diagnostic_DisableGutenbergClassicWidgets extends Diagnostic_Base {

	protected static $slug = 'disable-gutenberg-classic-widgets';
	protected static $title = 'Disable Gutenberg Classic Widgets';
	protected static $description = 'Disable Gutenberg Classic Widgets issue found';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();
		
		// Check 1: Classic widgets enabled
		$classic_widgets = get_option( 'classic_widgets_enabled', 0 );
		if ( ! $classic_widgets ) {
			$issues[] = 'Classic widgets support not enabled';
		}
		
		// Check 2: Widget blocks migration
		$migration = get_option( 'gutenberg_widget_migration_complete', 0 );
		if ( ! $migration ) {
			$issues[] = 'Widget block migration not complete';
		}
		
		// Check 3: Legacy widget support
		$legacy = get_option( 'gutenberg_legacy_widget_support', 0 );
		if ( ! $legacy ) {
			$issues[] = 'Legacy widget support not enabled';
		}
		
		// Check 4: Sidebar management
		$sidebars = get_option( 'gutenberg_sidebar_management', 0 );
		if ( ! $sidebars ) {
			$issues[] = 'Sidebar management not configured';
		}
		
		// Check 5: Widget compatibility mode
		$compat_mode = get_option( 'gutenberg_widget_compat_mode', 0 );
		if ( ! $compat_mode ) {
			$issues[] = 'Widget compatibility mode not enabled';
		}
		
		// Check 6: Block widget migration
		$block_widgets = get_option( 'gutenberg_block_widgets_available', 0 );
		if ( ! $block_widgets ) {
			$issues[] = 'Block-based widgets not available';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d classic widget issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/disable-gutenberg-classic-widgets',
			);
		}
		
		return null;
	}
}
