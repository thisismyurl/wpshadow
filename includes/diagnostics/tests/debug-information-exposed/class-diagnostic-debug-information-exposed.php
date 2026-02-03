<?php
/**
 * Debug Information Exposed Diagnostic
 *
 * Checks debug exposure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Debug_Information_Exposed Class
 *
 * Performs diagnostic check for Debug Information Exposed.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Debug_Information_Exposed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'debug-information-exposed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Debug Information Exposed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks debug exposure';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   defined('WP_DEBUG' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Debug information exposed in production. Set WP_DEBUG to false and log errors to file,
						'severity'   =>   'high',
						'threat_level'   =>   70,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/debug-information-exposed'
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
