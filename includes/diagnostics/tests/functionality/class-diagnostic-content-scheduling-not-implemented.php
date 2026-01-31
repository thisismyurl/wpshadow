<?php
/**
 * Content Scheduling Not Implemented Diagnostic
 *
 * Checks if content scheduling is implemented.
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
 * Content Scheduling Not Implemented Diagnostic Class
 *
 * Detects missing content scheduling.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Content_Scheduling_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-scheduling-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Scheduling Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if content scheduling is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for editorial calendar or scheduling capability
		if ( ! is_plugin_active( 'editorial-calendar/editorial-calendar.php' ) && ! current_theme_supports( 'post-scheduling' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content scheduling is not implemented. Use WordPress native scheduling or Editorial Calendar plugin for better content planning.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-scheduling-not-implemented',
			);
		}

		return null;
	}
}
