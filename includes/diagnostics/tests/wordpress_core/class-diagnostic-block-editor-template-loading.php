<?php
/**
 * Block Editor Template Loading Diagnostic
 *
 * Tests block editor template loading speed and availability.
 *
 * @since   1.2601.2112
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Block_Editor_Template_Loading
 *
 * Measures block editor template loading performance.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Block_Editor_Template_Loading extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2112
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		// Can only check if we're in edit context.
		if ( ! function_exists( 'get_block_templates' ) ) {
			return null; // Block templates not supported.
		}

		// Measure template loading time.
		$start = microtime( true );

		// Get available block templates (this loads from directory).
		$templates = get_block_templates();

		$load_time = microtime( true ) - $start;

		// Alert if template loading is slow (>500ms).
		if ( $load_time > 0.5 ) {
			return array(
				'id'           => 'block-editor-template-loading',
				'title'        => __( 'Slow Block Editor Template Loading', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: milliseconds */
					__( 'Block editor templates took %dms to load. This slows down the editor when creating new posts. Consider caching templates or optimizing theme file structure.', 'wpshadow' ),
					round( $load_time * 1000 )
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/block_editor_template_loading',
				'meta'         => array(
					'load_time_ms' => round( $load_time * 1000, 2 ),
					'threshold_ms' => 500,
					'template_count' => count( $templates ),
				),
			);
		}

		return null;
	}
}
