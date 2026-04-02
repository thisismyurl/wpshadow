<?php
/**
 * Test Automation Framework Not Implemented Diagnostic
 *
 * Checks if test automation is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test Automation Framework Not Implemented Diagnostic Class
 *
 * Detects missing test automation.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Test_Automation_Framework_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'test-automation-framework-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Test Automation Framework Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if test automation is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for PHPUnit configuration
		if ( ! file_exists( ABSPATH . 'phpunit.xml' ) && ! file_exists( ABSPATH . 'phpunit.xml.dist' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Test automation is not implemented. Set up PHPUnit for WordPress plugin testing to ensure code quality and catch regressions early.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/test-automation-framework-not-implemented',
			);
		}

		return null;
	}
}
