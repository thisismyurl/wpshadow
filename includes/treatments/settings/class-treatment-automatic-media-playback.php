<?php
/**
 * Treatment: Automatic Media Playback
 *
 * Detects auto-playing videos/audio violating WCAG guidelines.
 * Auto-play media disrupts screen readers and user experience.
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
 * Automatic Media Playback Treatment Class
 *
 * Checks for autoplay attributes in media elements.
 *
 * Detection methods:
 * - Video autoplay detection
 * - Audio autoplay detection
 * - Iframe autoplay (YouTube, Vimeo)
 * - Background video checking
 *
 * @since 1.6093.1200
 */
class Treatment_Automatic_Media_Playback extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'automatic-media-playback';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Automatic Media Playback';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Auto-play videos/audio violate WCAG and disrupt user experience';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'readability';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (3 points):
	 * - 3 points: No autoplay media found
	 * - 0 points: Autoplay detected
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Automatic_Media_Playback' );
	}
}
