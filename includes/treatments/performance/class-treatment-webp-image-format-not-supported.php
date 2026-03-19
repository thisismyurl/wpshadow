<?php
/**
 * WebP Image Format Not Supported Treatment
 *
 * Checks if WebP format is supported.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WebP Image Format Not Supported Treatment Class
 *
 * Detects missing WebP support.
 *
 * @since 1.6093.1200
 */
class Treatment_WebP_Image_Format_Not_Supported extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'webp-image-format-not-supported';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'WebP Image Format Not Supported';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WebP format is supported';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_WebP_Image_Format_Not_Supported' );
	}
}
