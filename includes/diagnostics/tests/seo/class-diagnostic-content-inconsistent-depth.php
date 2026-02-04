<?php
/**
 * Content Inconsistent Depth Diagnostic
 *
 * Identifies high variance in content depth without clear strategy.
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
 * Content Inconsistent Depth Diagnostic Class
 *
 * Identifies high variance in content depth (some 200 words, some 3000 words) without
 * clear strategy, which confuses reader expectations and dilutes site positioning.
 *
 * @since 1.6033.1645
 */
class Diagnostic_Content_Inconsistent_Depth extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-inconsistent-depth';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inconsistent Content Depth';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identify high variance in content depth without clear strategic purpose';

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

		// Check for depth variance
		$depth_variance = apply_filters( 'wpshadow_content_depth_variance', 0 );
		if ( $depth_variance > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: variance percentage */
				__( 'Content depth variance is %d%%; inconsistent depth confuses readers about site positioning', 'wpshadow' ),
				$depth_variance
			);
		}

		// Check for extreme word count ranges
		$extreme_range = apply_filters( 'wpshadow_has_extreme_wordcount_range', false );
		if ( $extreme_range ) {
			$issues[] = __( 'Posts range from very short to very long with no pattern; define content tiers instead', 'wpshadow' );
		}

		// Check for defined content tiers
		$content_tiers = apply_filters( 'wpshadow_has_defined_content_tiers', false );
		if ( ! $content_tiers ) {
			$issues[] = __( 'Define content tiers: Quick (400-600), Standard (1,000-1,500), Deep (2,000-3,000), Pillar (3,500+)', 'wpshadow' );
		}

		// Check for positioning clarity
		$positioning_clear = apply_filters( 'wpshadow_site_positioning_clear', false );
		if ( ! $positioning_clear ) {
			$issues[] = __( 'Inconsistent depth signals confused brand positioning to readers and search engines', 'wpshadow' );
		}

		// Check for type-to-depth matching
		$type_depth_match = apply_filters( 'wpshadow_content_type_depth_aligned', false );
		if ( ! $type_depth_match ) {
			$issues[] = __( 'Match depth to type: Tutorials 1,500-2,500, Reviews 1,200-1,800, Quick tips 400-600', 'wpshadow' );
		}

		// Check for reader expectation consistency
		$reader_expectations = apply_filters( 'wpshadow_reader_expectations_consistent', false );
		if ( ! $reader_expectations ) {
			$issues[] = __( 'Readers won\'t know what to expect from site; clear tiers help engagement', 'wpshadow' );
		}

		// Check for SEO focus clarity
		$seo_focus = apply_filters( 'wpshadow_seo_focus_clear_per_tier', false );
		if ( ! $seo_focus ) {
			$issues[] = __( 'Each tier targets different keywords; define strategy per tier for better SEO', 'wpshadow' );
		}

		// Check for visual tier indicators
		$visual_indicators = apply_filters( 'wpshadow_has_visual_content_tier_indicators', false );
		if ( ! $visual_indicators ) {
			$issues[] = __( 'Add badges (\"Quick Read\", \"In-Depth Guide\") to help readers choose content depth', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-inconsistent-depth',
			);
		}

		return null;
	}
}
