<?php
/**
 * CPT Base Class
 *
 * Abstract base class for custom post type declarations.
 * Provides a declarative pattern for CPT registration.
 *
 * @package    WPShadow
 * @subpackage Core
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_Base Class
 *
 * Abstract base for custom post types. Child classes declare their
 * CPT configuration via get_post_type_config() and taxonomies via
 * get_taxonomies_config().
 *
 * PATTERN:
 * ```php
 * class My_CPT extends CPT_Base {
 *     protected static function get_post_type_config(): array {
 *         return array(
 *             'slug'   => 'my_cpt',
 *             'labels' => array( ... ),
 *             'args'   => array( ... ),
 *         );
 *     }
 *
 *     protected static function get_taxonomies_config(): array {
 *         return array(
 *             'my_taxonomy' => array(
 *                 'labels' => array( ... ),
 *                 'args'   => array( ... ),
 *             ),
 *         );
 *     }
 * }
 * ```
 *
 * @since 0.6093.1200
 */
abstract class CPT_Base extends Hook_Subscriber_Base {

	/**
	 * Get post type configuration.
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Post type configuration.
	 *
	 *     @type string $slug   Post type slug.
	 *     @type array  $labels Post type labels.
	 *     @type array  $args   register_post_type() arguments.
	 * }
	 */
	abstract protected static function get_post_type_config(): array;

	/**
	 * Get taxonomies configuration (optional).
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Taxonomies configuration keyed by taxonomy slug.
	 *
	 *     @type array $taxonomy {
	 *         @type array $labels Taxonomy labels.
	 *         @type array $args   register_taxonomy() arguments.
	 *     }
	 * }
	 */
	protected static function get_taxonomies_config(): array {
		return array();
	}

	/**
	 * Get meta fields configuration (optional).
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Meta fields configuration.
	 *
	 *     @type array $field {
	 *         @type string $key  Meta key.
	 *         @type array  $args register_post_meta() or register_meta() arguments.
	 *     }
	 * }
	 */
	protected static function get_meta_config(): array {
		return array();
	}

	/**
	 * Get REST API fields configuration (optional).
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     REST fields configuration.
	 *
	 *     @type array $field {
	 *         @type string   $attribute  REST field name.
	 *         @type callable $get        Getter callback.
	 *         @type callable $update     Optional. Update callback.
	 *         @type array    $schema     Optional. Schema definition.
	 *     }
	 * }
	 */
	protected static function get_rest_fields_config(): array {
		return array();
	}

	/**
	 * Get hooks to subscribe to.
	 *
	 * This base implementation registers CPT, taxonomies, meta, and REST fields.
	 * Child classes can override to add additional hooks.
	 *
	 * @since 0.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		$hooks = array(
			'init' => 'register_all',
		);

		// Add REST API registration if REST fields are configured.
		if ( ! empty( static::get_rest_fields_config() ) ) {
			$hooks['rest_api_init'] = 'register_rest_fields';
		}

		return $hooks;
	}

	/**
	 * Register all CPT components (post type, taxonomies, meta).
	 *
	 * Called on 'init' hook.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register_all(): void {
		static::register_post_type();
		static::register_taxonomies();
		static::register_meta();
	}

	/**
	 * Register the post type.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register_post_type(): void {
		$config = static::get_post_type_config();

		if ( empty( $config['slug'] ) ) {
			return;
		}

		$slug   = $config['slug'];
		$labels = $config['labels'] ?? array();
		$args   = $config['args'] ?? array();

		// Merge labels into args.
		if ( ! empty( $labels ) ) {
			$args['labels'] = $labels;
		}

		register_post_type( $slug, $args );
	}

	/**
	 * Register taxonomies.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register_taxonomies(): void {
		$config    = static::get_post_type_config();
		$post_type = $config['slug'] ?? '';

		if ( empty( $post_type ) ) {
			return;
		}

		$taxonomies = static::get_taxonomies_config();

		foreach ( $taxonomies as $taxonomy_slug => $taxonomy_config ) {
			$labels = $taxonomy_config['labels'] ?? array();
			$args   = $taxonomy_config['args'] ?? array();

			// Merge labels into args.
			if ( ! empty( $labels ) ) {
				$args['labels'] = $labels;
			}

			register_taxonomy( $taxonomy_slug, $post_type, $args );
		}
	}

	/**
	 * Register meta fields.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register_meta(): void {
		$config    = static::get_post_type_config();
		$post_type = $config['slug'] ?? '';

		if ( empty( $post_type ) ) {
			return;
		}

		$meta_fields = static::get_meta_config();

		foreach ( $meta_fields as $field ) {
			$key  = $field['key'] ?? '';
			$args = $field['args'] ?? array();

			if ( empty( $key ) ) {
				continue;
			}

			register_post_meta( $post_type, $key, $args );
		}
	}

	/**
	 * Register REST API fields.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register_rest_fields(): void {
		$config    = static::get_post_type_config();
		$post_type = $config['slug'] ?? '';

		if ( empty( $post_type ) ) {
			return;
		}

		$rest_fields = static::get_rest_fields_config();

		foreach ( $rest_fields as $field ) {
			$attribute = $field['attribute'] ?? '';
			$get       = $field['get'] ?? null;
			$update    = $field['update'] ?? null;
			$schema    = $field['schema'] ?? null;

			if ( empty( $attribute ) || ! is_callable( $get ) ) {
				continue;
			}

			$args = array(
				'get_callback' => $get,
			);

			if ( is_callable( $update ) ) {
				$args['update_callback'] = $update;
			}

			if ( is_array( $schema ) ) {
				$args['schema'] = $schema;
			}

			register_rest_field( $post_type, $attribute, $args );
		}
	}

	/**
	 * Backwards compatibility: Redirect old init() to subscribe().
	 *
	 * @since 0.6093.1200
	 * @deprecated Use CPT_Registry auto-discovery instead.
	 * @return     void
	 */
	public static function init(): void {
		static::subscribe();
	}
}
