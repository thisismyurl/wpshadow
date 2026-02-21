<?php
/**
 * Media Picker Focus Management Treatment
 *
 * Tests focus trap and return in media picker modal.
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
 * Media Picker Focus Management Treatment Class
 *
 * Verifies that media picker modal properly manages focus,
 * including focus trapping within modal and focus return on close.
 *
 * @since 1.6033.0000
 */
class Treatment_Media_Picker_Focus_Management extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-picker-focus-management';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Picker Focus Management';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests focus trap and return in media picker modal';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Picker_Focus_Management' );
	}
}
