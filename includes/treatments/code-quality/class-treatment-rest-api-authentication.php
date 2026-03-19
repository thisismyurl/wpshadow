<?php
/**
 * REST API Authentication and Permissions Treatment
 *
 * Validates REST API authentication and permission implementations.
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
 * Treatment_REST_API_Authentication Class
 *
 * Checks REST API authentication and permission issues.
 *
 * @since 1.6093.1200
 */
class Treatment_REST_API_Authentication extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-authentication';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Authentication';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates REST API authentication and permission implementations';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'rest-api';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_REST_API_Authentication' );
	}
}
