<?php
/**
 * Mobile Email Signup Form Treatment
 *
 * Optimizes email signup form for mobile devices.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Email Signup Form Treatment Class
 *
 * Validates email signup form optimization for mobile devices,
 * ensuring proper input type and button sizing for better conversions.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Email_Signup_Form extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-email-signup-form';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Email Signup Form';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Optimize email signup form for mobile devices with proper input types and button sizing';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'forms';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Email_Signup_Form' );
	}
}
