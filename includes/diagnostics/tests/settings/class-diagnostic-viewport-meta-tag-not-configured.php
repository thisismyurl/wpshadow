<?php
/**
 * Viewport Meta Tag Not Configured Diagnostic
 *
 * Checks if viewport meta tag is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Viewport Meta Tag Not Configured Diagnostic Class
 *
 * Detects missing viewport meta tag.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Viewport_Meta_Tag_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'viewport-meta-tag-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Viewport Meta Tag Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if viewport meta tag is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for viewport meta tag in header
		if ( ! has_filter( 'wp_head', 'add_viewport_meta_tag' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Viewport meta tag is not configured. Add <meta name="viewport" content="width=device-width, initial-scale=1"> to enable responsive design on mobile devices.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 50,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/viewport-meta-tag-not-configured',
			);
		}

		return null;
	}
}
