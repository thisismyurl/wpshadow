<?php
/**
 * Email Deliverability Configuration Not Optimized Diagnostic
 *
 * Checks if email configuration is optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Deliverability Configuration Not Optimized Diagnostic Class
 *
 * Detects unoptimized email configuration.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Email_Deliverability_Configuration_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-deliverability-configuration-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Deliverability Configuration Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if email configuration is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for mail plugin
		if ( ! is_plugin_active( 'wps-smtp/wps-smtp.php' ) && ! is_plugin_active( 'mailgun/mailgun.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Email deliverability is not optimized. Configure SPF, DKIM, and DMARC records, and use a mail service for better delivery.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/email-deliverability-configuration-not-optimized',
			);
		}

		return null;
	}
}
