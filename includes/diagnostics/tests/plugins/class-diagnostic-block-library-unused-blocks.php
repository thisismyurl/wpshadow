<?php
/**
 * Block Library Unused Blocks Diagnostic
 *
 * Block Library Unused Blocks issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1244.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Library Unused Blocks Diagnostic Class
 *
 * @since 1.1244.0000
 */
class Diagnostic_BlockLibraryUnusedBlocks extends Diagnostic_Base {

	protected static $slug = 'block-library-unused-blocks';
	protected static $title = 'Block Library Unused Blocks';
	protected static $description = 'Block Library Unused Blocks issue detected';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Verify on-demand block assets
		if ( function_exists( 'wp_should_load_block_assets_on_demand' ) ) {
			if ( ! wp_should_load_block_assets_on_demand() ) {
				$issues[] = 'Block assets are not loaded on-demand';
			}
		}

		// Check 2: Check for unused core block assets
		$load_separate = get_option( 'should_load_separate_core_block_assets', false );
		if ( ! $load_separate ) {
			$issues[] = 'Separate core block assets not enabled';
		}

		// Check 3: Verify allowed block types restrictions
		$allowed_blocks = get_option( 'allowed_block_types', array() );
		if ( empty( $allowed_blocks ) ) {
			$issues[] = 'Allowed block types not restricted';
		}

		// Check 4: Check for block library style enqueue
		$block_styles = get_option( 'block_library_styles', 1 );
		if ( $block_styles ) {
			$issues[] = 'Block library styles loaded globally';
		}

		// Check 5: Verify editor assets in frontend
		$editor_assets = get_option( 'block_editor_assets_frontend', 0 );
		if ( $editor_assets ) {
			$issues[] = 'Editor assets loaded on the frontend';
		}

		// Check 6: Check for global block styles
		$global_styles = get_option( 'wp_global_styles_enabled', 1 );
		if ( $global_styles ) {
			$issues[] = 'Global block styles enabled for all pages';
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
					'Found %d block library optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/block-library-unused-blocks',
			);
		}

		return null;
	}
}
