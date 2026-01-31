<?php
/**
 * Gutenberg Widget Blocks Migration Diagnostic
 *
 * Gutenberg Widget Blocks Migration issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1242.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg Widget Blocks Migration Diagnostic Class
 *
 * @since 1.1242.0000
 */
class Diagnostic_GutenbergWidgetBlocksMigration extends Diagnostic_Base {

	protected static $slug = 'gutenberg-widget-blocks-migration';
	protected static $title = 'Gutenberg Widget Blocks Migration';
	protected static $description = 'Gutenberg Widget Blocks Migration issue detected';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Widget blocks enabled
		$blocks_enabled = get_option( 'gutenberg_widget_blocks', 0 );
		if ( ! $blocks_enabled ) {
			$issues[] = 'Gutenberg widget blocks not enabled';
		}

		// Check 2: Legacy widget support enabled
		$legacy_widgets = get_option( 'gutenberg_legacy_widget_support', 0 );
		if ( ! $legacy_widgets ) {
			$issues[] = 'Legacy widget support not enabled';
		}

		// Check 3: Widget migration progress
		$migration_progress = absint( get_option( 'gutenberg_widget_migration_progress', 0 ) );
		if ( $migration_progress < 100 ) {
			$issues[] = 'Widget migration not complete (' . $migration_progress . '%)';
		}

		// Check 4: Widget backup created
		$backup_created = get_option( 'gutenberg_widget_backup_created', 0 );
		if ( ! $backup_created ) {
			$issues[] = 'Widget backup not created';
		}

		// Check 5: Block compatibility mode
		$compat_mode = get_option( 'gutenberg_widget_compat_mode', 0 );
		if ( ! $compat_mode ) {
			$issues[] = 'Widget compatibility mode not enabled';
		}

		// Check 6: Custom widget blocks registered
		$custom_blocks = get_option( 'gutenberg_custom_widget_blocks', '' );
		if ( empty( $custom_blocks ) ) {
			$issues[] = 'Custom widget blocks not registered';
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
					'Found %d Gutenberg widget migration issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/gutenberg-widget-blocks-migration',
			);
		}

		return null;
	}
}
