<?php
/**
 * Classic Widgets Legacy Support Diagnostic
 *
 * Classic Widgets Legacy Support issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1441.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classic Widgets Legacy Support Diagnostic Class
 *
 * @since 1.1441.0000
 */
class Diagnostic_ClassicWidgetsLegacySupport extends Diagnostic_Base {

	protected static $slug = 'classic-widgets-legacy-support';
	protected static $title = 'Classic Widgets Legacy Support';
	protected static $description = 'Classic Widgets Legacy Support issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check if block editor is active (WordPress 5.8+)
		if ( ! function_exists( 'wp_use_widgets_block_editor' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify classic widgets plugin is active if needed
		$using_block_widgets = wp_use_widgets_block_editor();
		if ( $using_block_widgets && ! class_exists( 'Classic_Widgets_Plugin' ) ) {
			$active_widgets = wp_get_sidebars_widgets();
			if ( ! empty( $active_widgets ) ) {
				$issues[] = 'Block widget editor active but classic widgets plugin not installed';
			}
		}
		
		// Check 2: Check for widget compatibility issues
		if ( $using_block_widgets ) {
			$registered_widgets = $GLOBALS['wp_registered_widgets'];
			foreach ( $registered_widgets as $widget ) {
				if ( strpos( $widget['callback'][0]->id_base ?? '', 'text' ) === false ) {
					$issues[] = 'Custom widgets may not be compatible with block editor';
					break;
				}
			}
		}
		
		// Check 3: Verify theme compatibility
		$theme = wp_get_theme();
		if ( $using_block_widgets && ! current_theme_supports( 'widgets-block-editor' ) ) {
			$issues[] = 'Theme does not declare block widget editor support';
		}
		
		// Check 4: Check for widget migration
		$migrated = get_option( 'classic_widgets_migration_complete', false );
		if ( ! $using_block_widgets && ! $migrated ) {
			$issues[] = 'Widget migration to classic format not completed';
		}
		
		// Check 5: Verify sidebar registration
		$sidebars = wp_get_sidebars_widgets();
		if ( empty( $sidebars ) ) {
			$issues[] = 'No widget areas (sidebars) registered';
		}
		
		// Check 6: Check for legacy widget blocks
		if ( $using_block_widgets ) {
			$has_legacy_blocks = get_option( 'widget_block', array() );
			if ( ! empty( $has_legacy_blocks ) ) {
				$issues[] = 'Legacy widget blocks detected (consider updating to native blocks)';
			}
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
					'Found %d classic widgets legacy support issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/classic-widgets-legacy-support',
			);
		}
		
		return null;
	}
}
