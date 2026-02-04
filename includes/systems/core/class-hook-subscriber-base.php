<?php
/**
 * Hook Subscriber Base Class
 *
 * Abstract base class that enables convention-based hook registration.
 * Eliminates the need for manual add_action() and add_filter() calls.
 *
 * Usage:
 * 1. Extend this class
 * 2. Implement get_hooks() method
 * 3. Call YourClass::subscribe() once
 *
 * Philosophy:
 * - Commandment #7: Ridiculously Good (self-documenting hook subscriptions)
 * - Commandment #12: Expandable (easy for developers to extend)
 * - DRY: Zero repetitive add_action() calls
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      1.7035.1400
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook_Subscriber_Base Class
 *
 * Base class for all hook subscribers using convention-based registration.
 *
 * @since 1.7035.1400
 */
abstract class Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * Return an array of hook subscriptions in the format:
	 * [
	 *     'hook_name' => 'method_name',
	 *     'hook_name' => ['method_name', priority],
	 *     'hook_name' => ['method_name', priority, accepted_args],
	 * ]
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	abstract protected static function get_hooks(): array;

	/**
	 * Subscribe to all hooks defined in get_hooks().
	 *
	 * Automatically registers all actions and filters based on hook name patterns:
	 * - If hook name contains 'filter', uses add_filter()
	 * - Otherwise uses add_action()
	 *
	 * @since 1.7035.1400
	 * @return void
	 */
	public static function subscribe(): void {
		$hooks = static::get_hooks();

		foreach ( $hooks as $hook => $config ) {
			// Normalize config to array format
			if ( is_string( $config ) ) {
				// Simple format: 'hook' => 'method'
				$method   = $config;
				$priority = 10;
				$args     = 1;
			} elseif ( is_array( $config ) ) {
				// Extended format: 'hook' => ['method', priority, args]
				$method   = $config[0] ?? '';
				$priority = $config[1] ?? 10;
				$args     = $config[2] ?? 1;
			} else {
				continue; // Skip invalid config
			}

			// Validate method exists
			if ( ! method_exists( static::class, $method ) ) {
				continue;
			}

			// Determine if this is a filter or action based on hook name
			$is_filter = strpos( $hook, 'filter_' ) === 0 || strpos( $hook, '_filter' ) !== false;

			// Register the hook
			if ( $is_filter ) {
				add_filter( $hook, array( static::class, $method ), $priority, $args );
			} else {
				add_action( $hook, array( static::class, $method ), $priority, $args );
			}
		}
	}

	/**
	 * Convenience method for filters.
	 *
	 * Sugar syntax for registering filters specifically.
	 *
	 * @since  1.7035.1400
	 * @return array Filter subscriptions.
	 */
	protected static function get_filters(): array {
		return array();
	}

	/**
	 * Convenience method for actions.
	 *
	 * Sugar syntax for registering actions specifically.
	 *
	 * @since  1.7035.1400
	 * @return array Action subscriptions.
	 */
	protected static function get_actions(): array {
		return array();
	}

	/**
	 * Subscribe to filters only.
	 *
	 * @since 1.7035.1400
	 * @return void
	 */
	public static function subscribe_filters(): void {
		$filters = static::get_filters();

		foreach ( $filters as $hook => $config ) {
			// Normalize config
			if ( is_string( $config ) ) {
				$method   = $config;
				$priority = 10;
				$args     = 1;
			} elseif ( is_array( $config ) ) {
				$method   = $config[0] ?? '';
				$priority = $config[1] ?? 10;
				$args     = $config[2] ?? 1;
			} else {
				continue;
			}

			// Validate and register
			if ( method_exists( static::class, $method ) ) {
				add_filter( $hook, array( static::class, $method ), $priority, $args );
			}
		}
	}

	/**
	 * Subscribe to actions only.
	 *
	 * @since 1.7035.1400
	 * @return void
	 */
	public static function subscribe_actions(): void {
		$actions = static::get_actions();

		foreach ( $actions as $hook => $config ) {
			// Normalize config
			if ( is_string( $config ) ) {
				$method   = $config;
				$priority = 10;
				$args     = 1;
			} elseif ( is_array( $config ) ) {
				$method   = $config[0] ?? '';
				$priority = $config[1] ?? 10;
				$args     = $config[2] ?? 1;
			} else {
				continue;
			}

			// Validate and register
			if ( method_exists( static::class, $method ) ) {
				add_action( $hook, array( static::class, $method ), $priority, $args );
			}
		}
	}

	/**
	 * Unsubscribe from all hooks.
	 *
	 * Useful for testing or disabling functionality.
	 *
	 * @since 1.7035.1400
	 * @return void
	 */
	public static function unsubscribe(): void {
		$hooks = static::get_hooks();

		foreach ( $hooks as $hook => $config ) {
			if ( is_string( $config ) ) {
				$method   = $config;
				$priority = 10;
			} elseif ( is_array( $config ) ) {
				$method   = $config[0] ?? '';
				$priority = $config[1] ?? 10;
			} else {
				continue;
			}

			$is_filter = strpos( $hook, 'filter_' ) === 0 || strpos( $hook, '_filter' ) !== false;

			if ( $is_filter ) {
				remove_filter( $hook, array( static::class, $method ), $priority );
			} else {
				remove_action( $hook, array( static::class, $method ), $priority );
			}
		}
	}
}
