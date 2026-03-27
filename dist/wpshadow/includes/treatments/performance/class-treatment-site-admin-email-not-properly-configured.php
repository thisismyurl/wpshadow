<?php
/**
 * Site Admin Email Not Properly Configured Treatment
 *
 * Tests for admin email configuration.
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
 * Site Admin Email Not Properly Configured Treatment Class
 *
 * Tests for proper admin email configuration.
 *
 * @since 1.6093.1200
 */
class Treatment_Site_Admin_Email_Not_Properly_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-admin-email-not-properly-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Site Admin Email Not Properly Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for proper admin email configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Site_Admin_Email_Not_Properly_Configured' );
	}
}
