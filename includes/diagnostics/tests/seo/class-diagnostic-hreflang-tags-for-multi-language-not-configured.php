<?php
/**
 * Hreflang Tags For Multi-Language Not Configured Diagnostic
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
 * Hreflang Tags For Multi-Language Not Configured Diagnostic Class
 *
 * Detects missing hreflang tags.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Hreflang_Tags_For_Multi_Language_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hreflang-tags-for-multi-language-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hreflang Tags For Multi-Language Not Configured';

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
		// Check if multilingual plugin is active
		if ( is_multisite() && ! is_plugin_active( 'polylang/polylang.php' ) && ! is_plugin_active( 'wpml/sitepress.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Hreflang tags for multi-language are not configured. Add hreflang tags to tell search engines about language alternates.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/hreflang-tags-for-multi-language-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
