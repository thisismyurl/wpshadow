<?php
/**
 * No Referral or Affiliate Program Diagnostic
 *
 * Detects when referral programs are not implemented,
 * missing word-of-mouth growth opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Referral or Affiliate Program
 *
 * Checks whether referral/affiliate programs are
 * configured to incentivize word-of-mouth growth.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Referral_Or_Affiliate_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-referral-affiliate-program';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Referral/Affiliate Program';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether referral programs are configured';

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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for referral/affiliate plugins
		$has_referral = is_plugin_active( 'affiliate-wp/affiliate-wp.php' ) ||
			is_plugin_active( 'referral-system-for-woocommerce/referral-system.php' ) ||
			get_option( 'wpshadow_referral_program' );

		if ( ! $has_referral ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not incentivizing customers to refer others, which is the most cost-effective growth channel. Referral programs work because: referred customers have 37% higher retention, referral programs have 4x better ROI than paid ads, customers trust friend recommendations 90% vs ads 33%. Simple referral structure: give $20 off to referrer and referee, or percentage commission for affiliates. Dropbox grew 3900% in 15 months using referrals.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Customer Acquisition Cost',
					'potential_gain' => '4x better ROI than paid advertising',
					'roi_explanation' => 'Referral programs have 4x better ROI than ads, with referred customers having 37% higher retention.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/referral-affiliate-program',
			);
		}

		return null;
	}
}
