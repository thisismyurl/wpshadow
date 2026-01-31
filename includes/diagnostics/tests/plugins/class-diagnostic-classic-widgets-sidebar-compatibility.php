<?php
/**
 * Classic Widgets Sidebar Compatibility Diagnostic
 *
 * Classic Widgets Sidebar Compatibility issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1440.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classic Widgets Sidebar Compatibility Diagnostic Class
 *
 * @since 1.1440.0000
 */
class Diagnostic_ClassicWidgetsSidebarCompatibility extends Diagnostic_Base {

	protected static $slug = 'classic-widgets-sidebar-compatibility';
	protected static $title = 'Classic Widgets Sidebar Compatibility';
	protected static $description = 'Classic Widgets Sidebar Compatibility issue found';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Legacy widget support enabled
		$legacy_widgets = get_option( 'wp_legacy_widget_support', 0 );
		if ( ! $legacy_widgets ) {
			$issues[] = 'Legacy widget support not enabled';
		}

		// Check 2: Widget migration status
		$widget_migration = get_option( 'wp_widget_migration_status', '' );
		if ( 'complete' !== $widget_migration ) {
			$issues[] = 'Widget migration not complete';
		}

		// Check 3: Sidebar compatibility mode
		$compat_mode = get_option( 'wp_sidebar_compatibility_mode', 0 );
		if ( ! $compat_mode ) {
			$issues[] = 'Sidebar compatibility mode not enabled';
		}

		// Check 4: Widget block support
		$block_support = get_option( 'wp_widget_block_support', 0 );
		if ( ! $block_support ) {
			$issues[] = 'Widget block support not enabled';
		}

		// Check 5: Fallback for unsupported widgets
		$fallback = get_option( 'wp_widget_unsupported_fallback', '' );
		if ( empty( $fallback ) ) {
			$issues[] = 'Unsupported widget fallback not configured';
		}

		// Check 6: Widget deprecation warnings
		$warnings = get_option( 'wp_widget_deprecation_warnings', 0 );
		if ( ! $warnings ) {
			$issues[] = 'Widget deprecation warnings not enabled';
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
					'Found %d widget compatibility issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/classic-widgets-sidebar-compatibility',
			);
		}

		return null;
	}
}
