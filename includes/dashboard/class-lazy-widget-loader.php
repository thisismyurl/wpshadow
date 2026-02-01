<?php
/**
 * Lazy Widget Loader
 *
 * Implements lazy loading for dashboard widgets using AJAX callbacks.
 * Reduces initial dashboard load time by deferring widget rendering until needed.
 *
 * @package    WPShadow
 * @subpackage Dashboard
 * @since      1.2602.0100
 */

declare(strict_types=1);

namespace WPShadow\Dashboard;

use WPShadow\Core\Cache_Manager;
use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lazy Widget Loader Class
 *
 * Lazy loads dashboard widgets to improve initial page load time.
 * Uses AJAX to load widget content after page render completes.
 *
 * Philosophy: Show value (#9) - Faster dashboard experience.
 *
 * @since 1.2602.0100
 */
class Lazy_Widget_Loader {

	/**
	 * Initialize lazy widget loading
	 *
	 * @since  1.2602.0100
	 * @return void
	 */
	public static function init(): void {
		// Only on WPShadow dashboard pages
		add_action( 'admin_init', array( __CLASS__, 'setup_lazy_loading' ), 5 );

		// Register AJAX handler for widget loading
		add_action( 'wp_ajax_wpshadow_load_widget', array( 'WPShadow\Dashboard\AJAX_Load_Widget', 'handle' ) );
	}

	/**
	 * Setup lazy loading on dashboard pages
	 *
	 * @since  1.2602.0100
	 * @return void
	 */
	public static function setup_lazy_loading(): void {
		// Check if we're on a WPShadow page
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, 'wpshadow' ) ) {
			return;
		}

		// Only on dashboard
		if ( 'dashboard' !== $screen->id ) {
			return;
		}

		// Enqueue lazy loader script
		wp_enqueue_script(
			'wpshadow-lazy-widgets',
			WPSHADOW_URL . 'assets/js/lazy-widget-loader.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-lazy-widgets',
			'wpshadowLazyWidgets',
			array(
				'nonce'     => wp_create_nonce( 'wpshadow_load_widget' ),
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'widgets'   => self::get_lazy_widgets(),
				'loadDelay' => 500, // ms after page render
			)
		);

		// Enqueue lazy loading CSS
		wp_enqueue_style(
			'wpshadow-lazy-widgets',
			WPSHADOW_URL . 'assets/css/lazy-widgets.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Get list of widgets to lazy load
	 *
	 * High-impact widgets that take time to render are good candidates for lazy loading.
	 *
	 * @since  1.2602.0100
	 * @return array {
	 *     Array of lazy-loadable widgets.
	 *
	 *     @type string $id Widget ID
	 *     @type string $title Widget title
	 *     @type int $priority Load priority (lower = load first)
	 * }
	 */
	public static function get_lazy_widgets(): array {
		/**
		 * Filter lazy-loadable widgets
		 *
		 * @since  1.2602.0100
		 *
		 * @param array $widgets Array of widget configurations
		 */
		return apply_filters(
			'wpshadow_lazy_widgets',
			array(
				array(
					'id'       => 'wpshadow_diagnostics_widget',
					'title'    => __( 'Security Diagnostics', 'wpshadow' ),
					'priority' => 10,
				),
				array(
					'id'       => 'wpshadow_performance_widget',
					'title'    => __( 'Performance Metrics', 'wpshadow' ),
					'priority' => 20,
				),
				array(
					'id'       => 'wpshadow_recommendations_widget',
					'title'    => __( 'Recommendations', 'wpshadow' ),
					'priority' => 30,
				),
				array(
					'id'       => 'wpshadow_recent_activity_widget',
					'title'    => __( 'Recent Activity', 'wpshadow' ),
					'priority' => 40,
				),
			)
		);
	}

	/**
	 * Get widget placeholder HTML
	 *
	 * Placeholder shown while widget loads
	 *
	 * @since  1.2602.0100
	 * @param  string $widget_id Widget ID
	 * @param  string $title Widget title
	 * @return string Placeholder HTML
	 */
	public static function get_widget_placeholder( string $widget_id, string $title ): string {
		return sprintf(
			'<div class="wpshadow-widget-placeholder" data-widget-id="%s">
				<div class="wpshadow-widget-header">
					<h3>%s</h3>
					<div class="spinner"></div>
				</div>
				<div class="wpshadow-widget-content">
					<p class="description">%s</p>
				</div>
			</div>',
			esc_attr( $widget_id ),
			esc_html( $title ),
			esc_html__( 'Loading...', 'wpshadow' )
		);
	}

	/**
	 * Cache is valid check
	 *
	 * @since  1.2602.0100
	 * @param  string $widget_id Widget ID
	 * @return bool Whether cache is valid
	 */
	public static function is_cache_valid( string $widget_id ): bool {
		$cached = Cache_Manager::get( 'widget_' . $widget_id, 'wpshadow_widgets' );
		return false !== $cached;
	}

	/**
	 * Get widget content from cache
	 *
	 * @since  1.2602.0100
	 * @param  string $widget_id Widget ID
	 * @return string|false Widget HTML or false if not cached
	 */
	public static function get_cached_widget( string $widget_id ) {
		return Cache_Manager::get( 'widget_' . $widget_id, 'wpshadow_widgets', false );
	}

	/**
	 * Cache widget content
	 *
	 * @since  1.2602.0100
	 * @param  string $widget_id Widget ID
	 * @param  string $html Widget HTML
	 * @param  int $ttl Cache TTL in seconds
	 * @return void
	 */
	public static function cache_widget( string $widget_id, string $html, int $ttl = HOUR_IN_SECONDS ): void {
		Cache_Manager::set( 'widget_' . $widget_id, $html, $ttl, 'wpshadow_widgets' );
	}

	/**
	 * Invalidate widget cache
	 *
	 * @since  1.2602.0100
	 * @param  string $widget_id Widget ID
	 * @return void
	 */
	public static function invalidate_widget( string $widget_id ): void {
		Cache_Manager::delete( 'widget_' . $widget_id, 'wpshadow_widgets' );
	}

	/**
	 * Invalidate all widget caches
	 *
	 * @since  1.2602.0100
	 * @return void
	 */
	public static function invalidate_all_widgets(): void {
		Cache_Manager::flush( 'wpshadow_widgets' );
	}
}

/**
 * AJAX Handler for Loading Widgets
 *
 * Handles AJAX requests to load individual widgets
 *
 * @since 1.2602.0100
 */
class AJAX_Load_Widget extends AJAX_Handler_Base {

	/**
	 * Handle widget load request
	 *
	 * @since  1.2602.0100
	 * @return void Dies after sending response
	 */
	public static function handle(): void {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_load_widget', 'read' );

		// Get widget ID
		$widget_id = self::get_post_param( 'widget_id', 'text', '', true );

		// Validate widget ID
		if ( ! in_array( $widget_id, self::get_allowed_widgets(), true ) ) {
			self::send_error( __( 'Invalid widget ID', 'wpshadow' ) );
		}

		// Check cache first
		$cached = Lazy_Widget_Loader::get_cached_widget( $widget_id );
		if ( false !== $cached ) {
			self::send_success(
				array(
					'html'     => $cached,
					'cached'   => true,
					'widget'   => $widget_id,
				)
			);
		}

		// Render widget
		$html = self::render_widget( $widget_id );

		if ( empty( $html ) ) {
			self::send_error( __( 'Failed to load widget', 'wpshadow' ) );
		}

		// Cache the rendered widget
		Lazy_Widget_Loader::cache_widget( $widget_id, $html, HOUR_IN_SECONDS );

		self::send_success(
			array(
				'html'     => $html,
				'cached'   => false,
				'widget'   => $widget_id,
			)
		);
	}

	/**
	 * Get list of allowed widgets
	 *
	 * @since  1.2602.0100
	 * @return array Widget IDs
	 */
	private static function get_allowed_widgets(): array {
		$lazy_widgets = Lazy_Widget_Loader::get_lazy_widgets();
		return array_column( $lazy_widgets, 'id' );
	}

	/**
	 * Render widget content
	 *
	 * @since  1.2602.0100
	 * @param  string $widget_id Widget ID
	 * @return string Widget HTML
	 */
	private static function render_widget( string $widget_id ): string {
		// Widget rendering logic - dispatched by widget ID
		switch ( $widget_id ) {
			case 'wpshadow_diagnostics_widget':
				return self::render_diagnostics_widget();
			case 'wpshadow_performance_widget':
				return self::render_performance_widget();
			case 'wpshadow_recommendations_widget':
				return self::render_recommendations_widget();
			case 'wpshadow_recent_activity_widget':
				return self::render_activity_widget();
			default:
				/**
				 * Filter widget rendering
				 *
				 * @since  1.2602.0100
				 *
				 * @param string $html Empty HTML by default
				 * @param string $widget_id Widget ID
				 */
				return apply_filters( 'wpshadow_render_lazy_widget', '', $widget_id );
		}
	}

	/**
	 * Render diagnostics widget
	 *
	 * @since  1.2602.0100
	 * @return string Widget HTML
	 */
	private static function render_diagnostics_widget(): string {
		ob_start();
		?>
		<div class="wpshadow-widget wpshadow-diagnostics">
			<h3><?php esc_html_e( 'Security Diagnostics', 'wpshadow' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Loading diagnostics...', 'wpshadow' ); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render performance widget
	 *
	 * @since  1.2602.0100
	 * @return string Widget HTML
	 */
	private static function render_performance_widget(): string {
		ob_start();
		?>
		<div class="wpshadow-widget wpshadow-performance">
			<h3><?php esc_html_e( 'Performance Metrics', 'wpshadow' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Loading metrics...', 'wpshadow' ); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render recommendations widget
	 *
	 * @since  1.2602.0100
	 * @return string Widget HTML
	 */
	private static function render_recommendations_widget(): string {
		ob_start();
		?>
		<div class="wpshadow-widget wpshadow-recommendations">
			<h3><?php esc_html_e( 'Recommendations', 'wpshadow' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Loading recommendations...', 'wpshadow' ); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render activity widget
	 *
	 * @since  1.2602.0100
	 * @return string Widget HTML
	 */
	private static function render_activity_widget(): string {
		ob_start();
		?>
		<div class="wpshadow-widget wpshadow-activity">
			<h3><?php esc_html_e( 'Recent Activity', 'wpshadow' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Loading activity...', 'wpshadow' ); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}
}
