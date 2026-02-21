<?php
/**
 * Media Library Keyboard Navigation Treatment
 *
 * Tests complete keyboard navigation in media library.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Library Keyboard Navigation Treatment Class
 *
 * Verifies that media library supports complete keyboard navigation
 * including tab order, arrow keys, and keyboard shortcuts.
 *
 * @since 1.6033.0000
 */
class Treatment_Media_Library_Keyboard_Navigation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-library-keyboard-navigation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Library Keyboard Navigation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests complete keyboard navigation in media library';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Library_Keyboard_Navigation' );
	}
}
