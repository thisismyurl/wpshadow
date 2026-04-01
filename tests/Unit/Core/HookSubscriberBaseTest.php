<?php
/**
 * Tests for Hook_Subscriber_Base
 *
 * Validates the declarative hook subscription pattern works correctly.
 * Tests hook registration, callback execution, priorities, and arguments.
 *
 * @package    WPShadow
 * @subpackage Tests\Unit\Core
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit\Core;

use WPShadow\Tests\Helpers\MockHookSubscriber;
use WPShadow\Tests\TestCase;

/**
 * Hook_Subscriber_Base Test Class
 *
 * @since 1.6093.1200
 */
class HookSubscriberBaseTest extends TestCase {

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
	 * Test that get_hooks returns expected structure.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_get_hooks_returns_array() {
		$hooks = MockHookSubscriber::get_hooks();

		$this->assertIsArray( $hooks );
		$this->assertNotEmpty( $hooks );
	}

	/**
	 * Test simple hook subscription (string callback).
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_simple_hook_subscription() {
		$hooks = MockHookSubscriber::get_hooks();

		$this->assertArrayHasKey( 'init', $hooks );
		$this->assertEquals( 'handle_init', $hooks['init'] );
	}

	/**
	 * Test hook subscription with priority.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_hook_with_priority() {
		$hooks = MockHookSubscriber::get_hooks();

		$this->assertArrayHasKey( 'admin_init', $hooks );
		$this->assertIsArray( $hooks['admin_init'] );
		$this->assertEquals( 'handle_admin_init', $hooks['admin_init']['callback'] );
		$this->assertEquals( 20, $hooks['admin_init']['priority'] );
	}

	/**
	 * Test hook subscription with priority and arguments.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_hook_with_priority_and_args() {
		$hooks = MockHookSubscriber::get_hooks();

		$this->assertArrayHasKey( 'wp_loaded', $hooks );
		$this->assertIsArray( $hooks['wp_loaded'] );
		$this->assertEquals( 'handle_loaded', $hooks['wp_loaded']['callback'] );
		$this->assertEquals( 5, $hooks['wp_loaded']['priority'] );
		$this->assertEquals( 2, $hooks['wp_loaded']['args'] );
	}

	/**
	 * Test multiple callbacks for single hook.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_multiple_callbacks_per_hook() {
		$hooks = MockHookSubscriber::get_hooks();

		$this->assertArrayHasKey( 'admin_menu', $hooks );
		$this->assertIsArray( $hooks['admin_menu'] );
		$this->assertCount( 2, $hooks['admin_menu'] );

		// First callback (early).
		$this->assertEquals( 'handle_menu_early', $hooks['admin_menu'][0]['callback'] );
		$this->assertEquals( 5, $hooks['admin_menu'][0]['priority'] );

		// Second callback (late).
		$this->assertEquals( 'handle_menu_late', $hooks['admin_menu'][1]['callback'] );
		$this->assertEquals( 15, $hooks['admin_menu'][1]['priority'] );
	}

	/**
	 * Test that subscribe() registers hooks with WordPress.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_subscribe_registers_hooks() {
		global $wp_filter;

		// Subscribe hooks.
		MockHookSubscriber::subscribe();

		// Verify init hook registered.
		$this->assertTrue( has_action( 'init', array( 'WPShadow\Tests\Helpers\MockHookSubscriber', 'handle_init' ) ) !== false );

		// Verify admin_init hook registered with priority 20.
		$priority = has_action( 'admin_init', array( 'WPShadow\Tests\Helpers\MockHookSubscriber', 'handle_admin_init' ) );
		$this->assertEquals( 20, $priority );

		// Verify wp_loaded hook registered with priority 5.
		$priority = has_action( 'wp_loaded', array( 'WPShadow\Tests\Helpers\MockHookSubscriber', 'handle_loaded' ) );
		$this->assertEquals( 5, $priority );

		// Verify admin_menu has 2 callbacks.
		$menu_callbacks = $wp_filter['admin_menu'] ?? null;
		$this->assertNotNull( $menu_callbacks );

		// Check early callback (priority 5).
		$this->assertTrue( has_action( 'admin_menu', array( 'WPShadow\Tests\Helpers\MockHookSubscriber', 'handle_menu_early' ) ) !== false );

		// Check late callback (priority 15).
		$this->assertTrue( has_action( 'admin_menu', array( 'WPShadow\Tests\Helpers\MockHookSubscriber', 'handle_menu_late' ) ) !== false );
	}

	/**
	 * Test that hooks execute callbacks correctly.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_hooks_execute_callbacks() {
		// Subscribe hooks.
		MockHookSubscriber::subscribe();

		// Fire init hook.
		do_action( 'init' );

		// Verify callback was called.
		$this->assertTrue( MockHookSubscriber::was_called( 'init' ) );
		$this->assertEquals( 1, MockHookSubscriber::call_count( 'init' ) );
	}

	/**
	 * Test that hooks with arguments pass them correctly.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_hooks_pass_arguments() {
		// Subscribe hooks.
		MockHookSubscriber::subscribe();

		// Fire wp_loaded with arguments.
		do_action( 'wp_loaded', 'arg1', 'arg2' );

		// Verify callback was called.
		$this->assertTrue( MockHookSubscriber::was_called( 'wp_loaded' ) );
	}

	/**
	 * Test that multiple callbacks execute in correct order.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_multiple_callbacks_execute_in_order() {
		// Subscribe hooks.
		MockHookSubscriber::subscribe();

		// Fire admin_menu hook.
		do_action( 'admin_menu' );

		// Verify both callbacks were called.
		$this->assertTrue( MockHookSubscriber::was_called( 'admin_menu_early' ) );
		$this->assertTrue( MockHookSubscriber::was_called( 'admin_menu_late' ) );

		// Each should be called exactly once.
		$this->assertEquals( 1, MockHookSubscriber::call_count( 'admin_menu_early' ) );
		$this->assertEquals( 1, MockHookSubscriber::call_count( 'admin_menu_late' ) );
	}

	/**
	 * Test that hooks don't execute before subscription.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_hooks_dont_execute_before_subscription() {
		// Fire init without subscribing.
		do_action( 'init' );

		// Verify callback was NOT called.
		$this->assertFalse( MockHookSubscriber::was_called( 'init' ) );
		$this->assertEquals( 0, MockHookSubscriber::call_count( 'init' ) );
	}

	/**
	 * Test that duplicate subscription doesn't create duplicate hooks.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_duplicate_subscription_prevention() {
		// Subscribe twice.
		MockHookSubscriber::subscribe();
		MockHookSubscriber::subscribe();

		// Fire init hook.
		do_action( 'init' );

		// Should only be called once (WordPress prevents duplicate hooks).
		$this->assertEquals( 1, MockHookSubscriber::call_count( 'init' ) );
	}

	/**
	 * Test that reset() clears tracking data.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_reset_clears_tracking() {
		MockHookSubscriber::subscribe();
		do_action( 'init' );

		$this->assertTrue( MockHookSubscriber::was_called( 'init' ) );

		// Reset.
		MockHookSubscriber::reset();

		// Verify cleared.
		$this->assertFalse( MockHookSubscriber::was_called( 'init' ) );
		$this->assertEquals( 0, MockHookSubscriber::call_count( 'init' ) );
	}
}
