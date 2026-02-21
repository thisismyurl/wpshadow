<?php
/**
 * Mobile Video Playback Treatment
 *
 * Ensures videos play inline on mobile.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Video Playback Treatment Class
 *
 * Ensures videos play inline on mobile with proper controls and attributes,
 * preventing full-screen-only playback that disrupts content flow.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Video_Playback extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-video-playback';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Video Playback';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure videos play inline on mobile with proper controls';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Video_Playback' );
	}
}
