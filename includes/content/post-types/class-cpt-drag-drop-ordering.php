<?php
/**
 * CPT Drag & Drop Ordering
 *
 * Provides drag-and-drop ordering functionality for custom post types,
 * allowing users to reorder items visually in the admin interface.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_Drag_Drop_Ordering Class
 *
 * Adds drag-and-drop ordering to CPT admin pages with persistent storage.
 *
 * @since 1.6093.1200
 */
class CPT_Drag_Drop_Ordering {

	/**
	 * Supported post types for drag & drop ordering.
	 *
	 * @var array
	 */
	private static $supported_post_types = array(
		'testimonial',
		'team_member',
		'portfolio_item',
		'wps_event',
		'resource',
		'case_study',
		'service',
		'location',
		'documentation',
		'wps_product',
	);

	/**
	 * Initialize the drag & drop ordering system.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wpshadow_update_post_order', array( __CLASS__, 'handle_ajax_update_order' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'apply_custom_order' ) );
		add_filter( 'posts_orderby', array( __CLASS__, 'posts_orderby' ), 10, 2 );
	}

	/**
	 * Enqueue drag & drop assets on CPT admin pages.
	 *
	 * @since 1.6093.1200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		// Only load on edit.php pages for our CPTs.
		if ( 'edit.php' !== $hook ) {
			return;
		}

		$current_screen = get_current_screen();
		if ( ! $current_screen || ! in_array( $current_screen->post_type, self::$supported_post_types, true ) ) {
			return;
		}

		// Verify the post type is actually registered.
		if ( ! post_type_exists( $current_screen->post_type ) ) {
			return;
		}

		// Enqueue jQuery UI Sortable (built-in).
		wp_enqueue_script( 'jquery-ui-sortable' );

		// Enqueue our custom drag & drop script.
		wp_enqueue_script(
			'wpshadow-cpt-drag-drop',
			WPSHADOW_URL . 'assets/js/cpt-drag-drop.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			WPSHADOW_VERSION,
			true
		);

		// Localize script with AJAX data.
		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-cpt-drag-drop',
			'wpShadowDragDrop',
			'wpshadow_drag_drop_order',
			array(
				'postType' => $current_screen->post_type,
			)
		);

		// Enqueue drag & drop styles.
		wp_enqueue_style(
			'wpshadow-cpt-drag-drop',
			WPSHADOW_URL . 'assets/css/cpt-drag-drop.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Handle AJAX request to update post order.
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_ajax_update_order() {
		// Verify nonce.
		check_ajax_referer( 'wpshadow_drag_drop_order', 'nonce' );

		// Check user capabilities.
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Insufficient permissions', 'wpshadow' ),
			) );
		}

		// Get post IDs and order.
		$order = isset( $_POST['order'] ) ? array_map( 'intval', (array) $_POST['order'] ) : array();
		
		if ( empty( $order ) ) {
			wp_send_json_error( array(
				'message' => __( 'No posts to reorder', 'wpshadow' ),
			) );
		}

		// Update menu_order for each post.
		$success = true;

		foreach ( $order as $position => $post_id ) {
			$result = wp_update_post(
				array(
					'ID'         => (int) $post_id,
					'menu_order' => (int) $position,
				),
				true
			);

			if ( is_wp_error( $result ) ) {
				$success = false;
				break;
			}

			// Clear post cache.
			clean_post_cache( $post_id );
		}

		if ( $success ) {
			wp_send_json_success( array(
				'message' => __( 'Post order updated successfully', 'wpshadow' ),
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Failed to update post order', 'wpshadow' ),
			) );
		}
	}

	/**
	 * Apply custom order to CPT queries.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Query $query The WP_Query instance.
	 * @return void
	 */
	public static function apply_custom_order( $query ) {
		// Only apply to admin queries for our CPTs.
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$post_type = $query->get( 'post_type' );
		
		if ( ! in_array( $post_type, self::$supported_post_types, true ) ) {
			return;
		}

		// Don't override if user has set a custom orderby.
		if ( $query->get( 'orderby' ) ) {
			return;
		}

		// Set ordering by menu_order then date.
		$query->set( 'orderby', array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		) );
	}

	/**
	 * Modify the ORDER BY clause for CPT queries.
	 *
	 * @since 1.6093.1200
	 * @param  string    $orderby The ORDER BY clause.
	 * @param  \WP_Query $query   The WP_Query instance.
	 * @return string Modified ORDER BY clause.
	 */
	public static function posts_orderby( $orderby, $query ) {
		// Only modify for our CPTs.
		$post_type = $query->get( 'post_type' );
		
		if ( ! in_array( $post_type, self::$supported_post_types, true ) ) {
			return $orderby;
		}

		// Don't override if custom orderby is set.
		if ( $query->get( 'orderby' ) && 'menu_order date' !== $query->get( 'orderby' ) ) {
			return $orderby;
		}

		// Order by menu_order first, then date.
		return 'menu_order ASC, post_date DESC';
	}

	/**
	 * Get the current order for a post type.
	 *
	 * @since 1.6093.1200
	 * @param  string $post_type Post type slug.
	 * @return array Array of post IDs in order.
	 */
	public static function get_post_order( $post_type ) {
		if ( ! in_array( $post_type, self::$supported_post_types, true ) ) {
			return array();
		}

		$posts = get_posts( array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'fields'         => 'ids',
		) );

		return $posts;
	}

	/**
	 * Set a specific order for posts.
	 *
	 * @since 1.6093.1200
	 * @param  string $post_type Post type slug.
	 * @param  array  $order     Array of post IDs in desired order.
	 * @return bool True on success, false on failure.
	 */
	public static function set_post_order( $post_type, $order ) {
		if ( ! in_array( $post_type, self::$supported_post_types, true ) ) {
			return false;
		}

		if ( ! is_array( $order ) || empty( $order ) ) {
			return false;
		}

		foreach ( $order as $position => $post_id ) {
			wp_update_post(
				array(
					'ID'         => (int) $post_id,
					'menu_order' => (int) $position,
				)
			);

			clean_post_cache( $post_id );
		}

		return true;
	}
}
