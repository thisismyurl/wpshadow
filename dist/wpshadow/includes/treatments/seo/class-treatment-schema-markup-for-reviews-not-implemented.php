<?php
/**
 * Schema Markup For Reviews Not Implemented Treatment
 *
 * Checks if review schema is implemented.
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
 * Schema Markup For Reviews Not Implemented Treatment Class
 *
 * Detects missing review schema.
 *
 * @since 0.6093.1200
 */
class Treatment_Schema_Markup_For_Reviews_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'schema-markup-for-reviews-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Schema Markup For Reviews Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if review schema is implemented';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Schema_Markup_For_Reviews_Not_Implemented' );
	}
}
