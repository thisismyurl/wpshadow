<?php
/**
 * No Retargeting or Remarketing Strategy Diagnostic
 *
 * Detects when retargeting is not being used,
 * losing visitors who didn't convert on first visit.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Retargeting or Remarketing Strategy
 *
 * Checks whether retargeting/remarketing is implemented
 * to recapture lost visitors.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Retargeting_Or_Remarketing_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-retargeting-remarketing-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Retargeting & Remarketing Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether retargeting is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for retargeting pixels
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );

		// Check for common retargeting services
		$has_retargeting = preg_match( '/facebook.*pixel|google.*tag|adroll|retargeter|criteo/i', $body );

		if ( ! $has_retargeting ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not using retargeting, which means lost visitors never come back. Retargeting shows ads to people who visited but didn\'t convert. This works because: 96-98% of first-time visitors don\'t convert, but they\'re warmed up (not cold traffic). Retargeting ROI: 10x better than cold traffic. Platforms: Facebook Pixel, Google Ads Remarketing, AdRoll. Segment by: pages visited, products viewed, cart abandoners. Typical conversion: 2-5x higher than first visit.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Visitor Recovery & Conversion',
					'potential_gain' => '10x better ROI, 2-5x conversion vs first visit',
					'roi_explanation' => 'Retargeting recaptures 96-98% of visitors who didn\'t convert, with 10x better ROI than cold traffic.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/retargeting-remarketing-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
