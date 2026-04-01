<?php
/**
 * CSS Specificity Analysis Diagnostic
 *
 * Analyzes CSS specificity depth and complexity.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSS Specificity Analysis Diagnostic
 *
 * Evaluates CSS specificity patterns and identifies maintainability issues.
 *
 * @since 0.6093.1200
 */
class Diagnostic_CSS_Specificity_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'css-specificity-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CSS Specificity Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes CSS specificity depth and complexity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_styles;

		if ( ! isset( $wp_styles->registered ) ) {
			return null;
		}

		// Sample local CSS files for specificity analysis
		$high_specificity_count = 0;
		$important_usage_count  = 0;
		$analyzed_files         = 0;

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( ! $wp_styles->query( $handle ) ) {
				continue;
			}

			// Only analyze local stylesheets
			if ( ! isset( $style->src ) || ! is_string( $style->src ) || strpos( $style->src, home_url() ) === false ) {
				continue;
			}

			$file_path = str_replace( home_url(), ABSPATH, $style->src );
			$file_path = str_replace( array( 'http://', 'https://' ), '', $file_path );

			if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
				continue;
			}

			// Read file content (limit to first 50KB for performance)
			$content = file_get_contents( $file_path, false, null, 0, 51200 );
			if ( empty( $content ) ) {
				continue;
			}

			$analyzed_files++;

			// Check for high specificity patterns (simplified heuristic)
			// Pattern: multiple chained classes/IDs (e.g., #id .class .class .class)
			$high_specificity_matches = preg_match_all( '/[#\.][\w-]+\s+[#\.][\w-]+\s+[#\.][\w-]+\s+[#\.][\w-]+/i', $content );
			if ( $high_specificity_matches > 10 ) {
				$high_specificity_count += $high_specificity_matches;
			}

			// Check for !important usage
			$important_matches = preg_match_all( '/!important/i', $content );
			if ( $important_matches > 5 ) {
				$important_usage_count += $important_matches;
			}
		}

		// Generate findings based on analysis
		if ( $important_usage_count > 20 && $analyzed_files > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of !important declarations */
					__( '%d !important declarations detected. Excessive use indicates specificity issues and makes CSS harder to maintain.', 'wpshadow' ),
					$important_usage_count
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/css-specificity-analysis?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'important_count'   => $important_usage_count,
					'analyzed_files'    => $analyzed_files,
					'recommendation'    => 'Refactor CSS to reduce !important usage',
					'impact_estimate'   => 'Improves CSS maintainability and reduces conflicts',
					'best_practices'    => array(
						'Use BEM or similar naming methodology',
						'Avoid deep nesting (max 3-4 levels)',
						'Reserve !important for utilities only',
						'Prefer class selectors over IDs',
					),
				),
			);
		}

		if ( $high_specificity_count > 30 && $analyzed_files > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of high-specificity selectors */
					__( '%d high-specificity selectors detected. Deep selector nesting increases CSS complexity and render time.', 'wpshadow' ),
					$high_specificity_count
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/css-specificity-analysis?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'high_specificity_count' => $high_specificity_count,
					'analyzed_files'         => $analyzed_files,
					'recommendation'         => 'Flatten CSS structure, use simpler selectors',
					'impact_estimate'        => 'Faster selector matching, easier maintenance',
					'specificity_tips'       => array(
						'Limit nesting to 3 levels max',
						'Use single classes where possible',
						'Avoid chaining multiple classes',
						'Use CSS custom properties for themes',
					),
				),
			);
		}

		return null;
	}
}
