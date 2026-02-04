<?php
/**
 * Post-Purchase Email Sequence Diagnostic
 *
 * Detects when post-purchase email sequences are not implemented
 * to guide customers through onboarding and drive repeat purchases.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Post-Purchase Email Sequence
 *
 * Checks whether automated post-purchase email sequences
 * are configured for onboarding and repeat sales.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Post_Purchase_Email_Sequence extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-post-purchase-email-sequence';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post-Purchase Email Sequence';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether post-purchase email sequences are configured';

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
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for email automation platforms
		$has_email_automation = is_plugin_active( 'fluentcrm/fluent-crm.php' ) ||
			is_plugin_active( 'mailchimp-for-wordpress/mailchimp-for-wordpress.php' ) ||
			is_plugin_active( 'brevo/brevo.php' ) ||
			is_plugin_active( 'convertkit/convertkit.php' );

		// Check for post-purchase hooks/automation
		$has_post_purchase = get_option( 'wpshadow_post_purchase_email_sequence' );

		if ( ! $has_email_automation && ! $has_post_purchase ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not sending post-purchase email sequences yet. This is like not welcoming a new customer or helping them get the most value from their purchase. A simple post-purchase sequence might include: order confirmation → shipping notification → delivery notice + thank you → helpful tips → ask for review → offer related product. This sequence drives onboarding success and repeat purchases by 30-50%.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Customer Success & Repeat Sales',
					'potential_gain' => '+30-50% repeat purchase rate',
					'roi_explanation' => 'Post-purchase sequences guide customers toward success with their purchase, dramatically improving satisfaction and repeat sales rates.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/post-purchase-email-sequence',
			);
		}

		return null;
	}
}
