<?php
/**
 * Image Alt Text Generic Treatment
 *
 * Checks if images have meaningful, descriptive alt text.
 *
 * @since   1.6035.1400
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Alt Text Treatment Class
 *
 * Validates that images have descriptive alt text for screen readers.
 *
 * @since 1.6035.1400
 */
class Treatment_Image_Alt_Text_Generic extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-alt-text-generic';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Alt Text is Generic or Missing';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if images have meaningful, descriptive alt text';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Image_Alt_Text_Generic' );
	}
}
