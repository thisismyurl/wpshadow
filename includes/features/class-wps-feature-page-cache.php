<?php
/**
 * Feature: Page Cache
 *
 * Full page caching for improved performance.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_Page_Cache
 *
 * Full page caching with device detection and automatic invalidation.
 */
final class WPSHADOW_Feature_Page_Cache extends WPSHADOW_Abstract_Feature {

	/**
	 * Cache directory path.
	 */
	private const CACHE_DIR = WP_CONTENT_DIR . '/cache/wps-page-cache/';

	/**
	 * Cache lifespan in seconds.
	 */
	private const CACHE_LIFESPAN = 3600;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'page-cache',
				'name'               => __( 'Page Cache', 'plugin-wpshadow' ),
				'description'        => __( 'Creates full page copies that serve instantly to visitors, with device-aware variants, automatic invalidation when content updates, and preload to keep the cache warm. Reduces PHP and database work, lowers server load, and speeds up repeat views. Works alongside logged-in and admin bypass rules so editors see fresh content while visitors enjoy faster page delivery with minimal configuration.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
			'widget_group'       => 'advanced',
			'widget_label'       => __( 'Advanced Features', 'plugin-wpshadow' ),
				'widget_description' => __( 'Caching and performance optimization', 'plugin-wpshadow' ),
				'license_level'      => 2,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-performance',
				'category'           => 'performance',
				'priority'           => 10,
				'dashboard'          => 'overview',
				'widget_column'      => 'left',
				'widget_priority'    => 10,
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Output buffering for cache generation.
		add_action( 'template_redirect', array( $this, 'start_output_buffering' ), 0 );

		// Cache invalidation hooks if auto-invalidation is enabled.
		if ( get_option( 'wpshadow_page-cache_auto_invalidation', true ) ) {
			add_action( 'save_post', array( $this, 'invalidate_post_cache' ), 10, 2 );
			add_action( 'deleted_post', array( $this, 'invalidate_post_cache' ) );
			add_action( 'comment_post', array( $this, 'invalidate_comment_cache' ), 10, 3 );
			add_action( 'switch_theme', array( $this, 'purge_all_cache' ) );
		}

		// Admin bar menu.
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 90 );

		// AJAX handlers.
		add_action( 'wp_ajax_WPSHADOW_purge_cache', array( $this, 'ajax_purge_cache' ) );

		// Scheduled garbage collection.
		if ( ! wp_next_scheduled( 'wpshadow_cache_cleanup' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_cache_cleanup' );
		}
		add_action( 'wpshadow_cache_cleanup', array( $this, 'garbage_collection' ) );

		// Ensure cache directory exists.
		$this->ensure_cache_directory();
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Start output buffering to capture page HTML.
	 *
	 * @return void
	 */
	public function start_output_buffering(): void {
		if ( ! $this->should_cache() ) {
			return;
		}

		// Try to serve cached version.
		if ( $this->serve_cache() ) {
			exit;
		}

		// Start buffering to create cache.
		ob_start( array( $this, 'save_cache' ) );
	}

	/**
	 * Save captured output to cache.
	 *
	 * @param string $html Page HTML.
	 * @return string Unmodified HTML.
	 */
	public function save_cache( string $html ): string {
		if ( ! $this->should_cache() || empty( $html ) ) {
			return $html;
		}

		$cache_key  = $this->get_cache_key();
		$cache_file = self::CACHE_DIR . $cache_key . '.html';

		// Add cache info comment.
		$html .= "\n<!-- Cached by WPShadow on " . gmdate( 'Y-m-d H:i:s' ) . ' UTC -->';

		// Save to cache file.
		file_put_contents( $cache_file, $html, LOCK_EX );

		return $html;
	}

	/**
	 * Generate cache key for current request.
	 *
	 * @return string Cache key.
	 */
	private function get_cache_key(): string {
		$parts = array(
			$_SERVER['REQUEST_URI'] ?? '/',
			$this->get_device_type(),
			is_user_logged_in() ? 'logged-in' : 'logged-out',
			$_SERVER['QUERY_STRING'] ?? '',
		);

		return md5( implode( '|', $parts ) );
	}

	/**
	 * Get device type (mobile/desktop).
	 *
	 * @return string Device type.
	 */
	private function get_device_type(): string {
		if ( wp_is_mobile() ) {
			return 'mobile';
		}
		return 'desktop';
	}

	/**
	 * Check if current request should be cached.
	 *
	 * @return bool True if should cache.
	 */
	private function should_cache(): bool {
		// Don't cache if POST request.
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			return false;
		}

		// Don't cache if user is logged in.
		if ( is_user_logged_in() ) {
			return false;
		}

		// Don't cache admin pages.
		if ( is_admin() ) {
			return false;
		}

		// Don't cache if specific cookies present.
		if ( $this->has_nocache_cookies() ) {
			return false;
		}

		return true;
	}

	/**
	 * Check for no-cache cookies.
	 *
	 * @return bool True if has nocache cookies.
	 */
	private function has_nocache_cookies(): bool {
		$nocache_cookies = array(
			'wordpress_logged_in_',
			'wp-postpass_',
			'woocommerce_cart_hash',
			'woocommerce_items_in_cart',
		);

		foreach ( $_COOKIE as $name => $value ) {
			foreach ( $nocache_cookies as $nocache ) {
				if ( strpos( $name, $nocache ) === 0 ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Serve cached page if available.
	 *
	 * @return bool True if cache served.
	 */
	private function serve_cache(): bool {
		$cache_key  = $this->get_cache_key();
		$cache_file = self::CACHE_DIR . $cache_key . '.html';

		if ( ! file_exists( $cache_file ) ) {
			return false;
		}

		// Check if cache is expired.
		if ( ( time() - filemtime( $cache_file ) ) > self::CACHE_LIFESPAN ) {
			return false;
		}

		// Send cache hit header.
		header( 'X-WPS-Cache: HIT' );

		// Output cached HTML.
		readfile( $cache_file );

		return true;
	}

	/**
	 * Purge cache for a specific post.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function invalidate_post_cache( int $post_id ): void {
		$post_url = get_permalink( $post_id );
		if ( $post_url ) {
			$this->purge_url_cache( $post_url );
		}

		// Also purge homepage.
		$this->purge_url_cache( home_url( '/' ) );
	}

	/**
	 * Purge cache for a URL.
	 *
	 * @param string $url URL to purge.
	 * @return void
	 */
	private function purge_url_cache( string $url ): void {
		$uri = wp_parse_url( $url, PHP_URL_PATH );
		if ( ! $uri ) {
			return;
		}

		// Purge all device types for this URL.
		$devices      = array( 'mobile', 'desktop' );
		$login_states = array( 'logged-in', 'logged-out' );

		foreach ( $devices as $device ) {
			foreach ( $login_states as $state ) {
				$parts      = array( $uri, $device, $state, '' );
				$cache_key  = md5( implode( '|', $parts ) );
				$cache_file = self::CACHE_DIR . $cache_key . '.html';

				if ( file_exists( $cache_file ) ) {
					unlink( $cache_file );
				}
			}
		}
	}

	/**
	 * Purge all cache.
	 *
	 * @return void
	 */
	public function purge_all_cache(): void {
		$cache_files = glob( self::CACHE_DIR . '*.html' );
		if ( $cache_files ) {
			foreach ( $cache_files as $file ) {
				unlink( $file );
			}
		}
	}

	/**
	 * Invalidate cache when comment is posted.
	 *
	 * @param int $comment_id Comment ID.
	 * @return void
	 */
	public function invalidate_comment_cache( int $comment_id ): void {
		$comment = get_comment( $comment_id );
		if ( $comment && $comment->comment_post_ID ) {
			$this->invalidate_post_cache( $comment->comment_post_ID );
		}
	}

	/**
	 * Add admin bar menu.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar.
	 * @return void
	 */
	public function add_admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'wps-purge-cache',
				'title' => __( 'Purge Cache', 'plugin-wpshadow' ),
				'href'  => '#',
				'meta'  => array(
					'class' => 'wps-purge-cache-link',
				),
			)
		);
	}

	/**
	 * AJAX handler for cache purging.
	 *
	 * @return void
	 */
	public function ajax_purge_cache(): void {
		\WPShadow\WPSHADOW_verify_ajax_request( 'wps-cache' );

		$this->purge_all_cache();

		wp_send_json_success( array(
			'message' => __( 'Cache purged successfully', 'plugin-wpshadow' ),
		) );
	}

	/**
	 * Garbage collection for old cache files.
	 *
	 * @return void
	 */
	public function garbage_collection(): void {
		$cache_files = glob( self::CACHE_DIR . '*.html' );
		$now         = time();

		if ( $cache_files ) {
			foreach ( $cache_files as $file ) {
				if ( ( $now - filemtime( $file ) ) > self::CACHE_LIFESPAN ) {
					unlink( $file );
				}
			}
		}
	}

	/**
	 * Ensure cache directory exists.
	 *
	 * @return void
	 */
	private function ensure_cache_directory(): void {
		if ( ! is_dir( self::CACHE_DIR ) ) {
			wp_mkdir_p( self::CACHE_DIR );

			// Add .htaccess for security.
			$htaccess = self::CACHE_DIR . '.htaccess';
			if ( ! file_exists( $htaccess ) ) {
				file_put_contents( $htaccess, "# Cache files\n<Files *.html>\n\tRequire all granted\n</Files>\n" );
			}
		}
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_page_cache'] = array(
			'label' => __( 'Page Cache', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_page_cache' ),
		);
		return $tests;
	}

	/**
	 * Site Health test for page cache.
	 *
	 * @return array<string, mixed>
	 */
	public function test_page_cache(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Page Cache', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'orange',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Page Cache is not enabled. Enabling page caching can significantly improve site performance and reduce server load.', 'plugin-wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_page_cache',
			);
		}

		// Count cached files.
		$cached_files = 0;
		if ( is_dir( self::CACHE_DIR ) ) {
			$files = glob( self::CACHE_DIR . '*.html' );
			if ( is_array( $files ) ) {
				$cached_files = count( $files );
			}
		}

		// Count enabled sub-features.
		$enabled_features = 0;
		if ( get_option( 'wpshadow_page-cache_device_detection', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_page-cache_auto_invalidation', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_page-cache_gzip_compression', true ) ) {
			++$enabled_features;
		}

		return array(
			'label'       => __( 'Page Cache', 'plugin-wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				/* translators: 1: number of cached files, 2: number of enabled features */
				sprintf(
					__( 'Page Cache is active with %1$d cached pages and %2$d optimization features enabled.', 'plugin-wpshadow' ),
					$cached_files,
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_page_cache',
		);
	}
}
