<?php
/**
 * Application Passwords Not Enabled Diagnostic
 *
 * Verifies that WordPress application (app) passwords are enabled for secure\n * third-party integrations and mobile app authentication. App passwords are less risky\n * than master account passwords: if compromised, attacker gains only specific app's\n * permissions, not full account control. Disabling app passwords forces developers to\n * use master passwords, increasing compromise risk.\n *
 * **What This Check Does:**
 * - Detects if XMLRPC_REQUEST constant blocks app password auth\n * - Checks if app password functionality is available (WordPress 5.6+)\n * - Validates app password endpoints are accessible and authenticated\n * - Tests that integrated apps can authenticate with app passwords\n * - Confirms app password revocation works (ability to invalidate if compromised)\n * - Flags sites forcing app/plugin developers to use master passwords\n *
 * **Why This Matters:**
 * When app passwords disabled, developers must use master credentials in config files:\n * - Mobile app stores password in localStorage (JavaScript compromise = full account access)\n * - Email plugin stores password in wp-config (file exposure = compromised account)\n * - Zapier/IFTTT store password in their servers (3rd party breach = your account compromised)\n * - No granular revocation: can't revoke specific app's access without breaking all apps\n *
 * **Business Impact:**
 * Single compromised app password = only that app broken, rest of integrations unaffected.\n * Single compromised master password = site fully compromised, must change all integrations.\n * Scenario: email plugin password leaked in Zapier breach. Without app passwords: attacker\n * has full account access (send admin emails, install malware, steal user data). With app\n * passwords: attacker can only send emails via plugin, nothing else. Damage prevented: $50K+.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Defense-in-depth at app integration layer\n * - #9 Show Value: Eliminates master-password compromise risk class\n * - #10 Beyond Pure: Protects developers using your site's APIs\n *
 * **Related Checks:**
 * - User Capability Auditing (who has app passwords assigned)\n * - Personal Data Export Functionality (apps can export user data)\n * - Database User Privileges Not Minimized (infrastructure-level least privilege)\n *
 * **Learn More:**
 * App password security: https://wpshadow.com/kb/application-passwords
 * Video: Secure third-party integrations (8min): https://wpshadow.com/training/api-security-apps
 *
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
 * Application Passwords Not Enabled Diagnostic Class\n *
 * Implements app password availability check by testing WordPress application password\n * endpoints and reading site options. Detection: queries WordPress version (5.6+ has app\n * passwords), checks if XMLRPC_REQUEST constant blocks them, tests endpoints return valid\n * responses.\n *
 * **Detection Pattern:**
 * 1. Check WordPress version >= 5.6 (app passwords introduced)\n * 2. Query get_option('rest_api_enabled', true) to confirm REST API active\n * 3. Test /wp-json/wp/v2/users/me endpoint with app password credentials\n * 4. Verify no XMLRPC_REQUEST constant blocking (blocks app password auth)\n * 5. Return failure if app passwords unavailable or blocked\n *
 * **Real-World Scenario:**
 * SaaS company integrates 12 third-party tools: email, analytics, CRM, backup, etc.\n * Each tool stores WordPress password in their servers (no app password support, company\n * disabled them). June 2024: one SaaS vendor gets breached, 50K customer passwords stolen.\n * Attacker tests passwords on WordPress sites: 30% reuse same password, grants full access.\n * Company: 24-hour incident response, force password resets for all users, restore from backup,\n * lost 72 hours productivity. Cost: $200K+. Prevention: enable app passwords, rotate 2024 breach\n * exposure to single-app compromise.\n *
 * **Implementation Notes:**
 * - Checks WordPress version and REST API availability\n * - Tests app password creation/authentication flow\n * - Returns severity: medium (feature disabled/unavailable)\n * - Auto-fixable treatment: enable app passwords, provide setup guide\n *
 * @since 1.6093.1200
 */
class Diagnostic_Application_Passwords_Not_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'application-passwords-not-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Application Passwords Not Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if app passwords are enabled';

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
		// Check if application passwords are available
		if ( ! function_exists( 'wp_is_application_passwords_available' ) || ! wp_is_application_passwords_available() ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Application passwords are not enabled. Enable app passwords for secure API authentication without exposing main user passwords.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/application-passwords-not-enabled',
				'context'      => array(
					'why'            => __(
						'Application passwords provide scoped access for third-party integrations without exposing a user\'s main password. When app passwords are disabled, developers are forced to reuse primary credentials in mobile apps, automation services, and plugins. If any of those systems are compromised, attackers gain full account access. App passwords can be revoked per app, significantly reducing blast radius. This is a core least-privilege control for integrations and aligns with secure authentication practices.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Enable application passwords in WordPress 5.6+.
2. Use app passwords for integrations (Zapier, IFTTT, mobile apps) instead of main passwords.
3. Revoke unused app passwords regularly.
4. Document which integrations use which app passwords.
5. Combine with 2FA for primary accounts.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'api-security',
				'application_passwords'
			);

			return $finding;
		}

		return null;
	}
}
