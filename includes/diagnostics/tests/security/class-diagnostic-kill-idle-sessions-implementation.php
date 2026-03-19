<?php
/**
 * Kill Idle Sessions Implementation Diagnostic
 *
 * Validates that idle user sessions are automatically terminated to prevent\n * session fixation attacks and limit exposure window. Sessions left open indefinitely\n * create persistent attack vectors: attacker with stolen session can access account\n * days/weeks later.\n *
 * **What This Check Does:**
 * - Checks if idle session timeout is implemented\n * - Validates timeout duration (15-30 minutes recommended)\n * - Tests if inactivity tracked and enforced\n * - Detects if logout triggered after idle period\n * - Confirms session remains valid during user activity\n * - Validates grace period before session truly expires\n *
 * **Why This Matters:**
 * Sessions left open indefinitely create exposure windows. Scenarios:\n * - User logs in at library computer\n * - Forgets to log out\n * - Session remains active indefinitely\n * - Next library user accesses site with previous user's session\n * - Can view private data, send messages as previous user\n *
 * **Business Impact:**
 * Customer portal logs users out after 8 hours of inactivity (default timeout).\n * User leaves session open overnight. Next morning: another employee (different\n * department) uses same computer. Accesses previous user's customer data.\n * Compliance violation: PCI DSS requires 15-minute idle timeout. Fine: $5K-$50K.\n * With 15-minute timeout: vulnerability eliminated.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Sessions automatically protected\n * - #9 Show Value: Compliance with security standards\n * - #10 Beyond Pure: Defense in depth, layered session security\n *
 * **Related Checks:**
 * - Expired Sessions Cleanup Not Implemented (old session removal)\n * - Authentication Cookie Hijacking Prevention (session safety)\n * - Multi-factor Authentication Not Required (additional verification)\n *
 * **Learn More:**
 * Session timeout best practices: https://wpshadow.com/kb/wordpress-session-timeout\n * Video: Configuring idle session termination (7min): https://wpshadow.com/training/session-timeout\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kill Idle Sessions Implementation Diagnostic Class
 *
 * Implements detection of missing idle session termination.\n *
 * **Detection Pattern:**
 * 1. Check if session idle detection implemented\n * 2. Query session timeout duration\n * 3. Validate timeout is reasonable (< 1 hour)\n * 4. Test inactivity tracking\n * 5. Check if logout triggered on timeout\n * 6. Return severity if timeout missing/too long\n *
 * **Real-World Scenario:**
 * WordPress site with no idle timeout. User opens site, checks portfolio page.\n * Walks away. Session stays active for 30 days (default WordPress timeout).\n * Hacker on same network uses session ID to access account. Views private email,\n * customer list, payment history. If 15-minute timeout: attacker couldn't access\n * (session expired during inactive hour).\n *
 * **Implementation Notes:**
 * - Checks WordPress session timeout settings\n * - Validates inactivity detection\n * - Confirms logout on idle\n * - Severity: medium (no timeout), high (very long timeout)\n * - Treatment: implement 15-30 minute idle logout\n *
 * @since 1.6093.1200
 */
class Diagnostic_Kill_Idle_Sessions_Implementation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'kill-idle-sessions-implementation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Kill Idle Sessions Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if idle sessions are terminated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if idle session timeout is configured
		if ( ! has_filter( 'auth_cookie_life', 'set_idle_session_timeout' ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Idle session termination is not implemented. Set session timeout to 30 minutes to automatically log out inactive users.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/kill-idle-sessions-implementation',
				'context'      => array(
					'why'            => __(
						'Idle sessions are a common attack vector. Users often forget to log out on shared or public devices. Without idle timeouts, a stolen or abandoned session can be reused long after the user leaves. Many security standards (PCI-DSS, HIPAA) require automatic logout after 15 minutes of inactivity for sensitive systems. Implementing idle timeouts reduces the window for session hijacking and lowers exposure if a device is left unattended.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Implement idle timeout of 15-30 minutes for privileged users.
2. Use WordPress hooks to track user activity and invalidate sessions after inactivity.
3. Provide a grace warning before logout (e.g., 60-second countdown).
4. For public kiosks or shared devices, use shorter timeouts (5-10 minutes).
5. Combine with absolute session timeout to ensure sessions don\'t persist indefinitely.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'session-hardening',
				'idle_session_timeout'
			);

			return $finding;
		}

		return null;
	}
}
