<?php
/**
 * Rich Snippets Markup For Events Not Configured Diagnostic
 *
 * Checks if event structured data is configured.
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
 * Rich Snippets Markup For Events Not Configured Diagnostic Class
 *
 * Detects missing event structured data.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Rich_Snippets_Markup_For_Events_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rich-snippets-markup-for-events-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Rich Snippets Markup For Events Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if event structured data is configured';

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
		// Check for event post type
		if ( ! post_type_exists( 'event' ) && ! is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Event structured data is not configured. Add Event schema markup for better search visibility if you host events.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rich-snippets-markup-for-events-not-configured',
			);
		}

		return null;
	}
}
