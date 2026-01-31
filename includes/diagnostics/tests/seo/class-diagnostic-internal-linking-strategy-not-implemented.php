<?php
/**
 * Internal Linking Strategy Not Implemented Diagnostic
 *
 * Checks if internal linking is configured.
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
 * Internal Linking Strategy Not Implemented Diagnostic Class
 *
 * Detects missing internal linking strategy.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Internal_Linking_Strategy_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'internal-linking-strategy-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Internal Linking Strategy Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if internal linking is configured';

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
		// Check for internal linking plugin
		if ( ! is_plugin_active( 'internal-links/internal-links.php' ) && ! has_filter( 'the_content', 'add_internal_links' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Internal linking strategy is not implemented. Create a strategic internal linking structure to distribute page authority and improve crawlability.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/internal-linking-strategy-not-implemented',
			);
		}

		return null;
	}
}
