<?php
/**
 * Audio Player Functionality Treatment
 *
 * Tests HTML5 audio player and WordPress audio shortcode functionality.
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
 * Audio Player Functionality Treatment Class
 *
 * Validates that HTML5 audio player works correctly with proper controls,
 * and WordPress audio shortcode renders with correct attributes.
 *
 * @since 1.6093.1200
 */
class Treatment_Audio_Player_Functionality extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'audio-player-functionality';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Audio Player Functionality';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates HTML5 audio player and WordPress audio shortcode functionality';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if WordPress audio player is properly configured and
	 * audio shortcodes render with correct controls.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Audio_Player_Functionality' );
	}
}
