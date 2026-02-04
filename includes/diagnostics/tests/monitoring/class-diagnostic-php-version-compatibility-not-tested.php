<?php
/**
 * PHP Version Compatibility Not Tested Diagnostic
 *
 * Checks if PHP compatibility is tested.
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
 * PHP Version Compatibility Not Tested Diagnostic Class
 *
 * Detects untested PHP compatibility.
 *
 * @since 1.6030.2352
 */
class Diagnostic_PHP_Version_Compatibility_Not_Tested extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-version-compatibility-not-tested';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Version Compatibility Not Tested';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP compatibility is tested';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check PHP version test date
		if ( ! get_option( 'php_compatibility_test_date' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'PHP version compatibility is not tested. Test plugins and themes with your current and target PHP versions to ensure compatibility.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/php-version-compatibility-not-tested',
			);
		}

		return null;
	}
}
