<?php
/**
 * Dynamic Style Injection Diagnostic
 *
 * Detects dynamic CSS injection and inline style performance issues.
 *
 * @since   1.26033.2120
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dynamic Style Injection Diagnostic
 *
 * Analyzes dynamic CSS injection patterns and performance impact.
 *
 * @since 1.26033.2120
 */
class Diagnostic_Dynamic_Style_Injection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dynamic-style-injection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Dynamic Style Injection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects dynamic CSS injection and inline style performance issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2120
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_styles;

		if ( ! isset( $wp_styles->registered ) ) {
			return null;
		}

		// Count inline styles
		$inline_style_count = 0;
		$inline_style_size  = 0;
		$handles_with_inline = array();

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( ! $wp_styles->is_enqueued( $handle ) ) {
				continue;
			}

			// Check for inline styles
			if ( isset( $style->extra['after'] ) ) {
				$inline_code = is_array( $style->extra['after'] ) ? implode( '', $style->extra['after'] ) : $style->extra['after'];
				if ( ! empty( $inline_code ) ) {
					$inline_style_count++;
					$inline_style_size += strlen( $inline_code );
					$handles_with_inline[] = $handle;
				}
			}
		}

		// Convert size to KB
		$inline_style_size_kb = round( $inline_style_size / 1024, 2 );

		// Check for excessive inline styles
		if ( $inline_style_count > 5 && $inline_style_size_kb > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of inline styles, 2: size in KB */
					__( '%1$d inline style blocks detected (%2$s KB). Consolidate into external stylesheets for better caching.', 'wpshadow' ),
					$inline_style_count,
					$inline_style_size_kb
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dynamic-style-injection',
				'meta'         => array(
					'inline_style_count'   => $inline_style_count,
					'inline_style_size_kb' => $inline_style_size_kb,
					'handles_with_inline'  => $handles_with_inline,
					'recommendation'       => 'Move inline styles to external CSS files',
					'impact_estimate'      => 'Enable browser caching, reduce HTML size by ' . $inline_style_size_kb . ' KB',
					'caching_benefit'      => 'Inline styles cannot be cached, external CSS can',
				),
			);
		}

		// Check for moderate inline usage
		if ( $inline_style_count > 3 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of inline styles */
					__( '%d inline style blocks detected. Consider consolidation for better performance.', 'wpshadow' ),
					$inline_style_count
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dynamic-style-injection',
				'meta'         => array(
					'inline_style_count'  => $inline_style_count,
					'inline_style_size_kb' => $inline_style_size_kb,
					'recommendation'      => 'Limit inline styles to critical CSS only',
					'best_practice'       => 'Use inline styles only for above-the-fold critical CSS',
				),
			);
		}

		return null;
	}
}
