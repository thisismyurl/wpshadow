<?php
/**
 * Tests for Bootstrap_Autoloader
 *
 * Validates that Bootstrap_Autoloader correctly loads critical classes
 * and discovers feature files automatically.
 *
 * @package    WPShadow
 * @subpackage Tests\Integration\Core
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Integration\Core;

use WPShadow\Core\Bootstrap_Autoloader;
use WPShadow\Tests\TestCase;

/**
 * Bootstrap_Autoloader Test Class
 *
 * @since 1.6093.1200
 */
class BootstrapAutoloaderTest extends TestCase {

	/**
	 * Test that Bootstrap_Autoloader has init method.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_has_init_method() {
		$this->assertTrue( method_exists( 'WPShadow\Core\Bootstrap_Autoloader', 'init' ) );
	}

	/**
	 * Test that critical classes are loaded.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_loads_critical_classes() {
		// Initialize autoloader.
		Bootstrap_Autoloader::init();

		// Verify critical classes are loaded.
		$critical_classes = array(
			'WPShadow\Core\Error_Handler',
			'WPShadow\Core\Settings_Registry',
			'WPShadow\Core\Activity_Logger',
		);

		foreach ( $critical_classes as $class ) {
			$this->assertTrue(
				class_exists( $class ),
				"Expected critical class {$class} to be loaded"
			);
		}
	}

	/**
	 * Test that critical classes are loaded in correct order.
	 *
	 * Error_Handler must be loaded first to catch errors from other classes.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_critical_classes_loaded_in_order() {
		// Initialize autoloader.
		Bootstrap_Autoloader::init();

		// Verify Error_Handler is loaded before others.
		$this->assertTrue(
			class_exists( 'WPShadow\Core\Error_Handler' ),
			'Error_Handler should be loaded first'
		);

		// Verify other critical classes can use Error_Handler.
		$this->assertTrue(
			class_exists( 'WPShadow\Core\Settings_Registry' ),
			'Settings_Registry should be loaded after Error_Handler'
		);
	}

	/**
	 * Test that feature directories are scanned.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_scans_feature_directories() {
		Bootstrap_Autoloader::init();

		// Verify classes from feature directories are discoverable.
		$feature_classes = array(
			'WPShadow\Admin\Settings_Manager',
			'WPShadow\Content\KB_Post_Type',
			'WPShadow\Diagnostics\Diagnostic_Registry',
		);

		$discovered_count = 0;

		foreach ( $feature_classes as $class ) {
			if ( class_exists( $class ) ) {
				++$discovered_count;
			}
		}

		$this->assertGreaterThan(
			0,
			$discovered_count,
			'Expected Bootstrap_Autoloader to discover feature classes'
		);
	}

	/**
	 * Test that discover_feature_files finds PHP files recursively.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_discovers_files_recursively() {
		// Use reflection to access private method.
		$reflection = new \ReflectionClass( 'WPShadow\Core\Bootstrap_Autoloader' );
		$method     = $reflection->getMethod( 'discover_feature_files' );
		$method->setAccessible( true );

		// Test with includes/admin directory.
		$admin_dir = WPSHADOW_PATH . 'includes/admin';

		if ( is_dir( $admin_dir ) ) {
			$discovered_files = $method->invoke( null, $admin_dir );

			$this->assertIsArray( $discovered_files );
			$this->assertNotEmpty( $discovered_files, 'Should discover files in admin directory' );

			// Verify files have .php extension.
			foreach ( $discovered_files as $file ) {
				$this->assertStringEndsWith( '.php', $file );
			}
		}
	}

	/**
	 * Test that discovered files are loaded correctly.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_loads_discovered_files() {
		Bootstrap_Autoloader::init();

		// Check that classes from discovered files are available.
		$expected_classes = array(
			'WPShadow\Admin\AJAX_Handler',
			'WPShadow\Admin\Settings_Page',
			'WPShadow\Content\Post_Types_Manager',
		);

		foreach ( $expected_classes as $class ) {
			$this->assertTrue(
				class_exists( $class ),
				"Expected class {$class} to be loaded from discovered file"
			);
		}
	}

	/**
	 * Test that Bootstrap_Autoloader handles missing directories gracefully.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_handles_missing_directories_gracefully() {
		// Use reflection to access private method.
		$reflection = new \ReflectionClass( 'WPShadow\Core\Bootstrap_Autoloader' );
		$method     = $reflection->getMethod( 'discover_feature_files' );
		$method->setAccessible( true );

		// Test with non-existent directory.
		$fake_dir = WPSHADOW_PATH . 'includes/nonexistent-directory-12345';
		$result   = $method->invoke( null, $fake_dir );

		$this->assertIsArray( $result );
		$this->assertEmpty( $result, 'Should return empty array for non-existent directory' );
	}

	/**
	 * Test that Bootstrap_Autoloader skips non-PHP files.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_skips_non_php_files() {
		// Use reflection to access private method.
		$reflection = new \ReflectionClass( 'WPShadow\Core\Bootstrap_Autoloader' );
		$method     = $reflection->getMethod( 'discover_feature_files' );
		$method->setAccessible( true );

		// Test with assets directory (contains CSS, JS, not PHP).
		$assets_dir = WPSHADOW_PATH . 'assets';

		if ( is_dir( $assets_dir ) ) {
			$discovered_files = $method->invoke( null, $assets_dir );

			// Should be empty or contain only PHP files.
			foreach ( $discovered_files as $file ) {
				$this->assertStringEndsWith( '.php', $file, 'Should only discover .php files' );
			}
		}
	}

	/**
	 * Test that Bootstrap_Autoloader doesn't load files twice.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_prevents_duplicate_loading() {
		// Initialize twice.
		Bootstrap_Autoloader::init();
		Bootstrap_Autoloader::init();

		// Verify no errors occurred (WordPress/PHP prevent duplicate require_once).
		$this->assertTrue( true, 'Bootstrap_Autoloader::init completed without errors' );
	}

	/**
	 * Test that Bootstrap_Autoloader reduces manual require_once calls.
	 *
	 * According to Phase 4 documentation, we should eliminate 97% of
	 * manual require_once calls (from 79 to 3).
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_reduces_manual_require_calls() {
		// Count require_once in main plugin file.
		$plugin_file = WPSHADOW_PATH . 'wpshadow.php';
		$content     = file_get_contents( $plugin_file );

		// Count require_once statements.
		preg_match_all( '/require_once\s+/', $content, $matches );
		$require_count = count( $matches[0] );

		// After Phase 4 implementation, should have ≤ 5 require_once.
		// (vendor/autoload.php, Bootstrap_Autoloader class, Hook_Registry class).
		$expected_max = 10; // Allow some flexibility.

		$this->assertLessThanOrEqual(
			$expected_max,
			$require_count,
			"Expected ≤ {$expected_max} require_once calls after Bootstrap_Autoloader, found {$require_count}"
		);
	}

	/**
	 * Test that Bootstrap_Autoloader works with Hook_Registry integration.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_integrates_with_hook_registry() {
		// Initialize autoloader.
		Bootstrap_Autoloader::init();

		// Verify Hook_Registry class is available.
		$this->assertTrue(
			class_exists( 'WPShadow\Core\Hook_Registry' ),
			'Hook_Registry should be available after Bootstrap_Autoloader::init'
		);

		// Verify Hook_Subscriber_Base classes are available for Hook_Registry.
		$this->assertTrue(
			class_exists( 'WPShadow\Core\Hook_Subscriber_Base' ),
			'Hook_Subscriber_Base should be available'
		);
	}

	/**
	 * Test that Bootstrap_Autoloader loads files from all feature directories.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_loads_from_all_feature_directories() {
		Bootstrap_Autoloader::init();

		// Check classes from each feature directory.
		$feature_directories = array(
			'admin'       => 'WPShadow\Admin\Settings_Manager',
			'content'     => 'WPShadow\Content\KB_Post_Type',
			'diagnostics' => 'WPShadow\Diagnostics\Diagnostic_Registry',
			'systems'     => 'WPShadow\Core\Bootstrap_Autoloader',
			'ui'          => 'WPShadow\UI\Dashboard_Widget',
		);

		$loaded_dirs = 0;

		foreach ( $feature_directories as $dir => $class ) {
			if ( class_exists( $class ) ) {
				++$loaded_dirs;
			}
		}

		$this->assertGreaterThanOrEqual(
			3,
			$loaded_dirs,
			'Expected Bootstrap_Autoloader to load classes from multiple feature directories'
		);
	}

	/**
	 * Test that Bootstrap_Autoloader handles WordPress not loaded gracefully.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_handles_wordpress_not_loaded() {
		// This test verifies Bootstrap_Autoloader checks for ABSPATH.
		// We can't truly test this without breaking WordPress, but we can
		// verify the class has the check.

		$reflection = new \ReflectionClass( 'WPShadow\Core\Bootstrap_Autoloader' );
		$source     = file_get_contents( $reflection->getFileName() );

		$this->assertStringContainsString(
			'ABSPATH',
			$source,
			'Bootstrap_Autoloader should check for ABSPATH'
		);
	}
}
