<?php
/**
 * CCPA Right to Opt-Out Not Implemented Diagnostic
 *
 * Checks if CCPA opt-out mechanisms are available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CCPA Right to Opt-Out Not Implemented Diagnostic Class
 *
 * Detects missing CCPA opt-out implementation.
 *
 * @since 1.2601.2310
 */
class Diagnostic_CCPA_Right_To_Opt_Out_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ccpa-right-to-opt-out-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CCPA Right to Opt-Out Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CCPA opt-out is available';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for CCPA plugins
		$ccpa_plugins = array(
			'cookie-law-info/cookie-law-info.php',
			'iubenda-cookie-solution/iubenda-cookie-solution.php',
			'borlabs-cookie/borlabs-cookie.php',
			'ccpa-compliance/ccpa-compliance.php',
		);

		$ccpa_plugin_active = false;
		foreach ( $ccpa_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$ccpa_plugin_active = true;
				break;
			}
		}

		if ( ! $ccpa_plugin_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CCPA opt-out mechanism is not implemented. If serving California residents, you must provide opt-out functionality.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/ccpa-right-to-opt-out-not-implemented',
			);
		}

		return null;
	}
}
