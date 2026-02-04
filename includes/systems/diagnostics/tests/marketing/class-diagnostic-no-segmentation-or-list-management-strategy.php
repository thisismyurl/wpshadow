<?php
/**
 * No Segmentation or List Management Strategy Diagnostic
 *
 * Detects when email lists are not segmented or managed strategically,
 * causing lower engagement and higher unsubscribe rates.
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
 * Diagnostic: No Segmentation or List Management Strategy
 *
 * Checks whether email lists are segmented based on
 * subscriber behavior, interests, or characteristics.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Segmentation_Or_List_Management_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-segmentation-list-management-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Segmentation & List Management';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether email lists are segmented by behavior or interests';

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
		// Check for email marketing platforms with segmentation
		$has_email_platform = is_plugin_active( 'mailchimp-for-wordpress/mailchimp-for-wordpress.php' ) ||
			is_plugin_active( 'brevo/brevo.php' ) ||
			is_plugin_active( 'fluentcrm/fluent-crm.php' ) ||
			is_plugin_active( 'klaviyo/klv.php' );

		if ( ! $has_email_platform ) {
			return null; // Not applicable
		}

		// Check for segmentation strategy
		$has_segmentation = get_option( 'wpshadow_email_segmentation_strategy' );

		if ( ! $has_segmentation ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re sending the same email to your entire list, which is like serving the same meal to everyone—vegetarians get frustrated, meat-lovers get frustrated. Email segmentation sends relevant emails to relevant people: customers get different emails than prospects, email-interested subscribers get more emails than light subscribers. Segmented email campaigns have 14-100% higher open rates and 2-5x better conversion rates than broadcast emails.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Email Engagement & Conversion',
					'potential_gain' => '+14-100% open rate, +2-5x conversion',
					'roi_explanation' => 'Segmentation dramatically improves engagement by ensuring subscribers receive relevant content to their interests and behavior.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/email-segmentation-list-management',
			);
		}

		return null;
	}
}
