<?php
/**
 * Classic Widgets Block Widgets Migration Diagnostic
 *
 * Classic Widgets Block Widgets Migration issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1439.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classic Widgets Block Widgets Migration Diagnostic Class
 *
 * @since 1.1439.0000
 */
class Diagnostic_ClassicWidgetsBlockWidgetsMigration extends Diagnostic_Base {

	protected static $slug = 'classic-widgets-block-widgets-migration';
	protected static $title = 'Classic Widgets Block Widgets Migration';
	protected static $description = 'Classic Widgets Block Widgets Migration issue found';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Migration check enabled
		$migration_check = get_option( 'gutenberg_widget_migration_check_enabled', 0 );
		if ( ! $migration_check ) {
			$issues[] = 'Widget migration check not enabled';
		}

		// Check 2: Classic widget compatibility
		$classic_compat = get_option( 'gutenberg_classic_widget_compatibility_enabled', 0 );
		if ( ! $classic_compat ) {
			$issues[] = 'Classic widget compatibility not enabled';
		}

		// Check 3: Block widget availability
		$block_widgets = get_option( 'gutenberg_block_widgets_available', 0 );
		if ( ! $block_widgets ) {
			$issues[] = 'Block-based widgets not available';
		}

		// Check 4: Migration progress tracking
		$progress = get_option( 'gutenberg_widget_migration_progress', '' );
		if ( empty( $progress ) ) {
			$issues[] = 'Migration progress not tracked';
		}

		// Check 5: Fallback support
		$fallback = get_option( 'gutenberg_widget_fallback_support', 0 );
		if ( ! $fallback ) {
			$issues[] = 'Widget fallback support not enabled';
		}

		// Check 6: Legacy widget bridge
		$bridge = get_option( 'gutenberg_legacy_widget_bridge_enabled', 0 );
		if ( ! $bridge ) {
			$issues[] = 'Legacy widget bridge not enabled';
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
					'Found %d widget migration issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/classic-widgets-block-widgets-migration',
			);
		}

		return null;
	}
}
