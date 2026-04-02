<?php
/**
 * No Email Deliverability Monitoring Diagnostic
 *
 * Detects when email deliverability is not being monitored,
 * missing issues that reduce inbox placement rates.
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
 * Diagnostic: No Email Deliverability Monitoring
 *
 * Checks whether email deliverability is being monitored
 * to track inbox placement and spam rates.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Email_Deliverability_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-email-deliverability-monitoring';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Deliverability Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether email deliverability is monitored';

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
		// Check for deliverability monitoring
		$has_monitoring = is_plugin_active( 'post-smtp/postman-smtp.php' ) ||
			is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) ||
			get_option( 'wpshadow_email_deliverability_monitoring' );

		if ( ! $has_monitoring ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not monitoring email deliverability, which means you don\'t know if your emails reach inboxes. Email deliverability measures: what % reach inbox vs spam folder, what % bounce, what domains block you. Without monitoring, you could have 50% of emails going to spam without knowing. Good deliverability monitoring shows: bounce rates, spam rates, inbox placement by provider (Gmail, Outlook, etc.). This directly impacts email marketing ROI.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Email Marketing Effectiveness',
					'potential_gain' => 'Detect and fix 20-50% deliverability loss',
					'roi_explanation' => 'Deliverability monitoring reveals when emails go to spam, enabling fixes that restore 20-50% of lost reach.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/email-deliverability-monitoring',
			);
		}

		return null;
	}
}
