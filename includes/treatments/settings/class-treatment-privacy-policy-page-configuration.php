<?php
/**
 * Privacy Policy Page Configuration Treatment
 *
 * Ensures a privacy policy page is configured and published.
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
 * Privacy Policy Page Configuration Treatment
 *
 * Validates privacy policy page configuration and content.
 *
 * @since 1.6093.1200
 */
class Treatment_Privacy_Policy_Page_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-page-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Page Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures a privacy policy page is configured and published';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Privacy_Policy_Page_Configuration' );
	}
}
