<?php
/**
 * Content Weekend Publishing Gaps Diagnostic
 *
 * Identifies missed opportunities when traffic peaks on weekends.
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
 * Content Weekend Publishing Gaps Diagnostic Class
 *
 * Identifies missed opportunities when analytics show high weekend traffic
 * but no content is published during those peak periods.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Weekend_Publishing_Gaps extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-weekend-publishing-gaps';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Weekend Publishing Gaps';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identify missed opportunities when weekend traffic is high but publishing is absent';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for weekend traffic pattern
		$weekend_traffic = apply_filters( 'wpshadow_has_significant_weekend_traffic', false );
		if ( ! $weekend_traffic ) {
			return null; // No weekend traffic pattern, so gap isn't relevant
		}

		// Check if publishing on weekends
		$weekend_publishing = apply_filters( 'wpshadow_publishes_weekend_content', false );
		if ( ! $weekend_publishing ) {
			$issues[] = __( 'High weekend traffic detected but no content published on weekends; missed opportunity', 'wpshadow' );
		}

		// Check traffic vs publishing alignment
		$mismatch = apply_filters( 'wpshadow_traffic_publishing_day_mismatch', false );
		if ( $mismatch ) {
			$issues[] = __( 'Analytics show peak traffic on weekends; schedule content to match audience patterns', 'wpshadow' );
		}

		// Check for B2C content type
		$b2c_content = apply_filters( 'wpshadow_content_is_bc_oriented', false );
		if ( $b2c_content ) {
			$issues[] = __( 'B2C and hobbyist audiences browse heavily on weekends; capitalize with scheduled posts', 'wpshadow' );
		}

		// Check for DIY/tutorial content
		$diy_tutorials = apply_filters( 'wpshadow_has_diy_tutorial_content', false );
		if ( $diy_tutorials ) {
			$issues[] = __( 'DIY and tutorial content performs exceptionally well on weekends', 'wpshadow' );
		}

		// Check for weekend content performance data
		$weekend_performance = apply_filters( 'wpshadow_weekend_content_would_perform_well', false );
		if ( $weekend_performance ) {
			$issues[] = __( 'Weekend content could capture high-intent weekend searchers', 'wpshadow' );
		}

		// Check for scheduling capability
		$can_schedule = apply_filters( 'wpshadow_can_schedule_weekend_content', false );
		if ( $can_schedule ) {
			$issues[] = __( 'Use WordPress scheduled posts to automatically publish on optimal weekend times', 'wpshadow' );
		}

		// Check last 60 days publishing
		$recent_weekend = apply_filters( 'wpshadow_published_weekend_last_60_days', false );
		if ( ! $recent_weekend ) {
			$issues[] = __( 'No weekend posts in last 60 days; consider testing weekend publishing schedule', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-weekend-publishing-gaps?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
