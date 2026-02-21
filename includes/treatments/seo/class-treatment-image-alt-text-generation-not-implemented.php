<?php
/**
 * Image Alt Text Generation Not Implemented Treatment
 *
 * Checks if auto alt text generation is implemented.
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
 * Image Alt Text Generation Not Implemented Treatment Class
 *
 * Detects missing auto alt text.
 *
 * @since 1.6030.2352
 */
class Treatment_Image_Alt_Text_Generation_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-alt-text-generation-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Alt Text Generation Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if auto alt text generation is implemented';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Image_Alt_Text_Generation_Not_Implemented' );
	}
}
