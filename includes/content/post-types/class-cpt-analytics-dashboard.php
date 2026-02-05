<?php
/**
 * CPT Analytics Dashboard
 *
 * Provides analytics and tracking for custom post types,
 * showing views, interactions, and performance metrics.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since      1.6365.2359
 */

declare(strict_types=1);

namespace WPShadow\Content;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_Analytics_Dashboard Class
 *
 * Tracks and displays analytics data for all custom post types.
 *
 * @since 1.6365.2359
 */
class CPT_Analytics_Dashboard {

	/**
	 * Initialize analytics system.
	 *
	 * @since 1.6034.1300
	 * @return void
	 */
	public static function init() {
		add_action( 'wp', array( __CLASS__, 'track_view' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_analytics_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wpshadow_get_analytics', array( __CLASS__, 'handle_get_analytics' ) );
	}

	/**
	 * Track post view.
	 *
	 * @since 1.6034.1300
	 * @return void
	 */
	public static function track_view() {
		if ( ! is_singular() || is_admin() ) {
			return;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$post_type = get_post_type( $post_id );
		
		// Verify post type exists before checking supported list.
		if ( ! post_type_exists( $post_type ) ) {
			return;
		}

		$supported = array( 'testimonial', 'team_member', 'portfolio_item', 'wps_event', 'resource', 'case_study', 'service', 'location', 'documentation', 'wps_product' );

		if ( ! in_array( $post_type, $supported, true ) ) {
			return;
		}

		// Increment view count.
		$views = (int) get_post_meta( $post_id, '_wpshadow_views', true );
		update_post_meta( $post_id, '_wpshadow_views', $views + 1 );

		// Track daily views.
		$today = current_time( 'Y-m-d' );
		$daily_key = '_wpshadow_views_' . $today;
		$daily_views = (int) get_post_meta( $post_id, $daily_key, true );
		update_post_meta( $post_id, $daily_key, $daily_views + 1 );
	}

	/**
	 * Add analytics page to admin menu.
	 *
	 * @since 1.6034.1300
	 * @return void
	 */
	public static function add_analytics_page() {
		add_submenu_page(
			'wpshadow',
			__( 'Content Analytics', 'wpshadow' ),
			__( 'Analytics', 'wpshadow' ),
			'manage_options',
			'wpshadow-analytics',
			array( __CLASS__, 'render_analytics_page' )
		);
	}

	/**
	 * Render analytics page.
	 *
	 * @since 1.6034.1300
	 * @return void
	 */
	public static function render_analytics_page() {
		?>
		<div class="wrap wpshadow-analytics">
			<h1><?php esc_html_e( 'Content Analytics', 'wpshadow' ); ?></h1>

			<div class="wpshadow-analytics-filters">
				<select id="wpshadow-analytics-post-type">
					<option value="all"><?php esc_html_e( 'All Post Types', 'wpshadow' ); ?></option>
					<option value="testimonial"><?php esc_html_e( 'Testimonials', 'wpshadow' ); ?></option>
					<option value="team_member"><?php esc_html_e( 'Team Members', 'wpshadow' ); ?></option>
					<option value="portfolio_item"><?php esc_html_e( 'Portfolio', 'wpshadow' ); ?></option>
					<option value="wps_event"><?php esc_html_e( 'Events', 'wpshadow' ); ?></option>
					<option value="resource"><?php esc_html_e( 'Resources', 'wpshadow' ); ?></option>
					<option value="case_study"><?php esc_html_e( 'Case Studies', 'wpshadow' ); ?></option>
					<option value="service"><?php esc_html_e( 'Services', 'wpshadow' ); ?></option>
					<option value="location"><?php esc_html_e( 'Locations', 'wpshadow' ); ?></option>
					<option value="documentation"><?php esc_html_e( 'Documentation', 'wpshadow' ); ?></option>
					<option value="wps_product"><?php esc_html_e( 'Products', 'wpshadow' ); ?></option>
				</select>

				<select id="wpshadow-analytics-period">
					<option value="7"><?php esc_html_e( 'Last 7 Days', 'wpshadow' ); ?></option>
					<option value="30"><?php esc_html_e( 'Last 30 Days', 'wpshadow' ); ?></option>
					<option value="90"><?php esc_html_e( 'Last 90 Days', 'wpshadow' ); ?></option>
				</select>

				<button type="button" id="wpshadow-refresh-analytics" class="button">
					<?php esc_html_e( 'Refresh', 'wpshadow' ); ?>
				</button>
			</div>

			<div id="wpshadow-analytics-content">
				<div class="wpshadow-analytics-loading">
					<span class="spinner is-active"></span>
					<?php esc_html_e( 'Loading analytics...', 'wpshadow' ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue analytics assets.
	 *
	 * @since  1.6034.1300
	 * @param  string $hook Current page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'wpshadow_page_wpshadow-analytics' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-analytics',
			WPSHADOW_URL . 'assets/js/cpt-analytics.js',
			array( 'jquery', 'wp-util' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-analytics',
			'wpShadowAnalytics',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_analytics' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_enqueue_style(
			'wpshadow-analytics',
			WPSHADOW_URL . 'assets/css/cpt-analytics.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Handle analytics AJAX request.
	 *
	 * @since 1.6034.1300
	 * @return void
	 */
	public static function handle_get_analytics() {
		check_ajax_referer( 'wpshadow_analytics', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$post_type = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : 'all';
		$period    = isset( $_POST['period'] ) ? absint( $_POST['period'] ) : 7;

		$analytics = self::get_analytics_data( $post_type, $period );

		wp_send_json_success( $analytics );
	}

	/**
	 * Get analytics data.
	 *
	 * @since  1.6034.1300
	 * @param  string $post_type Post type slug or 'all'.
	 * @param  int    $period    Days to include.
	 * @return array Analytics data.
	 */
	private static function get_analytics_data( $post_type, $period ) {
		global $wpdb;

		$post_types = array( 'testimonial', 'team_member', 'portfolio_item', 'wps_event', 'resource', 'case_study', 'service', 'location', 'documentation', 'wps_product' );

		if ( 'all' !== $post_type ) {
			$post_types = array( $post_type );
		}

		$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		// Get top posts by views.
		$top_posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, p.post_title, p.post_type, pm.meta_value as views
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type IN ($placeholders)
				AND pm.meta_key = '_wpshadow_views'
				AND p.post_status = 'publish'
				ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC
				LIMIT 10",
				$post_types
			)
		);

		// Get total views.
		$total_views = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(CAST(pm.meta_value AS UNSIGNED))
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type IN ($placeholders)
				AND pm.meta_key = '_wpshadow_views'
				AND p.post_status = 'publish'",
				$post_types
			)
		);

		return array(
			'top_posts'   => $top_posts,
			'total_views' => (int) $total_views,
			'period'      => $period,
			'post_type'   => $post_type,
		);
	}

	/**
	 * Get view count for a post.
	 *
	 * @since  1.6034.1300
	 * @param  int $post_id Post ID.
	 * @return int View count.
	 */
	public static function get_view_count( $post_id ) {
		return (int) get_post_meta( $post_id, '_wpshadow_views', true );
	}
}
