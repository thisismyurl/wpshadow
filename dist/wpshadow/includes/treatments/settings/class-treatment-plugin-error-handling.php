<?php
/**
 * Plugin Error Handling Treatment
 *
 * Checks for missing error handling around remote requests.
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
 * Plugin Error Handling Treatment Class
 *
 * Detects active plugins that may call remote APIs without checking for WP_Error.
 *
 * @since 0.6093.1200
 */
class Treatment_Plugin_Error_Handling extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-error-handling';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Error Handling';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugins handle remote request errors';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Error_Handling' );
	}
}
