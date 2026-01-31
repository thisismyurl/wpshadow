<?php
/**
 * Disable Gutenberg Performance Impact Diagnostic
 *
 * Disable Gutenberg Performance Impact issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1438.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable Gutenberg Performance Impact Diagnostic Class
 *
 * @since 1.1438.0000
 */
class Diagnostic_DisableGutenbergPerformanceImpact extends Diagnostic_Base {

	protected static $slug = 'disable-gutenberg-performance-impact';
	protected static $title = 'Disable Gutenberg Performance Impact';
	protected static $description = 'Disable Gutenberg Performance Impact issue found';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		// Check 1: Gutenberg disabled
		$gutenberg_disabled = get_option( 'gutenberg_disabled', 0 );
		if ( ! $gutenberg_disabled ) {
			$issues[] = 'Gutenberg editor not fully disabled';
		}

		// Check 2: Gutenberg CSS removal
		$gutenberg_css_removed = get_option( 'gutenberg_css_removed', 0 );
		if ( ! $gutenberg_css_removed ) {
			$issues[] = 'Gutenberg CSS not removed from admin';
		}

		// Check 3: Block library disabled
		$blocks_disabled = get_option( 'gutenberg_blocks_library_disabled', 0 );
		if ( ! $blocks_disabled ) {
			$issues[] = 'Block library still loaded';
		}

		// Check 4: REST API block routes disabled
		$rest_routes = get_option( 'gutenberg_rest_block_routes_disabled', 0 );
		if ( ! $rest_routes ) {
			$issues[] = 'REST API block routes not disabled';
		}

		// Check 5: Classic editor theme support
		$classic_support = get_option( 'theme_classic_editor_support', 0 );
		if ( ! $classic_support ) {
			$issues[] = 'Classic editor theme support not enabled';
		}

		// Check 6: Performance optimization settings
		$perf_enabled = get_option( 'gutenberg_perf_settings_enabled', 0 );
		if ( ! $perf_enabled ) {
			$issues[] = 'Performance optimization settings not configured';
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
					'Found %d Gutenberg performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/disable-gutenberg-performance-impact',
			);
		}

		return null;
	}
}
