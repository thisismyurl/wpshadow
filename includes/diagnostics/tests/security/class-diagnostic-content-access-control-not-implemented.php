<?php
/**
 * Content Access Control Not Implemented Diagnostic
 *
 * Checks if content access control is implemented.
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
 * Content Access Control Not Implemented Diagnostic Class
 *
 * Detects missing access control.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Content_Access_Control_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-access-control-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Access Control Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if content access control is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for membership/content access plugin
		if ( ! is_plugin_active( 'memberpress/memberpress.php' ) && ! is_plugin_active( 'restrict-content-pro/restrict-content-pro.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content access control is not implemented. Use membership or content restriction plugins to gate premium content by user roles.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-access-control-not-implemented',
			);
		}

		return null;
	}
}
