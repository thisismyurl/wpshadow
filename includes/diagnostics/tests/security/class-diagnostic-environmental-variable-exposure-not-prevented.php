<?php
/**
 * Environmental Variable Exposure Not Prevented Diagnostic
 *
 * Checks env variable exposure.
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
 * Diagnostic_Environmental_Variable_Exposure_Not_Prevented Class
 *
 * Performs diagnostic check for Environmental Variable Exposure Not Prevented.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Environmental_Variable_Exposure_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'environmental-variable-exposure-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Environmental Variable Exposure Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks env variable exposure';

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
		if ( ! has_filter( 'wp_headers', 'hide_environment_variables' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Environment variable protection is not fully configured. Hiding sensitive values in headers and output helps avoid accidental exposure.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
