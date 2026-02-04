<?php
/**
 * Progressive Image Loading Not Implemented Diagnostic
 *
 * Checks progressive image loading.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Progressive_Image_Loading_Not_Implemented Class
 *
 * Performs diagnostic check for Progressive Image Loading Not Implemented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Progressive_Image_Loading_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'progressive-image-loading-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Progressive Image Loading Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks progressive image loading';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('wp_head',
						'enable_progressive_images' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Progressive image loading not implemented. Load low-quality image placeholder while high-quality version loads for improved perceived performance.',
						'severity'   =>   'low',
						'threat_level'   =>   15,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/progressive-image-loading-not-implemented'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
