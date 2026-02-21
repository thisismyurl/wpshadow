<?php
/**
 * Privacy Policy Treatment
 *
 * Tests if privacy policy exists and is current.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1522
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Treatment Class
 *
 * Evaluates whether the site has a current, accessible privacy policy.
 * Checks for GDPR compliance features and privacy management tools.
 *
 * @since 1.6035.1522
 */
class Treatment_Privacy_Policy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'maintains_privacy_policy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if privacy policy exists and is current';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1522
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Privacy_Policy' );
	}
}
