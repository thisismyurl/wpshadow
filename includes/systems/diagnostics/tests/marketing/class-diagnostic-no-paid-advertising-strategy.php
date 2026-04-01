<?php
/**
 * No Paid Advertising Strategy Diagnostic
 *
 * Detects when paid advertising is not being used,
 * missing controllable growth channel.
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
 * Diagnostic: No Paid Advertising Strategy
 *
 * Checks whether paid advertising is being used
 * for scalable customer acquisition.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Paid_Advertising_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-paid-advertising-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Paid Advertising Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether paid ads are being used';

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
		// Check for advertising/conversion tracking
		$has_google_ads = preg_match( '/google.*ads|googleadservices/i', home_url() ) ||
			is_plugin_active( 'google-ads-conversion-tracking/plugin.php' );

		$has_analytics_4 = get_option( 'wpshadow_ga4_enabled' );

		if ( ! $has_google_ads && ! $has_analytics_4 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not using paid advertising, which means relying only on organic growth (slow). Paid ads are scalable: you control budget and audience. Channels: Google Ads (search), Facebook/Instagram (display/social), LinkedIn (B2B), YouTube (video). ROI depends on conversion optimization: if you convert 1 visitor per 100, ads cost $1/click means $100 CAC. Good ROI is 3:1 (spend $1 make $3). Test with small budget to learn what works.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Scalable Customer Acquisition',
					'potential_gain' => 'Scale growth with controlled budget',
					'roi_explanation' => 'Paid advertising provides scalable customer acquisition, allowing faster growth than organic alone.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/paid-advertising-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
