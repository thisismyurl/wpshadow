<?php
/**
 * Submenu Icons Not Implemented Diagnostic
 *
 * Checks if submenu icons are implemented.
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
 * Submenu Icons Not Implemented Diagnostic Class
 *
 * Detects missing submenu icons.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Submenu_Icons_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'submenu-icons-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Submenu Icons Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if submenu icons are implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for submenu icon implementation
		if ( ! has_action( 'admin_menu', 'wp_add_submenu_icons' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Submenu icons are not implemented. Add visual icons to submenu items to improve admin navigation and usability.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/submenu-icons-not-implemented',
			);
		}

		return null;
	}
}
