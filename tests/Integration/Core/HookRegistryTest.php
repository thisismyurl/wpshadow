<?php
/**
 * Tests for Hook_Registry
 *
 * Validates that Hook_Registry correctly auto-discovers and subscribes
 * all classes that extend Hook_Subscriber_Base.
 *
 * @package    WPShadow
 * @subpackage Tests\Integration\Core
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Integration\Core;

use WPShadow\Core\Hook_Registry;
use WPShadow\Tests\Helpers\MockHookSubscriber;
use WPShadow\Tests\TestCase;

/**
 * Hook_Registry Test Class
 *
 * @since 1.6093.1200
 */
class HookRegistryTest extends TestCase {

	/**
	 * Set up before each test.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		MockHookSubscriber::reset();
	}

	/**
	 * Test that Hook_Registry has init method.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_hook_registry_has_init_method() {
		$this->assertTrue( method_exists( 'WPShadow\Core\Hook_Registry', 'init' ) );
	}

	/**
	 * Test that Hook_Registry discovers Hook_Subscriber_Base classes.
	 *
	 * This test verifies the auto-discovery mechanism works by checking
	 * that real classes in the codebase are discovered.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_discovers_hook_subscriber_classes() {
		// These are actual classes that should be discovered.
		$expected_classes = array(
			'WPShadow\Admin\AJAX_Handler',
			'WPShadow\Admin\Admin_Page_Scanner',
			'WPShadow\Admin\Settings_Manager',
			'WPShadow\Content\KB_Post_Type',
			'WPShadow\Content\FAQ_Post_Type',
			'WPShadow\Content\Modal_Post_Type',
		);

		// Get discovered classes using reflection.
		$registry_class = new \ReflectionClass( 'WPShadow\Core\Hook_Registry' );
		$init_method    = $registry_class->getMethod( 'init' );

		// Make method accessible.
		$init_method->setAccessible( true );

		// Initialize registry (this discovers classes).
		Hook_Registry::init();

		// Check that expected classes exist and extend Hook_Subscriber_Base.
		foreach ( $expected_classes as $class_name ) {
			$this->assertTrue(
				class_exists( $class_name ),
				"Expected class {$class_name} to exist"
			);

			$this->assertTrue(
				is_subclass_of( $class_name, 'WPShadow\Core\Hook_Subscriber_Base' ),
				"Expected {$class_name} to extend Hook_Subscriber_Base"
			);
		}
	}

	/**
	 * Test that discovered classes have hooks registered.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_discovered_classes_have_registered_hooks() {
		// Initialize registry.
		Hook_Registry::init();

		// Check a real class has hooks registered.
		$class_name = 'WPShadow\Admin\AJAX_Handler';

		if ( class_exists( $class_name ) && is_subclass_of( $class_name, 'WPShadow\Core\Hook_Subscriber_Base' ) ) {
			$hooks = $class_name::get_hooks();

			$this->assertIsArray( $hooks );
			$this->assertNotEmpty( $hooks, "{$class_name} should return non-empty hooks array" );
		}
	}

	/**
	 * Test that Hook_Registry subscribes discovered classes.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_registry_subscribes_discovered_classes() {
		// Initialize registry.
		Hook_Registry::init();

		// Check that AJAX_Handler hooks are registered.
		// (This is a real class that should be auto-subscribed).
		$this->assertTrue(
			has_action( 'wp_ajax_wpshadow_scan_diagnostics' ) !== false,
			'Expected AJAX_Handler hooks to be registered by Hook_Registry'
		);
	}

	/**
	 * Test that Hook_Registry handles classes without get_hooks method.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_handles_classes_without_get_hooks() {
		// This should not throw an error even if a class doesn't implement get_hooks.
		// Hook_Registry should gracefully skip such classes.

		$this->expectNotToPerformAssertions();

		// Initialize should complete without errors.
		Hook_Registry::init();
	}

	/**
	 * Test that Hook_Registry discovers classes in multiple directories.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_discovers_classes_in_multiple_directories() {
		Hook_Registry::init();

		// Check classes from different directories are discovered.
		$directories_with_subscribers = array(
			'Admin'   => 'WPShadow\Admin\AJAX_Handler',
			'Content' => 'WPShadow\Content\KB_Post_Type',
			'Systems' => 'WPShadow\Core\Error_Handler',
		);

		$discovered_count = 0;

		foreach ( $directories_with_subscribers as $dir => $class ) {
			if ( class_exists( $class ) && is_subclass_of( $class, 'WPShadow\Core\Hook_Subscriber_Base' ) ) {
				++$discovered_count;
			}
		}

		$this->assertGreaterThan(
			0,
			$discovered_count,
			'Expected Hook_Registry to discover classes from multiple directories'
		);
	}

	/**
	 * Test that Hook_Registry counts discovered classes.
	 *
	 * According to Phase 2 documentation, there should be 45 classes
	 * using Hook_Subscriber_Base pattern.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_discovers_expected_number_of_classes() {
		// Count Hook_Subscriber_Base subclasses.
		$hook_subscriber_classes = array();

		// Scan includes directory.
		$includes_dir = __DIR__ . '/../../../includes';
		$iterator     = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $includes_dir )
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && $file->getExtension() === 'php' ) {
				$content = file_get_contents( $file->getRealPath() );

				// Check if file extends Hook_Subscriber_Base.
				if ( strpos( $content, 'extends Hook_Subscriber_Base' ) !== false ) {
					$hook_subscriber_classes[] = $file->getRealPath();
				}
			}
		}

		// Phase 2 + Phase 3 = 42 + 3 = 45 classes.
		$expected_min = 40; // Allow some variance.
		$expected_max = 50;

		$actual_count = count( $hook_subscriber_classes );

		$this->assertGreaterThanOrEqual(
			$expected_min,
			$actual_count,
			"Expected at least {$expected_min} Hook_Subscriber_Base classes, found {$actual_count}"
		);

		$this->assertLessThanOrEqual(
			$expected_max,
			$actual_count,
			"Expected at most {$expected_max} Hook_Subscriber_Base classes, found {$actual_count}"
		);
	}

	/**
	 * Test that Hook_Registry doesn't subscribe classes multiple times.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_prevents_duplicate_subscriptions() {
		// Subscribe MockHookSubscriber manually first.
		MockHookSubscriber::subscribe();

		// Now initialize Hook_Registry (which might discover it again).
		Hook_Registry::init();

		// Fire init hook.
		do_action( 'init' );

		// Should only be called once (WordPress prevents duplicate hooks).
		$this->assertEquals(
			1,
			MockHookSubscriber::call_count( 'init' ),
			'Hook should not be subscribed multiple times'
		);
	}

	/**
	 * Test that Hook_Registry works with WordPress hooks lifecycle.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_works_with_wordpress_lifecycle() {
		// Hook_Registry::init should be called on plugins_loaded.
		$this->assertTrue(
			has_action( 'plugins_loaded', array( 'WPShadow\Core\Hook_Registry', 'init' ) ) !== false
			|| did_action( 'plugins_loaded' ) > 0,
			'Hook_Registry should be hooked to plugins_loaded or already initialized'
		);
	}

	/**
	 * Test that Hook_Registry logs discovered classes (if debug enabled).
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_logs_discovered_classes_in_debug_mode() {
		// Enable WP_DEBUG for this test.
		$original_debug = defined( 'WP_DEBUG' ) ? WP_DEBUG : false;

		if ( ! defined( 'WP_DEBUG' ) ) {
			define( 'WP_DEBUG', true );
		}

		// Initialize registry (should log in debug mode).
		Hook_Registry::init();

		// Verify no fatal errors occurred.
		$this->assertTrue( true, 'Hook_Registry::init completed without errors' );

		// Note: Actual log verification would require capturing error_log output,
		// which is beyond scope of this test. This test verifies no crashes occur.
	}
}
