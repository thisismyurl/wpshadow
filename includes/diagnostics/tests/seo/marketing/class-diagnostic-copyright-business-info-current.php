<?php
/**
 * Copyright and Business Information Diagnostic
 *
 * Checks whether copyright year and business contact details appear current.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Copyright and Business Information Diagnostic Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Copyright_Business_Info_Current extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'copyright-business-info-current';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Copyright and Business Information Outdated';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if copyright year and contact details look current';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'trust-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$html = Diagnostic_HTML_Helper::fetch_homepage_html_cached(
			'wpshadow_diagnostic_business_info_html',
			300,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( ! $html ) {
			return null;
		}

		$current_year = (int) gmdate( 'Y' );

		$year_matches = array();
		preg_match_all( '/(?:copyright|&copy;|&#169;|&#xA9;)[^0-9]{0,10}(\d{4})(?:\s*-\s*(\d{4}))?/i', $html, $year_matches );

		$years = array();
		if ( ! empty( $year_matches[1] ) ) {
			$years = array_merge( $years, $year_matches[1] );
		}
		if ( ! empty( $year_matches[2] ) ) {
			$years = array_merge( $years, $year_matches[2] );
		}

		$years = array_filter( array_map( 'intval', $years ) );
		$stats['copyright_years'] = $years;

		if ( empty( $years ) ) {
			$issues[] = __( 'No copyright year found in the homepage footer', 'wpshadow' );
		} else {
			$latest_year = max( $years );
			$stats['latest_year'] = $latest_year;

			if ( $latest_year < $current_year ) {
				$issues[] = __( 'Copyright year appears out of date', 'wpshadow' );
			}
		}

		$has_email = (bool) preg_match( '/mailto:[^"\']+/i', $html );
		$has_phone = (bool) preg_match( '/tel:[^"\']+/i', $html );
		$has_address = (bool) preg_match( '/<address\b|itemprop="address"/i', $html );
		$has_social = (bool) preg_match( '/facebook\.com|instagram\.com|linkedin\.com|x\.com|twitter\.com|youtube\.com/i', $html );

		$stats['has_email_link'] = $has_email;
		$stats['has_phone_link'] = $has_phone;
		$stats['has_address']    = $has_address;
		$stats['has_social']     = $has_social;

		if ( ! $has_email ) {
			$issues[] = __( 'No email contact link found in the footer', 'wpshadow' );
		}

		if ( ! $has_phone ) {
			$issues[] = __( 'No phone contact link found in the footer', 'wpshadow' );
		}

		if ( ! $has_address ) {
			$issues[] = __( 'No business address found in the footer', 'wpshadow' );
		}

		if ( ! $has_social ) {
			$issues[] = __( 'No social media links found for verification', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Up-to-date contact details and copyright dates reassure visitors that your business is active. Missing or outdated info can make your site look abandoned, even if you are still open.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/current-business-info',
			'context'      => array(
				'stats'  => $stats,
				'issues' => $issues,
			),
		);
	}
}
