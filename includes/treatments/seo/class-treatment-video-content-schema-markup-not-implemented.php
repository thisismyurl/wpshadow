<?php
/**
 * Video Content Schema Markup Not Implemented Treatment
 *
 * Checks if video schema is implemented.
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
 * Video Content Schema Markup Not Implemented Treatment Class
 *
 * Detects missing video schema.
 *
 * @since 0.6093.1200
 */
class Treatment_Video_Content_Schema_Markup_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-content-schema-markup-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Video Content Schema Markup Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if video schema is implemented';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Video_Content_Schema_Markup_Not_Implemented' );
	}
}
