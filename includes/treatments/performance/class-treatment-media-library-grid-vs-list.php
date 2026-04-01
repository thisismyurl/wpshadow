<?php
/**
 * Media Library Grid vs List Treatment
 *
 * Tests performance differences between grid and list views
 * in the media library.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Library_Grid_Vs_List Class
 *
 * Compares query performance for grid and list modes to
 * detect slow media library view rendering.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Library_Grid_Vs_List extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-library-grid-vs-list';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Library Grid vs List';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Compares performance of grid and list views in the media library';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Library_Grid_Vs_List' );
	}
}
