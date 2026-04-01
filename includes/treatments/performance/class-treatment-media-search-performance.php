<?php
/**
 * Media Search Performance Treatment
 *
 * Measures media library search speed. Tests search query optimization and
 * identifies performance bottlenecks.
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
 * Media Search Performance Treatment Class
 *
 * Checks for search performance issues in the media library.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Search_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-search-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Search Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures media library search speed and query optimization';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Search_Performance' );
	}
}
