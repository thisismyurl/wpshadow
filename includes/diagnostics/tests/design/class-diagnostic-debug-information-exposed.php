<?php
/**
 * Debug Information Exposed Diagnostic
 *
 * Checks debug exposure.
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
 * Diagnostic_Debug_Information_Exposed Class
 *
 * Performs diagnostic check for Debug Information Exposed.
 *
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Debug information may be visible in production. Turn off WP_DEBUG on live sites and log errors privately.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/debug-information-exposed?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
