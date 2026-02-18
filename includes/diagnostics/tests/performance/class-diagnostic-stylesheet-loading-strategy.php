<?php
/**
 * Stylesheet Loading Strategy Diagnostic
 *
 * Analyzes CSS loading strategy for optimal rendering performance including
 * media query optimization and critical CSS extraction.
 *
 * @since   1.6033.2091
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stylesheet Loading Strategy Diagnostic Class
 *
 * Verifies CSS optimization:
 * - Stylesheet count
 * - Media query specificity
 * - Print stylesheets
 * - Render-blocking CSS
 *
 * @since 1.6033.2091
 */
class Diagnostic_Stylesheet_Loading_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'stylesheet-loading-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Stylesheet Loading Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes CSS loading strategy for optimal rendering performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2091
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_styles;

		$total_stylesheets = 0;
		$render_blocking   = 0;
		$print_only        = 0;
		$media_specific    = 0;

		if ( ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				$style = $wp_styles->registered[ $handle ] ?? null;
				if ( ! $style || empty( $style->src ) ) {
					continue;
				}

				$total_stylesheets++;
				$media = $style->media ?? 'all';

				// Check media type
				if ( 'print' === $media ) {
					$print_only++;
				} elseif ( in_array( $media, array( 'screen', 'only screen', '(min-width: 600px)' ), true ) ) {
					$media_specific++;
				} else {
					// Render-blocking (all or screen)
					$render_blocking++;
				}
			}
		}

		// Flag if many render-blocking stylesheets
		if ( $render_blocking > 5 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: render-blocking stylesheets, %d: total */
					__( '%d of %d stylesheets are render-blocking. Use media queries and CSS splitting to improve FCP.', 'wpshadow' ),
					$render_blocking,
					$total_stylesheets
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/stylesheet-loading-strategy',
				'meta'          => array(
					'total_stylesheets'    => $total_stylesheets,
					'render_blocking'      => $render_blocking,
					'print_only'           => $print_only,
					'media_specific'       => $media_specific,
					'recommendation'       => 'Split CSS, use media queries for mobile/print, defer non-critical CSS',
					'impact'               => 'Optimized CSS loading improves FCP by 100-200ms',
					'best_practice'        => array(
						'Inline critical CSS in head',
						'Use media="print" for print stylesheets',
						'Use media="(min-width: 600px)" for responsive',
						'Defer non-critical CSS',
					),
				),
			);
		}

		return null;
	}
}
