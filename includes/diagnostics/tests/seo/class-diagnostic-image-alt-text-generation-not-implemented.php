<?php
/**
 * Image Alt Text Generation Not Implemented Diagnostic
 *
 * Checks if auto alt text generation is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Alt Text Generation Not Implemented Diagnostic Class
 *
 * Detects missing auto alt text.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Image_Alt_Text_Generation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-alt-text-generation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Alt Text Generation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if auto alt text generation is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if alt text generation is enabled
		if ( ! has_filter( 'wp_get_attachment_metadata', 'generate_alt_text' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Auto alt text generation is not implemented. Use AI or manual processes to auto-generate descriptive alt text for images without them.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-alt-text-generation-not-implemented',
			);
		}

		return null;
	}
}
