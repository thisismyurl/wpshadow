<?php
/**
 * Mock Hook Subscriber for Testing
 *
 * A simple mock class that extends Hook_Subscriber_Base for testing purposes.
 * Used to verify that the hook subscription pattern works correctly.
 *
 * @package    WPShadow
 * @subpackage Tests\Helpers
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Helpers;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mock Hook Subscriber Class
 *
 * Provides a minimal implementation of Hook_Subscriber_Base for testing.
 * Tracks hook subscriptions and callback execution.
 *
 * @since 1.6093.1200
 */
class MockHookSubscriber extends Hook_Subscriber_Base {

	/**
	 * Track whether hooks have been called
	 *
	 * @var array
	 */
	public static $called_hooks = array();

	/**
	 * Track number of times each hook was called
	 *
	 * @var array
	 */
	public static $call_counts = array();

	/**
	 * Get the hooks this class subscribes to.
	 *
	 * @since 1.6093.1200
	 * @return array Array of hooks with callbacks and priorities.
	 */
	public static function get_hooks() {
		return array(
			'init'       => 'handle_init',
			'admin_init' => array(
				'callback' => 'handle_admin_init',
				'priority' => 20,
			),
			'wp_loaded'  => array(
				'callback' => 'handle_loaded',
				'priority' => 5,
				'args'     => 2,
			),
			'admin_menu' => array(
				array(
					'callback' => 'handle_menu_early',
					'priority' => 5,
				),
				array(
					'callback' => 'handle_menu_late',
					'priority' => 15,
				),
			),
		);
	}

	/**
	 * Handle init hook.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle_init() {
		self::track_call( 'init' );
	}

	/**
	 * Handle admin_init hook.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle_admin_init() {
		self::track_call( 'admin_init' );
	}

	/**
	 * Handle wp_loaded hook.
	 *
	 * @since 1.6093.1200
	 * @param  mixed $arg1 First argument.
	 * @param  mixed $arg2 Second argument.
	 * @return void
	 */
	public static function handle_loaded( $arg1 = null, $arg2 = null ) {
		self::track_call( 'wp_loaded', array( $arg1, $arg2 ) );
	}

	/**
	 * Handle admin_menu hook (early).
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle_menu_early() {
		self::track_call( 'admin_menu_early' );
	}

	/**
	 * Handle admin_menu hook (late).
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle_menu_late() {
		self::track_call( 'admin_menu_late' );
	}

	/**
	 * Track that a callback was called.
	 *
	 * @since 1.6093.1200
	 * @param  string $hook Hook name.
	 * @param  array  $args Optional. Arguments passed to callback.
	 * @return void
	 */
	private static function track_call( $hook, $args = array() ) {
		if ( ! isset( self::$called_hooks[ $hook ] ) ) {
			self::$called_hooks[ $hook ] = array();
			self::$call_counts[ $hook ]  = 0;
		}

		self::$called_hooks[ $hook ][] = $args;
		++self::$call_counts[ $hook ];
	}

	/**
	 * Check if a hook was called.
	 *
	 * @since 1.6093.1200
	 * @param  string $hook Hook name.
	 * @return bool True if called, false otherwise.
	 */
	public static function was_called( $hook ) {
		return isset( self::$called_hooks[ $hook ] ) && self::$call_counts[ $hook ] > 0;
	}

	/**
	 * Get number of times a hook was called.
	 *
	 * @since 1.6093.1200
	 * @param  string $hook Hook name.
	 * @return int Call count.
	 */
	public static function call_count( $hook ) {
		return self::$call_counts[ $hook ] ?? 0;
	}

	/**
	 * Reset tracking data.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function reset() {
		self::$called_hooks = array();
		self::$call_counts  = array();
	}
}
