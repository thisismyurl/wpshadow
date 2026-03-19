<?php
/**
 * No Upsell or Cross-Sell in Email Campaigns Diagnostic
 *
 * Detects when upsell and cross-sell opportunities are not implemented
 * in email marketing campaigns to increase customer value.
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
 * Diagnostic: No Upsell or Cross-Sell in Email
 *
 * Checks whether email campaigns include upsell and cross-sell
 * opportunities to increase average customer value.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Upsell_Or_Cross_Sell_In_Email extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-upsell-crosssell-email';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upsell & Cross-Sell in Email';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether email campaigns include upsell/cross-sell opportunities';

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
		// Check for email marketing platforms
		$has_email_platform = is_plugin_active( 'mailchimp-for-wordpress/mailchimp-for-wordpress.php' ) ||
			is_plugin_active( 'brevo/brevo.php' ) ||
			is_plugin_active( 'fluentcrm/fluent-crm.php' ) ||
			is_plugin_active( 'klaviyo/klv.php' );

		if ( ! $has_email_platform ) {
			return null; // Not applicable
		}

		// Check for upsell/cross-sell campaigns
		$has_upsell_strategy = get_option( 'wpshadow_upsell_email_strategy' );

		if ( ! $has_upsell_strategy ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your email campaigns aren\'t including upsell or cross-sell offers, which means you\'re leaving 20-30% of potential revenue on the table. Upsells offer better versions of what customers already bought (bought blue shirt? offer premium blue shirt). Cross-sells offer complementary products (bought coffee? offer coffee filters). These aren\'t pushy—done well, customers appreciate the suggestions. Email is perfect for this because customers are already engaged and reading your message.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Average Customer Value',
					'potential_gain' => '+20-30% revenue per customer',
					'roi_explanation' => 'Upsell and cross-sell in email campaigns increase AOV by 20-30% with existing customers who already trust your brand.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/upsell-crosssell-email-campaigns',
			);
		}

		return null;
	}
}
