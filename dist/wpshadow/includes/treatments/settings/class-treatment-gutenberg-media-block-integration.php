<?php
/**
 * Gutenberg Media Block Integration Treatment
 *
 * Detects if Gutenberg block editor media blocks are properly integrated.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Gutenberg_Media_Block_Integration Class
 *
 * Tests if Gutenberg media blocks (image, video, audio, file) are properly
 * registered and integrated with the block editor for optimal media management.
 *
 * @since 0.6093.1200
 */
class Treatment_Gutenberg_Media_Block_Integration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'gutenberg-media-block-integration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Gutenberg Media Block Integration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Gutenberg media blocks are properly integrated';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Gutenberg_Media_Block_Integration' );
	}
}
