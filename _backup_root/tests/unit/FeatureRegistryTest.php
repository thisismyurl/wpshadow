<?php
/**
 * Tests for feature registry functionality
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test feature registry
 */
class FeatureRegistryTest extends TestCase {

	/**
	 * Test feature abstract class exists
	 */
	public function test_feature_abstract_exists(): void {
		$file = dirname( dirname( dirname( __FILE__ ) ) ) . '/includes/features/class-wps-feature-abstract.php';
		$this->assertFileExists( $file );
	}

	/**
	 * Test feature interface exists
	 */
	public function test_feature_interface_exists(): void {
		$file = dirname( dirname( dirname( __FILE__ ) ) ) . '/includes/features/interface-wps-feature.php';
		$this->assertFileExists( $file );
	}

	/**
	 * Test feature files follow naming convention
	 */
	public function test_feature_files_naming_convention(): void {
		$features_dir = dirname( dirname( dirname( __FILE__ ) ) ) . '/free/includes/features';
		$files        = scandir( $features_dir );

		$this->assertIsArray( $files );

		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file ) {
				continue;
			}

			// Feature files should either be class-wps-feature-*.php or interface-wps-feature.php
			$this->assertMatchesRegularExpression(
				'/^(class-wps-feature-|interface-wps-feature|class-wps-feature-abstract).*\.php$/i',
				$file,
				"File $file does not follow feature naming convention"
			);
		}
	}

	/**
	 * Test feature registration file exists
	 */
	public function test_feature_registry_file_exists(): void {
		$file = dirname( dirname( dirname( __FILE__ ) ) ) . '/free/includes/class-wps-feature-registry.php';
		$this->assertFileExists( $file );
	}

	/**
	 * Test feature detection file exists
	 */
	public function test_feature_detector_exists(): void {
		$file = dirname( dirname( dirname( __FILE__ ) ) ) . '/free/includes/class-wps-feature-detector.php';
		$this->assertFileExists( $file );
	}

	/**
	 * Test core features are defined
	 */
	public function test_core_features_defined(): void {
		$main_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/free/wpshadow.php';
		$content   = file_get_contents( $main_file );

		$this->assertStringContainsString( 'WPSHADOW_register_core_features', $content );
		$this->assertStringContainsString( 'register_WPSHADOW_feature', $content );
	}

	/**
	 * Test all feature files are properly required
	 */
	public function test_features_are_registered(): void {
		$main_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/wpshadow.php';
		$content   = file_get_contents( $main_file );

		// Should have multiple require statements for features
		preg_match_all( '/require_once.*feature.*\.php/i', $content, $matches );
		$this->assertGreaterThan( 10, count( $matches[0] ), 'Expected multiple feature requires' );
	}

	/**
	 * Test no duplicate feature requires
	 */
	public function test_no_duplicate_feature_requires(): void {
		$main_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/free/wpshadow.php';
		$content   = file_get_contents( $main_file );

		// Extract all require paths
		preg_match_all( '/require_once.*?\'(.*?)\'/s', $content, $matches );
		$paths = $matches[1] ?? array();

		// Check for duplicates
		$unique_paths = array_unique( $paths );
		$this->assertCount( count( $paths ), $unique_paths, 'Found duplicate requires in main plugin file' );
	}
}
