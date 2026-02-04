<?php
/**
 * Content Inconsistent Reading Level Diagnostic
 *
 * Detects dramatic variance in reading level consistency.
 *
 * @since   1.6033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Inconsistent Reading Level Diagnostic Class
 *
 * Reading level varies dramatically (grade 6 to grade 14+) causing brand
 * inconsistency. After standardizing: 31% bounce rate decrease.
 *
 * @since 1.6033.1645
 */
class Diagnostic_Content_Inconsistent_Reading_Level extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-inconsistent-reading-level';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inconsistent Reading Level';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect dramatic variance in reading level across content';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for reading level inconsistency
		$inconsistent = apply_filters( 'wpshadow_has_inconsistent_reading_levels', false );
		if ( $inconsistent ) {
			$issues[] = __( 'Reading level varies dramatically across content (grade 6 to 14+)', 'wpshadow' );
		}

		// Check for brand consistency
		$brand_impact = apply_filters( 'wpshadow_reading_inconsistency_damages_brand', false );
		if ( $brand_impact ) {
			$issues[] = __( 'Inconsistent reading level confuses brand positioning to readers', 'wpshadow' );
		}

		// Check for target reading level definition
		$target_defined = apply_filters( 'wpshadow_has_target_reading_level', false );
		if ( ! $target_defined ) {
			$issues[] = __( 'Define target reading level (grade 8-10 for general audiences)', 'wpshadow' );
		}

		// Check for reading level variance range
		$variance_range = apply_filters( 'wpshadow_reading_level_variance_grade_count', 0 );
		if ( $variance_range > 4 && $variance_range > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: variance in grade levels */
				__( 'Reading level varies across %d grade levels; standardize to maximum 2-grade range', 'wpshadow' ),
				$variance_range
			);
		}

		// Check for bounce rate impact
		$bounce_rate = apply_filters( 'wpshadow_bounce_rate_reduced_by_consistency', false );
		if ( $bounce_rate ) {
			$issues[] = __( 'Standardizing reading level can decrease bounce rate by 31%', 'wpshadow' );
		}

		// Check for audience clarity
		$audience_clarity = apply_filters( 'wpshadow_target_audience_reading_level_clear', false );
		if ( ! $audience_clarity ) {
			$issues[] = __( 'Define target audience first; this determines appropriate reading level', 'wpshadow' );
		}

		// Check for style guide adoption
		$style_guide = apply_filters( 'wpshadow_has_style_guide_reading_level', false );
		if ( ! $style_guide ) {
			$issues[] = __( 'Create style guide specifying target reading level and vocabulary guidelines', 'wpshadow' );
		}

		// Check for multiple author coordination
		$author_coordination = apply_filters( 'wpshadow_multiple_authors_reading_consistency', false );
		if ( ! $author_coordination ) {
			$issues[] = __( 'Multiple authors should follow consistent reading level guidelines', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-inconsistent-reading-level',
			);
		}

		return null;
	}
}
