<?php
/**
 * No Email Opt-in Forms Treatment
 *
 * Detects missing email capture forms, losing opportunities to
 * build audience and drive conversions.
 *
 * @package    WPShadow
 * @subpackage Treatments\Engagement
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Email Opt-in Forms Treatment Class
 *
 * Checks for email list building mechanisms to ensure visitor
 * capture and ongoing engagement opportunities.
 *
 * **Why This Matters:**
 * - Email has 40x higher conversion than social
 * - You own your email list (platform independent)
 * - Email ROI: $42 for every $1 spent
 * - Visitors leave and never return without capture
 * - Email enables ongoing relationships
 *
 * **Email Capture Best Practices:**
 * - Popup after 30 seconds or scroll 50%
 * - Inline forms mid-content
 * - Exit-intent popups
 * - Content upgrades (lead magnets)
 * - Welcome bar at top
 *
 * @since 0.6093.1200
 */
class Treatment_No_Email_Opt_In_Forms extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-email-opt-in-forms';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Email Opt-in Forms';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Missing email capture forms, losing opportunities to build your audience';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'engagement';

	/**
	 * Run the treatment check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if no email forms detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_Email_Opt_In_Forms' );
	}
}
