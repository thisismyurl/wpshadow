<?php
/**
 * API Authentication Strength Treatment
 *
 * Validates REST API authentication mechanisms and strength.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Authentication Strength Treatment
 *
 * Checks REST API authentication configuration and security.
 *
 * @since 0.6093.1200
 */
class Treatment_API_Authentication_Strength extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-authentication-strength';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Authentication Strength';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates REST API authentication mechanisms and strength';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_API_Authentication_Strength' );
	}
}
