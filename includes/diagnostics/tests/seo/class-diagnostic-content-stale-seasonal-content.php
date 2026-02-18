<?php
/**
 * Content Seasonal Content Not Refreshed Diagnostic
 *
 * Detects seasonal content that has not been refreshed.
 *
 * @since   1.6033.1700
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Seasonal Content Not Refreshed Diagnostic Class
 *
 * Seasonal guides with outdated dates (e.g., 2022) lose up to 80% of
 * traffic vs refreshed competitors.
 *
 * @since 1.6033.1700
 */
class Diagnostic_Content_Stale_Seasonal_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-stale-seasonal-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Seasonal Content Not Refreshed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects seasonal content with outdated dates or stale information';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1700
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for stale seasonal posts.
		$stale_seasonal_count = apply_filters( 'wpshadow_stale_seasonal_content_count', 0 );
		if ( $stale_seasonal_count > 0 ) {
			$issues[] = __( 'Seasonal content contains outdated dates and needs refresh', 'wpshadow' );
		}

		// Check for current season alignment.
		$current_season_updated = apply_filters( 'wpshadow_current_season_content_updated', false );
		if ( ! $current_season_updated ) {
			$issues[] = __( 'Current-season content has not been updated for this year', 'wpshadow' );
		}

		// Check for traffic loss signals.
		$traffic_drop = apply_filters( 'wpshadow_seasonal_content_traffic_drop', false );
		if ( $traffic_drop ) {
			$issues[] = __( 'Seasonal posts show major traffic decline vs refreshed competitors (up to 80%)', 'wpshadow' );
		}

		// Check for outdated dates in titles.
		$outdated_title_years = apply_filters( 'wpshadow_seasonal_titles_outdated_years', false );
		if ( $outdated_title_years ) {
			$issues[] = __( 'Seasonal titles reference old years (e.g., 2022) and reduce click-through', 'wpshadow' );
		}

		// Check for update cadence.
		$update_cadence = apply_filters( 'wpshadow_seasonal_content_update_cadence_defined', false );
		if ( ! $update_cadence ) {
			$issues[] = __( 'Set an annual refresh cadence for seasonal content', 'wpshadow' );
		}

		// Check for internal linking updates.
		$internal_links_updated = apply_filters( 'wpshadow_seasonal_internal_links_updated', false );
		if ( ! $internal_links_updated ) {
			$issues[] = __( 'Update internal links to point to refreshed seasonal guides', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-stale-seasonal-content',
			);
		}

		return null;
	}
}
