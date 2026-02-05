<?php
/**
 * Fallback Authentication Not Available Treatment
 *
 * Checks fallback auth.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Fallback_Authentication_Not_Available Class
 *
 * Performs treatment check for Fallback Authentication Not Available.
 *
 * @since 1.6033.2033
 */
class Treatment_Fallback_Authentication_Not_Available extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'fallback-authentication-not-available';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Fallback Authentication Not Available';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks fallback auth';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'provide_fallback_authentication' ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Fallback authentication not available. Provide backup authentication method when primary service is unavailable.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/fallback-authentication-not-available',
				'context'      => array(
					'why'            => __( 'No fallback auth = denial of service. Primary auth fails (token expired, 2FA lost, email inaccessible). Users locked out permanently. Business continuity broken. Angry users, support overload, reputation damage. Fallback enables graceful degradation. Scenario: 2FA service outage (Authy, Google Authenticator sync fails). Users can\'t log in. With fallback (security questions, backup codes, temp password): users regain access quickly. Zero fallback = 100% account lockout impact. IT must manually reset passwords (costly, unsecure).', 'wpshadow' ),
					'recommendation' => __( '1. Generate 10 backup codes when 2FA enabled (4-digit format, one-time use). 2. Store backup codes hashed with wp_hash_password() in database. 3. Implement email-based recovery (send temp login link to recovery email). 4. Add security questions as fallback (3 questions, answers hashed). 5. Implement recovery codes (distinct from backup codes, longer validity). 6. Backup codes: users print and store securely (warn about physical theft). 7. Rate limit recovery attempts (max 3 per hour, 10 per day). 8. Log recovery attempts in activity log (manual password reset suspicious). 9. Notify user via registered email when recovery used. 10. Force password change after using fallback auth (suspicious activity).', 'wpshadow' ),
				),
			);
			return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'authentication', 'fallback-auth' );
		}

		return null;
	}
}
