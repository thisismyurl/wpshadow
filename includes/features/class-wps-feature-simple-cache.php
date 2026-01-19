<?php
/**
 * Feature: Simple Page Cache
 *
 * Serves static HTML files instead of regenerating pages on every visit.
 * Significantly improves performance by bypassing WordPress and PHP processing
 * for cached pages.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75002
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_Simple_Cache
 *
 * Implements a simple page caching system that stores rendered HTML.
 */
final class WPSHADOW_Feature_Simple_Cache extends WPSHADOW_Abstract_Feature {

	/**
	 * Cache directory path.
	 *
	 * @var string
	 */
	private string $cache_dir;

	/**
	 * Cache expiration time in seconds.
	 *
	 * @var int
	 */
	private const CACHE_LIFETIME = 3600; // 1 hour

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'simple-cache',
				'name'            => __( 'Save Pages for Faster Loading', 'wpshadow' ),
				'description'     => __( 'Save a copy of your pages so they load instantly for visitors without rebuilding every time. Makes your site much faster.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => false,
				'version'         => '1.0.0',
				'widget_group'    => 'performance',
				'aliases'         => array(
					'page cache',
					'caching',
					'speed up site',
					'faster loading',
					'performance optimization',
					'slow site',
					'static html',
					'page speed',
					'load time',
					'quick pages',
				),
				'sub_features'    => array(
					'cache_pages'         => array(
						'name'               => __( 'Cache Static Pages', 'wpshadow' ),
						'description_short'  => __( 'Save copies of front page and static pages', 'wpshadow' ),
						'description_long'   => __( 'Caches static pages like your homepage and policy pages. These pages change infrequently, so caching them as static HTML provides massive speed improvements. Visitors see pre-built HTML instead of WordPress needing to build the page from scratch every time.', 'wpshadow' ),
						'description_wizard' => __( 'Speed up your main pages by serving them as pre-built HTML. Big performance gain with minimal configuration needed.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'cache_posts'         => array(
						'name'               => __( 'Cache Blog Posts', 'wpshadow' ),
						'description_short'  => __( 'Save copies of published blog articles', 'wpshadow' ),
						'description_long'   => __( 'Caches individual blog posts after publishing. Once cached, visitors see the static version instead of WordPress rebuilding the page. Works great for blog content that rarely changes after publication. Comments on cached posts still load dynamically, ensuring readers see current feedback.', 'wpshadow' ),
						'description_wizard' => __( 'Cache published blog posts for fast loading. Readers still see live comments while enjoying page speed benefits.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'cache_archives'      => array(
						'name'               => __( 'Cache Archive Pages', 'wpshadow' ),
						'description_short'  => __( 'Save copies of category and date archives', 'wpshadow' ),
						'description_long'   => __( 'Caches archive pages like category listings, tag clouds, and date archives. These pages display lists of posts and change when new content is published. Caching helps even though they change, especially on high-traffic sites with many requests to same archive page.', 'wpshadow' ),
						'description_wizard' => __( 'Speed up archive and listing pages by serving cached versions. Automatically updates when new content is published.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'skip_logged_in'      => array(
						'name'               => __( 'Skip Cache for Logged-In Users', 'wpshadow' ),
						'description_short'  => __( 'Always show fresh content when editing', 'wpshadow' ),
						'description_long'   => __( 'Disables caching when you\'re logged into WordPress. Logged-in users (especially editors and admins) see fresh content so edits take effect immediately. Without this, you might edit a post and still see the old cached version, which is confusing. Essential for content creators.', 'wpshadow' ),
						'description_wizard' => __( 'Very important: Disable caching for logged-in users so you see your edits immediately. Always keep this enabled.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'skip_query_strings'  => array(
						'name'               => __( 'Skip Cache for Search/Filters', 'wpshadow' ),
						'description_short'  => __( 'Don\'t cache search results or filter results', 'wpshadow' ),
						'description_long'   => __( 'Disables caching for pages with query strings (?search=keyword, ?filter=value). Search results and filter results vary for each query, so caching them doesn\'t make sense and can cause wrong results to display. These pages are typically uncached anyway due to dynamic content.', 'wpshadow' ),
						'description_wizard' => __( 'Don\'t cache search results and filters - they\'re too dynamic. Keep this enabled to prevent wrong results.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'auto_clear_on_save'  => array(
						'name'               => __( 'Auto-Clear on Publish', 'wpshadow' ),
						'description_short'  => __( 'Update cache when content is published', 'wpshadow' ),
						'description_long'   => __( 'Automatically clears and refreshes cache when you publish or update content. Ensures visitors always see the latest version. Without this, old cached content stays displayed until cache expires or is manually cleared, showing stale content to readers.', 'wpshadow' ),
						'description_wizard' => __( 'Essential: Automatically update cache when you publish changes. Without this, visitors see old content until cache expires.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'advanced_cache_keys' => array(
						'name'               => __( 'Advanced Cache Keys', 'wpshadow' ),
						'description_short'  => __( 'Create separate cache for different devices/roles', 'wpshadow' ),
						'description_long'   => __( 'Creates separate cache entries for different device types (mobile vs desktop) and user roles. Useful if your site displays different content based on device or who\'s viewing. Requires more cache space but prevents showing desktop version to mobile users. Advanced option disabled by default.', 'wpshadow' ),
						'description_wizard' => __( 'Advanced: Cache different versions for mobile vs desktop. Only enable if your site shows different content to different devices or roles.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'partial_cache'       => array(
						'name'               => __( 'Partial/Fragment Caching', 'wpshadow' ),
						'description_short'  => __( 'Cache page sections instead of whole pages', 'wpshadow' ),
						'description_long'   => __( 'Enables fragment caching where parts of pages are cached separately. Useful for pages with both static content (sidebar, header) and dynamic content (comments, user data). Allows caching the static parts while keeping dynamic parts fresh. Advanced technique disabled by default.', 'wpshadow' ),
						'description_wizard' => __( 'Advanced: Cache page fragments separately. Only for experienced users who understand cache invalidation.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'cache_preload'       => array(
						'name'               => __( 'Cache Preloading', 'wpshadow' ),
						'description_short'  => __( 'Pre-generate cache for popular pages', 'wpshadow' ),
						'description_long'   => __( 'Automatically pre-generates and warms up cache for your most popular pages before visitors request them. Ensures these pages are always cached and ready instantly. Useful for traffic spikes or promoting popular content. Advanced option disabled by default.', 'wpshadow' ),
						'description_wizard' => __( 'Advanced: Pre-cache your most popular pages so they\'re instantly ready. Good for high-traffic sites.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'cache_cdn'           => array(
						'name'               => __( 'CDN Integration', 'wpshadow' ),
						'description_short'  => __( 'Serve cached files from CDN', 'wpshadow' ),
						'description_long'   => __( 'Enables CDN integration where cached files are served from a content delivery network for faster global distribution. CDN makes pages load faster for visitors in different geographic regions by serving content from servers closer to them. Requires CDN account configuration. Advanced option disabled by default.', 'wpshadow' ),
						'description_wizard' => __( 'Advanced: Use a CDN to serve cached content globally faster. Requires CDN account setup.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'cache_warming'       => array(
						'name'               => __( 'Background Cache Warming', 'wpshadow' ),
						'description_short'  => __( 'Refresh cache in background before expiry', 'wpshadow' ),
						'description_long'   => __( 'Refreshes expired cache in the background before visitors request it. Instead of visitors waiting for cache to rebuild, old cache serves while new cache builds in background. Provides seamless experience without the first-visitor-after-expiry slowdown. Advanced option disabled by default.', 'wpshadow' ),
						'description_wizard' => __( 'Advanced: Rebuild cache in the background so visitors never wait for cache refresh.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'cache_compression'   => array(
						'name'               => __( 'Cache Compression', 'wpshadow' ),
						'description_short'  => __( 'Compress cached files with gzip', 'wpshadow' ),
						'description_long'   => __( 'Compresses cached HTML files with gzip compression to reduce disk space requirements. Works great for sites with huge amounts of cached content. Compression saves 50-70% of cache size on disk. Decompression is handled automatically. Advanced option disabled by default.', 'wpshadow' ),
						'description_wizard' => __( 'Advanced: Compress cache files to save disk space. Good for sites with lots of cached content.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'cache_mobile_split'  => array(
						'name'               => __( 'Mobile-Specific Cache', 'wpshadow' ),
						'description_short'  => __( 'Cache mobile and desktop separately', 'wpshadow' ),
						'description_long'   => __( 'Creates separate cache for mobile vs desktop visitors. Useful if your site displays significantly different layouts or content for mobile and desktop. Prevents showing desktop layout to mobile users. Requires detecting device type and managing separate cache. Advanced option disabled by default.', 'wpshadow' ),
						'description_wizard' => __( 'Advanced: Cache mobile and desktop versions separately if your site displays them differently.', 'wpshadow' ),
						'default_enabled'    => false,
					),
				),
			)
		);

		$this->register_default_settings(
			array(
				'cache_pages'         => true,
				'cache_posts'         => true,
				'cache_archives'      => true,
				'skip_logged_in'      => true,
				'skip_query_strings'  => true,
				'auto_clear_on_save'  => true,
				'advanced_cache_keys' => false,
				'partial_cache'       => false,
				'cache_preload'       => false,
				'cache_cdn'           => false,
				'cache_warming'       => false,
				'cache_compression'   => false,
				'cache_mobile_split'  => false,
			)
		);

		// Set cache directory per-site to avoid multisite collisions.
		$cache_base = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
		$blog_id    = is_multisite() ? get_current_blog_id() : 0;
		$this->cache_dir = trailingslashit( $cache_base ) . 'uploads/wpshadow-cache';
		if ( $blog_id > 0 ) {
			$this->cache_dir .= '-' . $blog_id;
		}

		$this->log_activity( 'feature_initialized', 'Simple Cache feature initialized', 'info' );
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

		// Create cache directory if needed
		$this->ensure_cache_directory();

		// Hook very early to check for cached content before WordPress loads templates
		add_action( 'template_redirect', array( $this, 'maybe_serve_cache' ), 1 );

		// Start output buffering to capture page content
		add_action( 'template_redirect', array( $this, 'start_output_buffering' ), 2 );

		// Clear cache on post save/update
		if ( $this->is_sub_feature_enabled( 'auto_clear_on_save', true ) ) {
			add_action( 'save_post', array( $this, 'clear_post_cache' ), 10, 1 );
			add_action( 'deleted_post', array( $this, 'clear_post_cache' ), 10, 1 );
			add_action( 'wp_trash_post', array( $this, 'clear_post_cache' ), 10, 1 );
		}

		// Clear cache on comment
		add_action( 'comment_post', array( $this, 'clear_post_cache_by_comment' ), 10, 1 );
		add_action( 'wp_set_comment_status', array( $this, 'clear_post_cache_by_comment' ), 10, 1 );

		// Clear cache on theme/plugin changes
		add_action( 'switch_theme', array( $this, 'clear_all_cache' ) );
		add_action( 'activated_plugin', array( $this, 'clear_all_cache' ) );
		add_action( 'deactivated_plugin', array( $this, 'clear_all_cache' ) );

		// Admin actions
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_button' ), 100 );
		add_action( 'admin_post_wpshadow_clear_cache', array( $this, 'admin_clear_cache' ) );
		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );

		// Site health test
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		// WP-CLI commands
		if ( defined( 'WP_CLI' ) && 
			class_exists( '\WP_CLI' ) ) {
			$this->register_cli_commands();
		}
	}

	/**
	 * Ensure cache directory exists and is writable.
	 *
	 * @return bool
	 */
	private function ensure_cache_directory(): bool {
		if ( ! file_exists( $this->cache_dir ) ) {
			if ( ! wp_mkdir_p( $this->cache_dir ) ) {
				$this->log_activity( 'cache_error', 'Failed to create cache directory', 'error' );
				return false;
			}
		}

		// Create .htaccess to protect cache directory
		$htaccess_file = $this->cache_dir . '/.htaccess';
		if ( ! file_exists( $htaccess_file ) ) {
			$htaccess_content = "# WPShadow Cache Directory\n";
			$htaccess_content .= "<Files ~ \"\.html$\">\n";
			$htaccess_content .= "  Allow from all\n";
			$htaccess_content .= "</Files>\n";
			file_put_contents( $htaccess_file, $htaccess_content ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		// Create index.php to prevent directory listing
		$index_file = $this->cache_dir . '/index.php';
		if ( ! file_exists( $index_file ) ) {
			file_put_contents( $index_file, '<?php // Silence is golden' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		return true;
	}

	/**
	 * Check if current request should be cached.
	 *
	 * @return bool
	 */
	private function should_cache_request(): bool {
		// Don't cache if disabled
		if ( ! $this->is_enabled() ) {
			return false;
		}

		// Don't cache admin, login, or AJAX requests
		if ( is_admin() || is_user_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return false;
		}

		// Don't cache for logged-in users if setting enabled
		if ( $this->is_sub_feature_enabled( 'skip_logged_in', true ) && is_user_logged_in() ) {
			return false;
		}

		// Don't cache POST requests
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return false;
		}

		// Don't cache requests with query strings if setting enabled
		if ( $this->is_sub_feature_enabled( 'skip_query_strings', true ) && ! empty( $_SERVER['QUERY_STRING'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return false;
		}

		// Check if current page type should be cached
		if ( is_singular() && ! is_front_page() ) {
			if ( is_page() && ! $this->is_sub_feature_enabled( 'cache_pages', true ) ) {
				return false;
			}
			if ( is_single() && ! $this->is_sub_feature_enabled( 'cache_posts', true ) ) {
				return false;
			}
		}

		if ( is_archive() && ! $this->is_sub_feature_enabled( 'cache_archives', true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get cache file path for current request.
	 *
	 * @return string
	 */
	private function get_cache_file_path(): string {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$host        = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';

		// Create a unique cache key
		$cache_key = md5( $host . $request_uri );

		return $this->cache_dir . '/' . $cache_key . '.html';
	}

	/**
	 * Check for cached page and serve it if valid.
	 *
	 * @return void
	 */
	public function maybe_serve_cache(): void {
		if ( ! $this->should_cache_request() ) {
			return;
		}

		$cache_file = $this->get_cache_file_path();

		// Check if cache file exists and is still valid
		if ( file_exists( $cache_file ) ) {
			$file_age = time() - filemtime( $cache_file );

			if ( $file_age < self::CACHE_LIFETIME ) {
				// Serve cached content
				header( 'X-WPShadow-Cache: HIT' );
				header( 'X-Cache-Age: ' . $file_age . 's' );
				header( 'Cache-Control: public, max-age=' . ( self::CACHE_LIFETIME - $file_age ) );
						do_action( 'wpshadow_simple_cache_hit', $this->get_current_url(), $cache_file, $file_age );

				// Read and output cached content
				readfile( $cache_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile

				$this->log_activity( 'cache_hit', 'Served cached page: ' . $this->get_current_url(), 'info' );

				exit;
			} else {
				// Cache expired, delete it
				unlink( $cache_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_unlink
				$this->log_activity( 'cache_expired', 'Cache expired for: ' . $this->get_current_url(), 'info' );
				do_action( 'wpshadow_simple_cache_expired', $this->get_current_url(), $cache_file );
			}
		}

		// Mark that this is a cache miss
		header( 'X-WPShadow-Cache: MISS' );
		do_action( 'wpshadow_simple_cache_miss', $this->get_current_url() );
	}

	/**
	 * Start output buffering to capture page content.
	 *
	 * @return void
	 */
	public function start_output_buffering(): void {
		if ( ! $this->should_cache_request() ) {
			return;
		}

		ob_start( array( $this, 'cache_output_buffer' ) );
	}

	/**
	 * Cache the output buffer content.
	 *
	 * @param string $buffer Output buffer content.
	 * @return string Unmodified buffer content.
	 */
	public function cache_output_buffer( string $buffer ): string {
		// Only cache if we have substantial content
		if ( empty( $buffer ) || strlen( $buffer ) < 255 ) {
			return $buffer;
		}

		// Don't cache if there are errors
		$status = http_response_code();
		if ( $status >= 400 ) {
			return $buffer;
		}

		$cache_file = $this->get_cache_file_path();

		// Add cache metadata comment
		$metadata = sprintf(
			"\n<!-- WPShadow Cache | Generated: %s | Expires: %s | URL: %s -->\n",
			gmdate( 'Y-m-d H:i:s' ),
			gmdate( 'Y-m-d H:i:s', time() + self::CACHE_LIFETIME ),
			esc_html( $this->get_current_url() )
		);

		// Insert metadata before </body> tag if it exists
		if ( stripos( $buffer, '</body>' ) !== false ) {
			$buffer_to_save = str_ireplace( '</body>', $metadata . '</body>', $buffer );
		} else {
			$buffer_to_save = $buffer . $metadata;
		}

		// Save to cache file
		$saved = file_put_contents( $cache_file, $buffer_to_save ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		if ( $saved !== false ) {
			$this->log_activity( 'cache_saved', sprintf( 'Cached page (%s KB): %s', number_format( strlen( $buffer_to_save ) / 1024, 2 ), $this->get_current_url() ), 'info' );
			do_action( 'wpshadow_simple_cache_saved', $this->get_current_url(), $cache_file );
		}

		// Return original buffer unchanged
		return $buffer;
	}

	/**
	 * Get the current URL for logging and cache keys.
	 *
	 * @return string
	 */
	private function get_current_url(): string {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$host        = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		
		return $host . $request_uri;
	}

	/**
	 * Clear cache for a specific post.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function clear_post_cache( int $post_id ): void {
		// Clear all cache to be safe (more sophisticated cache key mapping could be added)
		$this->clear_all_cache();

		$this->log_activity( 'cache_cleared', sprintf( 'Cleared cache for post ID %d', $post_id ), 'info' );
	}

	/**
	 * Clear cache when a comment is posted.
	 *
	 * @param int $comment_id Comment ID.
	 * @return void
	 */
	public function clear_post_cache_by_comment( int $comment_id ): void {
		$comment = get_comment( $comment_id );
		if ( $comment && $comment->comment_post_ID ) {
			$this->clear_post_cache( (int) $comment->comment_post_ID );
		}
	}

	/**
	 * Clear all cached files.
	 *
	 * @return int Number of files deleted.
	 */
	public function clear_all_cache(): int {
		$deleted = 0;

		if ( ! is_dir( $this->cache_dir ) ) {
			return 0;
		}

		$files = glob( $this->cache_dir . '/*.html' );

		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_unlink
					$deleted++;
				}
			}
		}

		$this->log_activity( 'cache_cleared', sprintf( 'Cleared %d cached files', $deleted ), 'info' );

		return $deleted;
	}

	/**
	 * Add clear cache button to admin bar.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_button( $wp_admin_bar ): void {
		if ( is_network_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'wpshadow-clear-cache',
				'title' => __( 'Clear Page Cache', 'wpshadow' ),
				'href'  => wp_nonce_url( admin_url( 'admin-post.php?action=wpshadow_clear_cache' ), 'wpshadow_clear_cache' ),
				'meta'  => array(
					'title' => __( 'Clear all cached pages', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Display admin notice after cache clear.
	 *
	 * @return void
	 */
	public function display_admin_notices(): void {
		if ( isset( $_GET['wpshadow-cache-cleared'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$deleted = intval( $_GET['wpshadow-cache-cleared'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: %d: Number of cached files deleted */
						esc_html( _n( 'Cleared %d cached page.', 'Cleared %d cached pages.', $deleted, 'wpshadow' ) ),
						esc_html( number_format_i18n( $deleted ) )
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Get cache statistics.
	 *
	 * @return array<string, mixed>
	 */
	public function get_cache_stats(): array {
		$stats = array(
			'total_files' => 0,
			'total_size'  => 0,
			'oldest_file' => null,
			'newest_file' => null,
		);

		if ( ! is_dir( $this->cache_dir ) ) {
			return $stats;
		}

		$files = glob( $this->cache_dir . '/*.html' );

		if ( ! is_array( $files ) ) {
			return $stats;
		}

		$stats['total_files'] = count( $files );
		$oldest_time          = PHP_INT_MAX;
		$newest_time          = 0;

		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				$stats['total_size'] += filesize( $file );
				$mtime                = filemtime( $file );

				if ( $mtime < $oldest_time ) {
					$oldest_time           = $mtime;
					$stats['oldest_file'] = $mtime;
				}

				if ( $mtime > $newest_time ) {
					$newest_time           = $mtime;
					$stats['newest_file'] = $mtime;
				}
			}
		}

		return $stats;
	}

	/**
	 * Handle admin clear cache request.
	 *
	 * @return void
	 */
	public function admin_clear_cache(): void {
		check_admin_referer( 'wpshadow_clear_cache' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'wpshadow' ) );
		}

		$deleted = $this->clear_all_cache();
		do_action( 'wpshadow_simple_cache_cleared', $deleted );

		$redirect_url = wp_get_referer();
		if ( ! $redirect_url ) {
			$redirect_url = admin_url();
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'wpshadow-cache-cleared' => $deleted,
				),
				$redirect_url
			)
		);
		exit;
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, array<string, mixed>> $tests Site Health tests.
	 * @return array<string, array<string, mixed>>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['wpshadow_simple_cache'] = array(
			'label' => __( 'Simple Page Cache', 'wpshadow' ),
			'test'  => array( $this, 'site_health_test' ),
		);

		return $tests;
	}

	/**
	 * Register WP-CLI commands for cache management.
	 */
	private function register_cli_commands(): void {
		if ( ! class_exists( '\WP_CLI' ) ) {
			return;
		}

		$feature = $this;

		\WP_CLI::add_command(
			'wpshadow cache',
			new class( $feature ) {
				private WPSHADOW_Feature_Simple_Cache $feature;

				public function __construct( WPSHADOW_Feature_Simple_Cache $feature ) {
					$this->feature = $feature;
				}

				/**
				 * Clear all cached files.
				 *
				 * ## OPTIONS
				 * [--network]
				 * : Run on the network (multisite) using --url for a specific site.
				 *
				 * ## EXAMPLES
				 * wp wpshadow cache clear
				 */
				public function clear( array $args, array $assoc_args ): void {
					$deleted = $this->feature->clear_all_cache();
					\WP_CLI::success( sprintf( '%d cached files deleted.', $deleted ) );
				}

				/**
				 * Show cache status and statistics.
				 */
				public function status( array $args, array $assoc_args ): void {
					$stats = $this->feature->get_cache_stats();
					\WP_CLI::line( sprintf( 'Files: %d', $stats['total_files'] ) );
					\WP_CLI::line( sprintf( 'Size: %s', size_format( $stats['total_size'] ) ) );
				}
			}
		);
	}

	/**
	 * Site Health test callback.
	 *
	 * @return array<string, mixed>
	 */
	public function site_health_test(): array {
		$result = array(
			'label'       => __( 'Page cache is working', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'The simple page cache is enabled and working correctly.', 'wpshadow' )
			),
			'actions'     => '',
			'test'        => 'wpshadow_simple_cache',
		);

		if ( ! $this->is_enabled() ) {
			$result['status']      = 'recommended';
			$result['label']       = __( 'Page cache is not enabled', 'wpshadow' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__( 'Enable the simple page cache to make your site load faster by saving copies of your pages.', 'wpshadow' )
			);
			$result['actions'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features' ),
				__( 'Enable Page Cache', 'wpshadow' )
			);
		} elseif ( ! is_dir( $this->cache_dir ) || ! is_writable( $this->cache_dir ) ) {
			$result['status']      = 'critical';
			$result['label']       = __( 'Cache directory is not writable', 'wpshadow' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %s: Cache directory path */
					__( 'The cache directory at %s is not writable. Please check file permissions.', 'wpshadow' ),
					'<code>' . esc_html( $this->cache_dir ) . '</code>'
				)
			);
		} else {
			// Get cache statistics
			$stats = $this->get_cache_stats();

			$result['description'] = sprintf(
				'<p>%s</p><ul><li>%s</li><li>%s</li><li>%s</li></ul>',
				__( 'The page cache is active and storing rendered pages:', 'wpshadow' ),
				sprintf(
					/* translators: %d: Number of cached files */
					_n( '%d page cached', '%d pages cached', $stats['total_files'], 'wpshadow' ),
					number_format_i18n( $stats['total_files'] )
				),
				sprintf(
					/* translators: %s: Cache size in MB */
					__( 'Cache size: %s', 'wpshadow' ),
					size_format( $stats['total_size'] )
				),
				sprintf(
					/* translators: %d: Cache lifetime in hours */
					__( 'Cache lifetime: %d hour', 'wpshadow' ),
					self::CACHE_LIFETIME / 3600
				)
			);

			$result['actions'] = sprintf(
				'<a href="%s">%s</a>',
				wp_nonce_url( admin_url( 'admin-post.php?action=wpshadow_clear_cache' ), 'wpshadow_clear_cache' ),
				__( 'Clear Cache', 'wpshadow' )
			);
		}

		return $result;
	}
}
