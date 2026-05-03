<?php
/**
 * Abstract Registry Base Class
 *
 * Base class for registry pattern implementations to eliminate duplication.
 *
 * @package ThisIsMyURL\Shadow
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Core;

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
	 * @return string Namespace prefix (e.g., 'ThisIsMyURL\Shadow\Diagnostics\').
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
	 * Get count of registered items.
	 *
	 * @return int Number of registered items.
	 */
	public static function count() {
		return count( static::get_registered_items() );
	}
}
