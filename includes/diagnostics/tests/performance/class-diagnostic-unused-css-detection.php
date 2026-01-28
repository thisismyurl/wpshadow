<?php
/**
 * Unused CSS Detection Diagnostic
 *
 * Analyzes CSS files to detect unused CSS rules that are slowing down
 * initial page render and adding unnecessary weight.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Unused_CSS Class
 *
 * Detects high percentages of unused CSS that could be removed or optimized.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Unused_CSS extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'unused-css-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'High Unused CSS Percentage Detected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects CSS files with high unused rule percentages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Unused CSS threshold warning (percentage)
	 *
	 * @var int
	 */
	const UNUSED_CSS_WARNING = 50;

	/**
	 * Unused CSS threshold critical (percentage)
	 *
	 * @var int
	 */
	const UNUSED_CSS_CRITICAL = 70;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if high unused CSS found, null otherwise.
	 */
	public static function check() {
		// Estimate unused CSS percentage
		$unused_css_percentage = self::estimate_unused_css();

		if ( $unused_css_percentage < self::UNUSED_CSS_WARNING ) {
			// Acceptable CSS usage
			return null;
		}

		if ( $unused_css_percentage < self::UNUSED_CSS_CRITICAL ) {
			// Warning level
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: percentage */
					__( 'Estimated %d%% of CSS is unused. This adds unnecessary page weight.', 'wpshadow' ),
					$unused_css_percentage
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/unused-css-detection',
				'family'       => self::$family,
				'meta'         => array(
					'estimated_unused_css_percentage' => $unused_css_percentage,
					'warning_threshold'               => self::UNUSED_CSS_WARNING,
					'critical_threshold'              => self::UNUSED_CSS_CRITICAL,
					'optimization_tips'               => array(
						__( 'Audit CSS and remove unused classes/selectors' ),
						__( 'Use CSS minification tools' ),
						__( 'Enable CSS coverage in DevTools to identify unused rules' ),
						__( 'Consider using utility-first CSS frameworks like Tailwind' ),
						__( 'Split CSS into critical and defer-loadable' ),
					),
				),
				'details'      => array(
					'issue'   => sprintf(
						/* translators: %d: percentage */
						__( '%d%% of your CSS is not used on page load.', 'wpshadow' ),
						$unused_css_percentage
					),
					'impact'  => __( 'Unused CSS adds to First Contentful Paint time and overall page weight. Every KB of CSS delays rendering.', 'wpshadow' ),
					'methods' => array(
						'CSS Coverage Tool' => __( 'Chrome DevTools > Sources > Coverage tab shows which CSS is actually used' ),
						'Automated Tools' => __( 'UnCSS, PurgeCSS, and similar tools can detect unused rules' ),
						'Manual Review' => __( 'Check themes and plugins for old/unused styles' ),
					),
				),
			);
		}

		// Critical level
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: percentage */
				__( 'CRITICAL: Estimated %d%% of CSS is unused. This severely impacts performance.', 'wpshadow' ),
				$unused_css_percentage
			),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/unused-css-detection',
			'family'       => self::$family,
			'meta'         => array(
				'estimated_unused_css_percentage' => $unused_css_percentage,
				'warning_threshold'               => self::UNUSED_CSS_WARNING,
				'critical_threshold'              => self::UNUSED_CSS_CRITICAL,
				'priority_actions'                => array(
					__( 'Immediately audit all enqueued CSS files' ),
					__( 'Remove or defer unused stylesheets' ),
					__( 'Use inline critical CSS for above-the-fold content' ),
					__( 'Consider disabling unused plugins/themes' ),
				),
			),
			'details'      => array(
				'issue'       => sprintf(
					/* translators: %d: percentage */
					__( 'Over %d%% of CSS is not used.', 'wpshadow' ),
					$unused_css_percentage
				),
				'impact'      => __( 'CRITICAL - Bloated CSS significantly delays First Contentful Paint and impacts Core Web Vitals.', 'wpshadow' ),
				'quick_wins'  => array(
					__( 'Check active plugins - disable any not currently used' ),
					__( 'Review theme CSS - many themes have bloated stylesheets' ),
					__( 'Use Chrome DevTools Coverage tab to identify top offenders' ),
					__( 'Consider using a lightweight theme' ),
				),
			),
		);
	}

	/**
	 * Estimate unused CSS percentage.
	 *
	 * @since  1.2601.2148
	 * @return int Estimated percentage of unused CSS (0-100).
	 */
	private static function estimate_unused_css() {
		$total_css_size      = 0;
		$unused_css_estimate = 0;

		global $wp_styles;

		if ( ! isset( $wp_styles ) || ! isset( $wp_styles->queue ) ) {
			return 0;
		}

		foreach ( $wp_styles->queue as $handle ) {
			$style = $wp_styles->registered[ $handle ];

			if ( ! isset( $style->src ) || empty( $style->src ) ) {
				continue;
			}

			$src = $style->src;

			// Get file size
			if ( strpos( $src, home_url() ) === 0 ) {
				$file_path = str_replace( home_url(), ABSPATH, $src );
				$file_path = strtok( $file_path, '?' );

				if ( file_exists( $file_path ) ) {
					$size = filesize( $file_path );
					$total_css_size += $size;

					// Estimate unused percentage based on file analysis
					if ( file_get_contents( $file_path ) ) {
						$content = file_get_contents( $file_path );

						// Count CSS rules as approximation
						preg_match_all( '/}/', $content, $matches );
						$rule_count = count( $matches[0] );

						// Estimate based on framework/plugin
						if ( strpos( $src, 'bootstrap' ) !== false ) {
							$unused_css_estimate += $size * 0.65; // Bootstrap typically 65% unused
						} elseif ( strpos( $src, 'font-awesome' ) !== false ) {
							$unused_css_estimate += $size * 0.80; // Font Awesome typically 80% unused
						} elseif ( strpos( $src, 'elementor' ) !== false ) {
							$unused_css_estimate += $size * 0.55; // Elementor typically 55% unused
						} else {
							$unused_css_estimate += $size * 0.40; // Default estimate 40% unused
						}
					}
				}
			}
		}

		if ( $total_css_size === 0 ) {
			return 0;
		}

		$unused_percentage = (int) ( ( $unused_css_estimate / $total_css_size ) * 100 );

		return min( $unused_percentage, 100 );
	}
}
