<?php
/**
 * REST API Not Properly Secured Treatment
 *
 * Tests for REST API security.
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
 * REST API Not Properly Secured Treatment Class
 *
 * Tests for REST API security configuration.
 *
 * @since 0.6093.1200
 */
class Treatment_REST_API_Not_Properly_Secured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-not-properly-secured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Not Properly Secured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for REST API security';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_REST_API_Not_Properly_Secured' );
	}
}
