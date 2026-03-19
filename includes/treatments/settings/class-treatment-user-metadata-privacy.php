<?php
/**
 * User Metadata Privacy Treatment
 *
 * Validates that sensitive user metadata is protected from exposure
 * and is not accessible to users without proper permissions.
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
 * User Metadata Privacy Treatment Class
 *
 * Checks user metadata privacy and security.
 *
 * @since 1.6093.1200
 */
class Treatment_User_Metadata_Privacy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-metadata-privacy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'User Metadata Privacy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user metadata privacy and security';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_User_Metadata_Privacy' );
	}
}
