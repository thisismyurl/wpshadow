<?php
/**
 * Abstract Registry Base Class
 *
 * Base class for registry pattern implementations to eliminate duplication.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Registry Base Class
 *
 * Provides common registry functionality for managing collections
 * of diagnostic, treatment, or other plugin components.
 */
abstract class Abstract_Registry {
	/**
	 * Get the list of registered items.
	 *
	 * Must be implemented by child classes to return array of class names.
	 *
	 * @return array Array of class names.
	 */
	abstract protected static function get_registered_items();

	/**
	 * Get the namespace for registered items.
	 *
	 * Must be implemented by child classes.
	 *
	 * @return string Namespace prefix (e.g., 'WPShadow\Diagnostics\').
	 */
	abstract protected static function get_namespace();

	/**
	 * Get all registered items with full namespace.
	 *
	 * @return array Array of fully-qualified class names.
	 */
	public static function get_all() {
		$items     = static::get_registered_items();
		$namespace = static::get_namespace();
		$qualified = array();

		foreach ( $items as $item ) {
			$qualified[] = $namespace . $item;
		}

		return $qualified;
	}

	/**
	 * Check if an item is registered.
	 *
	 * @param string $class_name The class name (with or without namespace).
	 * @return bool True if registered.
	 */
	public static function is_registered( $class_name ) {
		$items     = static::get_registered_items();
		$namespace = static::get_namespace();
		
		// Strip namespace if provided
		$class_name = str_replace( $namespace, '', $class_name );
		
		return in_array( $class_name, $items, true );
	}

	/**
	 * Get count of registered items.
	 *
	 * @return int Number of registered items.
	 */
	public static function count() {
		return count( static::get_registered_items() );
	}
}
