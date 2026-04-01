<?php
/**
 * Hreflang Tags Not Configured Diagnostic
 *
 * Checks if hreflang tags are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hreflang Tags Not Configured Diagnostic Class
 *
 * Detects missing hreflang tags.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Hreflang_Tags_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hreflang-tags-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hreflang Tags Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if hreflang tags are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for hreflang tag implementation
		if ( ! has_filter( 'wp_head', 'add_hreflang_tags' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Hreflang tags are not configured. Add hreflang tags to multi-language or multi-region pages to tell search engines about alternate versions and prevent duplicate content issues.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/hreflang-tags-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
