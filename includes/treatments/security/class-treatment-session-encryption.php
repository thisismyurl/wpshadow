<?php
/**
 * Session Data Encryption Treatment
 *
 * Checks if session data is properly encrypted at rest and sensitive data
 * is not stored in sessions or cookies without encryption.
 *
 * @package    WPShadow
 * @subpackage Treatments\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Data Encryption Treatment Class
 *
 * Detects insecure session data storage and provides recommendations
 * for proper session encryption and security.
 *
 * @since 1.6093.1200
 */
class Treatment_Session_Encryption extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'encrypts_session_data';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Session Data Encryption';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies session data is encrypted at rest and sensitive data is not stored insecurely';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Encryption' );
	}
}
