<?php
/**
 * No SMS Marketing or Text Campaigns Diagnostic
 *
 * Detects when SMS marketing is not being used,
 * missing highest-engagement communication channel.
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
 * Diagnostic: No SMS Marketing or Text Campaigns
 *
 * Checks whether SMS marketing is implemented
 * for highest-engagement customer communication.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_SMS_Marketing_Or_Text_Campaigns extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-sms-marketing-text-campaigns';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SMS Marketing & Text Campaigns';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether SMS marketing is configured';

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
		// Check for SMS marketing plugins
		$has_sms = is_plugin_active( 'twilio-sms-notifications/twilio-sms.php' ) ||
			is_plugin_active( 'sms-notifications-for-woocommerce/woocommerce-sms.php' );

		if ( ! $has_sms ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'SMS marketing isn\'t configured, which means you\'re missing the highest-engagement channel. Email open rate: 20-30%, SMS open rate: 98%. Text messages are read within 3 minutes vs email days later. Use SMS for: order confirmations, shipping updates, flash sales (limited-time offers), event reminders, customer support. SMS generates 60-70% higher conversion than email. Even small campaigns get immediate response.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Customer Engagement & Conversion',
					'potential_gain' => '+60-70% higher conversion than email, 98% open rate',
					'roi_explanation' => 'SMS has 98% open rate vs email 20-30%, and generates 60-70% higher conversion.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/sms-marketing-text-campaigns?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
