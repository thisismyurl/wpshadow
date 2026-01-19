<?php declare(strict_types=1);
/**
 * Feature: Enhanced Image Lazy Loading
 *
 * Auto-enable WordPress native lazy loading for images, iframes, avatars.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class WPSHADOW_Feature_Image_Lazy_Loading extends WPSHADOW_Abstract_Feature {

	/**
	 * Image counter for excluding first image.
	 *
	 * @var int
	 */
	private int $image_count = 0;

	public function __construct() {
		parent::__construct( array(
			'id'          => 'image-lazy-loading',
			'name'        => __( 'Load Images As Needed', 'wpshadow' ),
			'description' => __( 'Only load pictures when people scroll down to see them. Makes pages appear faster, especially on slow connections.', 'wpshadow' ),
			'aliases'     => array( 'lazy load', 'image optimization', 'defer images', 'loading attribute', 'image performance', 'lazy loading images', 'defer loading', 'image speed', 'page speed', 'lazy iframes', 'image delay', 'deferred images' ),
			'sub_features' => array(
				'lazy_images'        => array(
					'name'               => __( 'Lazy Load Images', 'wpshadow' ),
					'description_short'  => __( 'Delay loading images until needed', 'wpshadow' ),
					'description_long'   => __( 'Delays loading images until they become visible in the viewport when users scroll down. This is a standard web performance optimization that significantly speeds up initial page load, especially on pages with many images. Images below the fold don\'t load until needed, saving bandwidth for users who don\'t scroll all the way down.', 'wpshadow' ),
					'description_wizard' => __( 'Speed up page load by waiting to load images until they\'re about to appear on screen. Saves bandwidth and makes pages feel faster.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'lazy_iframes'       => array(
					'name'               => __( 'Lazy Load iFrames', 'wpshadow' ),
					'description_short'  => __( 'Delay loading embedded videos and content', 'wpshadow' ),
					'description_long'   => __( 'Delays loading of iframes (embedded videos, maps, social media widgets) until they\'re visible on screen. Embedded content often requires additional HTTP requests and JavaScript execution that can slow page load. Lazy loading iframes significantly improves performance, especially pages with multiple videos or embeds.', 'wpshadow' ),
					'description_wizard' => __( 'Embedded videos and widgets slow down initial page load. Load them only when users scroll down to see them.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'lazy_avatars'       => array(
					'name'               => __( 'Lazy Load User Avatars', 'wpshadow' ),
					'description_short'  => __( 'Delay loading user profile pictures', 'wpshadow' ),
					'description_long'   => __( 'Delays loading of user avatars and profile pictures, which often come from external services like Gravatar. Comment sections and user lists can have many avatars. Lazy loading them reduces initial page load time and external requests. Users will still see all avatars - they just load when needed rather than blocking page rendering.', 'wpshadow' ),
					'description_wizard' => __( 'User avatars and profile pictures slow down page load, especially when they come from external services. Load them lazily to speed up the page.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'lazy_thumbnails'    => array(
					'name'               => __( 'Lazy Load Thumbnails', 'wpshadow' ),
					'description_short'  => __( 'Delay loading featured/post images', 'wpshadow' ),
					'description_long'   => __( 'Delays loading of featured images and post thumbnails that appear in archives, blog listings, and related posts sections. These images often appear below-the-fold and can significantly slow down initial page display. Lazy loading them speeds up perceived page load time without affecting visual quality.', 'wpshadow' ),
					'description_wizard' => __( 'Archive page thumbnails don\'t need to load immediately. Enable lazy loading to make listing pages feel faster.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'exclude_first_image' => array(
					'name'               => __( 'Exclude First Image', 'wpshadow' ),
					'description_short'  => __( 'Always load first image immediately', 'wpshadow' ),
					'description_long'   => __( 'Always loads the first image on a page immediately instead of lazily. The first image is usually the hero or featured image that\'s visible above-the-fold (without scrolling). Loading it immediately improves perceived page load time since visitors see the main image instantly. This is an above-the-fold optimization best practice.', 'wpshadow' ),
					'description_wizard' => __( 'The first image is usually visible without scrolling. Loading it immediately improves how fast the page appears to load. Recommended for best perceived performance.', 'wpshadow' ),
					'default_enabled'    => false,
				),
			),
		) );

		$this->register_default_settings( array(
			'lazy_images'        => true,
			'lazy_iframes'       => true,
			'lazy_avatars'       => true,
			'lazy_thumbnails'    => true,
			'exclude_first_image' => false,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		// Enable lazy loading for images
		if ( $this->is_sub_feature_enabled( 'lazy_images', true ) || $this->is_sub_feature_enabled( 'lazy_iframes', true ) ) {
			add_filter( 'wp_lazy_loading_enabled', array( $this, 'enable_lazy_loading' ), 10, 2 );
		}

		// Add loading attribute to content images
		if ( $this->is_sub_feature_enabled( 'lazy_images', true ) ) {
			add_filter( 'the_content', array( $this, 'add_loading_to_images' ), 20 );
		}

		// Add lazy loading to thumbnails
		if ( $this->is_sub_feature_enabled( 'lazy_thumbnails', true ) ) {
			add_filter( 'post_thumbnail_html', array( $this, 'add_loading_lazy' ) );
		}

		// Add lazy loading to avatars
		if ( $this->is_sub_feature_enabled( 'lazy_avatars', true ) ) {
			add_filter( 'get_avatar', array( $this, 'add_loading_lazy' ) );
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow image-lazy-loading', array( $this, 'handle_cli_command' ) );
		}
	}

	/**
	 * Enable lazy loading.
	 */
	public function enable_lazy_loading( bool $default, string $tag_name ): bool {
		if ( in_array( $tag_name, array( 'img', 'iframe' ), true ) ) {
			return true;
		}
		return $default;
	}

	/**
	 * Add loading="lazy" to images in content.
	 */
	public function add_loading_to_images( string $content ): string {
		if ( empty( $content ) ) {
			return $content;
		}

		// Reset counter for each content block.
		$this->image_count = 0;

		return preg_replace_callback(
			'/<img([^>]+?)\/?>/',
			function ( $matches ) {
				if ( strpos( $matches[0], 'loading=' ) !== false ) {
					return $matches[0];
				}

				$this->image_count++;

				// Skip first image if enabled (above-the-fold optimization).
				if ( $this->image_count === 1 && $this->is_sub_feature_enabled( 'exclude_first_image', false ) ) {
					do_action( 'wpshadow_lazy_loading_skipped_first_image', $matches[0] );
					return $matches[0];
				}

				$updated = str_replace( '<img', '<img loading="lazy"', $matches[0] );
				do_action( 'wpshadow_lazy_loading_applied', $updated );
				return $updated;
			},
			$content
		);
	}

	/**
	 * Add loading="lazy" attribute.
	 */
	public function add_loading_lazy( string $html ): string {
		if ( empty( $html ) || strpos( $html, 'loading=' ) !== false ) {
			return $html;
		}
		$updated = str_replace( '<img', '<img loading="lazy"', $html );
		do_action( 'wpshadow_lazy_loading_applied', $updated );
		return $updated;
	}

	/**
	 * Handle WP-CLI command for image lazy loading.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args (unused).
	 *
	 * @return void
	 */
	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' !== $action ) {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow image-lazy-loading status', 'wpshadow' ) );
			return;
		}

		\WP_CLI::log( __( 'Image Lazy Loading status:', 'wpshadow' ) );
		\WP_CLI::log( sprintf( '  %s: %s', __( 'Feature enabled', 'wpshadow' ), $this->is_enabled() ? 'yes' : 'no' ) );

		$subs = array(
			'lazy_images',
			'lazy_iframes',
			'lazy_avatars',
			'lazy_thumbnails',
			'exclude_first_image',
		);

		foreach ( $subs as $sub ) {
			$enabled = $this->is_sub_feature_enabled( $sub, false );
			\WP_CLI::log( sprintf( '  - %s: %s', $sub, $enabled ? 'on' : 'off' ) );
		}

		\WP_CLI::success( __( 'Image lazy loading inspected.', 'wpshadow' ) );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['image_lazy'] = array(
			'label'  => __( 'Image Lazy Loading', 'wpshadow' ),
			'test'   => array( $this, 'test_lazy_loading' ),
		);

		return $tests;
	}

	public function test_lazy_loading(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Image Lazy Loading', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable image lazy loading for better performance.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'image_lazy',
			);
		}

		$enabled_count = 0;
		$subs = array( 'lazy_images', 'lazy_iframes', 'lazy_avatars', 'lazy_thumbnails', 'exclude_first_image' );
		foreach ( $subs as $sub ) {
			if ( $this->is_sub_feature_enabled( $sub, false ) ) {
				$enabled_count++;
			}
		}

		return array(
			'label'       => __( 'Image Lazy Loading', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d of 5 lazy loading options enabled.', 'wpshadow' ),
				$enabled_count
			),
			'actions'     => '',
			'test'        => 'image_lazy',
		);
	}
}
