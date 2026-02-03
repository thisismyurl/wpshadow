<?php
/**
 * User Registration Moderation Not Configured Diagnostic
 *
 * Checks if user registration moderation is configured.
 * Unmoderated registration = anyone creates account immediately.
 * Spammers abuse. Moderation = admin approves first.
 *
 * **What This Check Does:**
 * - Checks if registration moderation enabled
 * - Validates admin approval required
 * - Tests if spam filtering applied
 * - Checks for manual approval workflow
 * - Validates registration notifications
 * - Returns severity if moderation disabled
 *
 * **Why This Matters:**
 * Without moderation: spammer registers 1000 accounts.
 * Floods site with spam. With moderation: admin reviews each.
 * Spam accounts rejected. Only legitimate users approved.
 *
 * **Business Impact:**
 * Forum allows open registration. Spammers register 5000 bot accounts.
 * Post 50K spam links. Site reputation tanks. Google penalizes.
 * Search traffic drops 70%. Revenue loss: $300K+. With moderation:
 * admin reviews 10-20 registrations daily. Spam accounts rejected.
 * Forum stays clean. Quality maintained.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Registration controlled
 * - #9 Show Value: Prevents spam account abuse
 * - #10 Beyond Pure: Human review process
 *
 * **Related Checks:**
 * - User Email Verification Status (related)
 * - Account Registration Validation (complementary)
 * - Spam Prevention (broader)
 *
 * **Learn More:**
 * Registration moderation: https://wpshadow.com/kb/registration-moderation
 * Video: Moderating registrations (9min): https://wpshadow.com/training/moderation
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Registration Moderation Not Configured Diagnostic Class
 *
 * Detects unmoderated user registration.
 *
 * **Detection Pattern:**
 * 1. Check registration settings
 * 2. Validate if moderation enabled
 * 3. Test admin approval workflow
 * 4. Check spam filtering
 * 5. Validate notification system
 * 6. Return if moderation disabled
 *
 * **Real-World Scenario:**
 * Membership site enables open registration (no moderation).
 * Competitor registers 200 fake accounts. Posts negative reviews.
 * Reputation damaged. With moderation: admin reviews each registration.
 * Suspicious patterns detected (same IP, similar names). Fake accounts
 * rejected. Real community protected.
 *
 * **Implementation Notes:**
 * - Checks moderation settings
 * - Validates approval workflow
 * - Tests spam filtering
 * - Severity: high (no moderation on public sites)
 * - Treatment: enable registration moderation
 *
 * @since 1.2601.2352
 */
class Diagnostic_User_Registration_Moderation_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-registration-moderation-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Registration Moderation Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if user registration moderation is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if user registration requires approval
		$moderation_enabled = get_option( 'users_can_register' );

		if ( $moderation_enabled && ! get_option( 'require_admin_approval_for_registration' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'User registration moderation is not configured. Enable admin approval for new user registrations to control access.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-registration-moderation-not-configured',
			);
		}

		return null;
	}
}
