<?php
/**
 * FAQ Schema Markup Not Implemented Treatment
 *
 * Checks if FAQ schema is implemented.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FAQ Schema Markup Not Implemented Treatment Class
 *
 * Detects missing FAQ schema.
 *
 * @since 1.6030.2352
 */
class Treatment_FAQ_Schema_Markup_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'faq-schema-markup-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'FAQ Schema Markup Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if FAQ schema is implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_FAQ_Schema_Markup_Not_Implemented' );
	}
}
