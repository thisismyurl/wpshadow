<?php
/**
 * JSON-LD Breadcrumb Schema Not Implemented Treatment
 *
 * Checks if JSON-LD breadcrumb schema is implemented.
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
 * JSON-LD Breadcrumb Schema Not Implemented Treatment Class
 *
 * Detects missing JSON-LD breadcrumb schema.
 *
 * @since 0.6093.1200
 */
class Treatment_JSON_LD_Breadcrumb_Schema_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'json-ld-breadcrumb-schema-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'JSON-LD Breadcrumb Schema Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if JSON-LD breadcrumb schema is implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_JSON_LD_Breadcrumb_Schema_Not_Implemented' );
	}
}
