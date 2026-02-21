<?php
/**
 * User Login Notification System Treatment
 *
 * Validates that important login events are being logged and monitored
 * for security purposes (failed logins, unusual activity).
 * Login notifications = admin alerted to suspicious activity.
 * Detects compromises early (before major damage).
 *
 * **What This Check Does:**
 * - Checks if login monitoring enabled
 * - Validates failed login notifications
 * - Tests unusual login detection (new location/device)
 * - Checks admin notification for successful admin logins
 * - Validates login log retention
 * - Returns severity if monitoring disabled
 *
 * **Why This Matters:**
 * Without monitoring: account compromised, admin doesn't know.
 * Attacker operates silently for weeks/months.
 * With notifications: suspicious login triggers alert.
 * Admin investigates immediately. Compromise detected early.
 *
 * **Business Impact:**
 * Admin account compromised (phishing). Attacker logs in from Russia.
 * Without notifications: attacker operates 2 months undetected. Installs
 * malware. Steals data. Damage: $500K+. With notifications: admin gets
 * email "Login from Russia" immediately. Resets password. Account secured
 * within minutes. Damage prevented.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Suspicious activity detected
 * - #9 Show Value: Early compromise detection
 * - #10 Beyond Pure: Security monitoring embedded
 *
 * **Related Checks:**
 * - User Login Attempt Limiting (complementary)
 * - Activity Logging Overall (broader)
 * - Admin Account Security (related)
 *
 * **Learn More:**
 * Login monitoring setup: https://wpshadow.com/kb/login-monitoring
 * Video: Configuring login notifications (11min): https://wpshadow.com/training/login-alerts
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1335
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Login Notification System Treatment Class
 *
 * Checks login monitoring and notification configuration.
 *
 * **Detection Pattern:**
 * 1. Check if login monitoring plugin active
 * 2. Validate failed login notifications enabled
 * 3. Test unusual login detection
 * 4. Check admin notification settings
 * 5. Validate log retention period
 * 6. Return if monitoring disabled
 *
 * **Real-World Scenario:**
 * Admin receives email: "Login from new location (Tokyo, Japan)".
 * Admin didn't login. Immediately resets password. Checks activity log.
 * Attacker accessed for 10 minutes. No damage done. Crisis averted.
 * Without notification: attacker operates undetected for months.
 *
 * **Implementation Notes:**
 * - Checks monitoring configuration
 * - Validates notification settings
 * - Tests alert triggers
 * - Severity: high (no monitoring)
 * - Treatment: enable login monitoring and notifications
 *
 * @since 1.6032.1335
 */
class Treatment_User_Login_Notification_System extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-login-notification-system';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'User Login Notification System';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates login monitoring and notifications';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_User_Login_Notification_System' );
	}
}
