<?php
/**
 * jQuery Deprecation Not Addressed Diagnostic
 *
 * Checks if jQuery deprecation is addressed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * jQuery Deprecation Not Addressed Diagnostic Class
 *
 * Detects unaddressed jQuery deprecation.
 *
 * @since 1.6030.2352
 */
class Diagnostic_jQuery_Deprecation_Not_Addressed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'jquery-deprecation-not-addressed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'jQuery Deprecation Not Addressed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if jQuery deprecation is addressed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if deprecation warnings are handled
		global $wp_scripts;
		if ( $wp_scripts && $wp_scripts->query( 'jquery' ) && ! get_option( 'jquery_migration_plan' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'jQuery deprecation is not addressed. Plan to migrate away from jQuery to vanilla JavaScript or modern frameworks. Update deprecated jQuery methods and remove unused jQuery plugins.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/jquery-deprecation-not-addressed',
			);
		}

		return null;
	}
}
