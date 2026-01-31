<?php
/**
 * Phpunit Test Isolation Diagnostic
 *
 * Phpunit Test Isolation issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1075.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Phpunit Test Isolation Diagnostic Class
 *
 * @since 1.1075.0000
 */
class Diagnostic_PhpunitTestIsolation extends Diagnostic_Base {

	protected static $slug = 'phpunit-test-isolation';
	protected static $title = 'Phpunit Test Isolation';
	protected static $description = 'Phpunit Test Isolation issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// PHPUnit only relevant in development/testing environments
		if ( wp_get_environment_type() === 'production' ) {
			return null;
		}
		
		// Check for PHPUnit configuration
		$phpunit_xml = ABSPATH . 'phpunit.xml';
		if ( ! file_exists( $phpunit_xml ) && ! file_exists( ABSPATH . 'phpunit.xml.dist' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Bootstrap file exists
		$bootstrap = ABSPATH . 'tests/bootstrap.php';
		if ( ! file_exists( $bootstrap ) ) {
			$issues[] = __( 'PHPUnit bootstrap file not found', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHPUnit test configuration incomplete', 'wpshadow' ),
				'severity'    => 60,
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/phpunit-test-isolation',
			);
		}
		
		// Check 2: Database reset configuration
		$bootstrap_content = file_get_contents( $bootstrap );
		if ( strpos( $bootstrap_content, 'WP_UnitTestCase' ) === false ) {
			$issues[] = __( 'Not using WP_UnitTestCase (no automatic database reset)', 'wpshadow' );
		}
		
		// Check 3: Global state backup
		if ( strpos( $bootstrap_content, '@backupGlobals' ) === false ) {
			$issues[] = __( 'Global state backup not configured (@backupGlobals)', 'wpshadow' );
		}
		
		// Check 4: Static attribute backup
		if ( strpos( $bootstrap_content, '@backupStaticAttributes' ) === false ) {
			$issues[] = __( 'Static attribute backup not configured', 'wpshadow' );
		}
		
		// Check 5: Test dependencies warning
		$test_dir = ABSPATH . 'tests';
		if ( is_dir( $test_dir ) ) {
			$test_files = glob( $test_dir . '/**/*Test.php' );
			$depends_usage = 0;
			
			foreach ( $test_files as $file ) {
				$content = file_get_contents( $file );
				if ( strpos( $content, '@depends' ) !== false ) {
					$depends_usage++;
				}
			}
			
			if ( $depends_usage > 5 ) {
				$issues[] = sprintf( __( '%d tests use @depends (isolation concerns)', 'wpshadow' ), $depends_usage );
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of isolation issues */
				__( 'PHPUnit test isolation has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/phpunit-test-isolation',
		);
	}
}
