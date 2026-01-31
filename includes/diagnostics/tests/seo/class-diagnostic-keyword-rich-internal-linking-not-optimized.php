<?php
/**
 * Keyword Rich Internal Linking Not Optimized Diagnostic
 *
 * Checks if internal linking is optimized.
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
 * Keyword Rich Internal Linking Not Optimized Diagnostic Class
 *
 * Detects missing internal linking optimization.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Keyword_Rich_Internal_Linking_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'keyword-rich-internal-linking-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keyword Rich Internal Linking Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if internal linking is optimized';

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
		// Check for internal linking plugins or optimization
		if ( ! is_plugin_active( 'linkwhisper/linkwhisper.php' ) && ! is_plugin_active( 'internal-links/internal-links.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Keyword-rich internal linking is not optimized. Implement strategic internal linking with keyword-rich anchor text for better SEO.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/keyword-rich-internal-linking-not-optimized',
			);
		}

		return null;
	}
}
