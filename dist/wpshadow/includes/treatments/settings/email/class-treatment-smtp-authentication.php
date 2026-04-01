<?php
/**
 * SMTP Authentication Treatment
 *
 * Tests SMTP authentication configuration and validates credentials.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SMTP Authentication Treatment Class
 *
 * Verifies that SMTP authentication is properly configured. This is like
 * having a password to prove who you are - without it, email servers will
 * reject your messages.
 *
 * @since 0.6093.1200
 */
class Treatment_Smtp_Authentication extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'smtp-authentication';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SMTP Authentication';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates SMTP authentication configuration and credentials';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the SMTP authentication treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if authentication issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SMTP_Authentication' );
	}
}
