<?php
/**
 * Content Missing Update Dates Diagnostic
 *
 * Detects missing update timestamps on content.
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
 * Content Missing Update Dates Diagnostic Class
 *
 * Hidden update dates reduce trust. Showing "Updated" increases
 * perceived credibility by ~23%.
 *
 * @since 1.6033.1700
 */
class Diagnostic_Content_Missing_Update_Dates extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-missing-update-dates';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Update Timestamps';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing or hidden update timestamps on posts and pages';

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

		// Check for update timestamps on content.
		$has_update_dates = apply_filters( 'wpshadow_content_shows_update_dates', false );
		if ( ! $has_update_dates ) {
			$issues[] = __( 'Update timestamps are hidden; add "Updated" date for credibility boost', 'wpshadow' );
		}

		// Check for last updated being displayed.
		$last_updated_visible = apply_filters( 'wpshadow_last_updated_visible', false );
		if ( ! $last_updated_visible ) {
			$issues[] = __( 'Readers cannot see when content was last updated', 'wpshadow' );
		}

		// Check for recent update activity.
		$recent_updates = apply_filters( 'wpshadow_has_recent_content_updates', false );
		if ( ! $recent_updates ) {
			$issues[] = __( 'No recent content updates detected; stale pages appear outdated', 'wpshadow' );
		}

		// Check for structured data update dates.
		$has_schema_updates = apply_filters( 'wpshadow_has_schema_update_dates', false );
		if ( ! $has_schema_updates ) {
			$issues[] = __( 'Add dateModified schema markup for better search result trust', 'wpshadow' );
		}

		// Check for template consistency.
		$template_consistency = apply_filters( 'wpshadow_update_dates_consistent_across_templates', false );
		if ( ! $template_consistency ) {
			$issues[] = __( 'Update timestamps are inconsistent across templates', 'wpshadow' );
		}

		// Check for editorial policy.
		$editorial_policy = apply_filters( 'wpshadow_has_content_update_policy', false );
		if ( ! $editorial_policy ) {
			$issues[] = __( 'Define a content update policy (quarterly, biannual, annual)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-missing-update-dates',
			);
		}

		return null;
	}
}
