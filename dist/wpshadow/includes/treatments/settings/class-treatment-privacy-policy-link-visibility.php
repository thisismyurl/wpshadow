<?php
/**
 * Privacy Policy Link Visibility Treatment
 *
 * Ensures the privacy policy is easily accessible to visitors through
 * proper linking in key locations like footer, registration forms, etc.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Link Visibility Treatment Class
 *
 * Checks that the privacy policy link is visible in appropriate locations.
 *
 * @since 1.6093.1200
 */
class Treatment_Privacy_Policy_Link_Visibility extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-link-visibility';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Link Visibility';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies privacy policy is easily accessible';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - Privacy policy link in comment forms
	 * - Privacy policy link in registration forms
	 * - Privacy policy in footer menu
	 * - Privacy policy auto-linking enabled
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Privacy_Policy_Link_Visibility' );
	}
}
