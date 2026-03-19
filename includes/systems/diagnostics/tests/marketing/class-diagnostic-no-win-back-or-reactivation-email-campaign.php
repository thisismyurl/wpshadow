<?php
/**
 * Win-Back/Reactivation Email Campaign Diagnostic
 *
 * Detects when win-back or reactivation campaigns are not implemented
 * to re-engage inactive customers cost-effectively.
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
 * Diagnostic: No Win-Back or Reactivation Email Campaign
 *
 * Checks whether the site has implemented email campaigns
 * to win back or reactivate inactive customers.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Win_Back_Or_Reactivation_Email_Campaign extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-winback-reactivation-campaign';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Win-Back/Reactivation Email Campaign';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether reactivation campaigns exist for inactive customers';

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

		// Check for automation workflow
		$has_automation = get_option( 'wpshadow_reactivation_campaign' );

		if ( ! $has_email_platform && ! $has_automation ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not reaching out to inactive customers yet. This is like ignoring a friend who stopped calling—it\'s often easier to reactivate someone who\'s bought before than to find a new customer. Win-back campaigns cost 2-3x less than acquiring new customers while achieving 40-50% conversion rates. A simple email saying "We miss you—here\'s a special offer" can win back 10-20% of inactive customers.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Customer Reactivation',
					'potential_gain' => '10-20% of inactive customers',
					'roi_explanation' => 'Reactivation costs 2-3x less than acquisition with 40-50% conversion rates, making it highly profitable.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/win-back-reactivation-campaign',
			);
		}

		return null;
	}
}
