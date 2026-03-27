<?php
/**
 * Mobile Font Size - Body Text
 *
 * Validates minimum font size for body text on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Treatments\Typography
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Font Size - Body Text
 *
 * Ensures body text on mobile is at least 16px to prevent iOS auto-zoom on form focus
 * and to maintain readability without pinch-zoom.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Font_Size extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-font-size-too-small';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Font Size - Body Text';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures body text is at least 16px on mobile devices';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'typography';

	/**
	 * Minimum font size for body text (prevents iOS auto-zoom).
	 *
	 * @var int
	 */
	const MIN_FONT_SIZE = 16;

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Font_Size' );
	}

	/**
	 * Get theme stylesheet content.
	 *
	 * @since 1.6093.1200
	 * @return string|null CSS content.
	 */
	private static function get_stylesheet_content(): ?string {
		$stylesheet = get_template_directory() . '/style.css';

		if ( file_exists( $stylesheet ) ) {
			return file_get_contents( $stylesheet );
		}

		return null;
	}
}
