<?php
/**
 * Rich Snippet Not Configured Diagnostic
 *
 * Checks if rich snippets are configured.
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
 * Rich Snippet Not Configured Diagnostic Class
 *
 * Detects missing rich snippets.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Rich_Snippet_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rich-snippet-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Rich Snippet Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if rich snippets are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for structured data plugin
		if ( ! is_plugin_active( 'all-in-one-schema-rich-snippets/index.php' ) && ! has_filter( 'wp_head', 'output_structured_data' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Rich snippet schema is not configured. Add structured data (FAQ, Product, Event) for enhanced search result appearance.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rich-snippet-not-configured',
			);
		}

		return null;
	}
}
