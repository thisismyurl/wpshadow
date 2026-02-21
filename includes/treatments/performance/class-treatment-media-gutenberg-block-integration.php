<?php
/**
 * Media Gutenberg Block Integration Treatment
 *
 * Checks if media blocks in Gutenberg editor work properly.
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
 * Media Gutenberg Block Integration Treatment Class
 *
 * Verifies that Gutenberg media blocks (Image, Gallery, Video, Audio)
 * are properly registered and functional with media library integration.
 *
 * @since 1.6033.0000
 */
class Treatment_Media_Gutenberg_Block_Integration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-gutenberg-block-integration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Gutenberg Block Integration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media blocks in Gutenberg editor work properly';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Gutenberg_Block_Integration' );
	}
}
