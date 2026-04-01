<?php
/**
 * Privacy Policy Page Not Configured Treatment
 *
 * Tests for privacy policy configuration.
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
 * Privacy Policy Page Not Configured Treatment Class
 *
 * Tests for privacy policy page configuration.
 *
 * @since 0.6093.1200
 */
class Treatment_Privacy_Policy_Page_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-page-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Page Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for privacy policy configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Privacy_Policy_Page_Not_Configured' );
	}
}
