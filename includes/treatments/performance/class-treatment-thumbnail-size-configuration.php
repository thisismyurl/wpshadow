<?php
/**
 * Thumbnail Size Configuration Treatment
 *
 * Verifies thumbnail image sizes are properly configured for optimal performance
 * and consistent display across the site.
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
 * Thumbnail Size Configuration Treatment Class
 *
 * Checks WordPress thumbnail size settings for best practices.
 *
 * @since 0.6093.1200
 */
class Treatment_Thumbnail_Size_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'thumbnail-size-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Thumbnail Size Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies thumbnail sizes are optimized';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Thumbnail_Size_Configuration' );
	}
}
