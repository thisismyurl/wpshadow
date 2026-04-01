<?php
/**
 * Media REST API Endpoint Security Treatment
 *
 * Checks if REST API media endpoints have proper authentication and permissions.
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
 * Media REST API Endpoint Security Treatment Class
 *
 * Verifies that WordPress REST API media endpoints have proper
 * authentication, capability checks, and permission validation.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Rest_Api_Endpoint_Security extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-rest-api-endpoint-security';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media REST API Endpoint Security';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if REST API media endpoints have proper authentication and permissions';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Rest_Api_Endpoint_Security' );
	}
}
