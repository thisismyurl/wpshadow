<?php
/**
 * Short-Form Video Strategy Treatment
 *
 * Tests whether the site has an active strategy for YouTube Shorts, Instagram Reels, and TikToks.
 *
 * @since   1.6034.0410
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Short-Form Video Strategy Treatment Class
 *
 * Short-form videos generate 2.5x more engagement than traditional content.
 * Shorts, Reels, and TikToks are essential for discoverability in 2026.
 *
 * @since 1.6034.0410
 */
class Treatment_Short_Form_Video_Strategy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'short-form-video-strategy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Short-Form Video Strategy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site has an active strategy for YouTube Shorts, Instagram Reels, and TikToks';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'video-marketing';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6034.0410
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Short_Form_Video_Strategy' );
	}
}
