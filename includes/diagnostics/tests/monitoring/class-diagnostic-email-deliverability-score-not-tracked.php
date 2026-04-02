<?php
/**
 * Email Deliverability Score Not Tracked Diagnostic
 *
 * Checks if email deliverability is tracked.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Deliverability Score Not Tracked Diagnostic Class
 *
 * Detects missing email tracking.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Email_Deliverability_Score_Not_Tracked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-deliverability-score-not-tracked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Deliverability Score Not Tracked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if email deliverability is tracked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for email tracking
		if ( ! get_option( 'email_deliverability_score' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Email deliverability is not tracked. Monitor bounce rates, DKIM/SPF/DMARC records, and sender reputation to improve email delivery.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/email-deliverability-score-not-tracked',
			);
		}

		return null;
	}
}
