<?php
/**
 * X-Frame-Options Not Set Diagnostic
 *
 * Checks X-Frame-Options.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_X_Frame_Options_Not_Set Class
 *
 * Performs diagnostic check for X Frame Options Not Set.
 *
 * @since 1.6093.1200
 */
class Diagnostic_X_Frame_Options_Not_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'x-frame-options-not-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'X-Frame-Options Not Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks X-Frame-Options';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'set_x_frame_options' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'X-Frame-Options header not set. Set to DENY or SAMEORIGIN to prevent clickjacking attacks.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/x-frame-options-not-set',
			);
		}

		return null;
	}
}
