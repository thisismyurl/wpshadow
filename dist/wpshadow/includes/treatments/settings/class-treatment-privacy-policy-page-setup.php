<?php
/**
 * Privacy Policy Page Setup Treatment
 *
 * Verifies that a privacy policy page is properly configured and accessible,
 * which is required for GDPR and other privacy law compliance.
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
 * Privacy Policy Page Setup Treatment Class
 *
 * Ensures the privacy policy page exists, is published, and properly configured.
 *
 * @since 1.6093.1200
 */
class Treatment_Privacy_Policy_Page_Setup extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'privacy-policy-page-setup';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy Page Setup';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies privacy policy page configuration';

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
	 * - Privacy policy page is set in Settings > Privacy
	 * - Page exists and is published
	 * - Page is accessible (not password protected)
	 * - Page has actual content
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Privacy_Policy_Page_Setup' );
	}
}
