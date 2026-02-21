<?php
/**
 * Site Membership Settings Misconfigured Treatment
 *
 * Tests for membership and user registration settings.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Membership Settings Misconfigured Treatment Class
 *
 * Tests for membership and user registration settings.
 *
 * @since 1.6033.0000
 */
class Treatment_Site_Membership_Settings_Misconfigured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-membership-settings-misconfigured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Site Membership Settings Misconfigured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for membership and user registration settings';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Site_Membership_Settings_Misconfigured' );
	}
}
