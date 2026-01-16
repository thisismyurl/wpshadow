<?php
/**
 * Feature: Enhanced Image Lazy Loading
 *
 * Auto-enable and enhance WordPress native lazy loading for images.
 * Extends WordPress 5.5+ native lazy loading with additional optimization.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Image_Lazy_Loading
 *
 * Enhanced image lazy loading for better performance.
 */
final class WPSHADOW_Feature_Image_Lazy_Loading extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'image-lazy-loading',
				'name'               => __( 'Enhanced Image Lazy Loading', 'plugin-wpshadow' ),
				'description'        => __( 'Enables native lazy loading on images throughout your site so browsers delay downloading offscreen images until visitors scroll near them. Cuts initial page weight, speeds the first paint, and saves bandwidth for mobile users. Works automatically with existing image tags, respects critical images you mark to load eagerly, and pairs well with compression or CDNs for even faster visual delivery.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
			'widget_group'       => 'image-optimization',
			'widget_label'       => __( 'Image Optimization', 'plugin-wpshadow' ),
				'widget_description' => __( 'Optimize images and page load performance', 'plugin-wpshadow' ),
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'lazy_images'        => __( 'Lazy Load Images', 'plugin-wpshadow' ),
					'lazy_iframes'       => __( 'Lazy Load Iframes', 'plugin-wpshadow' ),
					'lazy_avatars'       => __( 'Lazy Load Avatars', 'plugin-wpshadow' ),
					'lazy_thumbnails'    => __( 'Lazy Load Post Thumbnails', 'plugin-wpshadow' ),
					'exclude_first_image' => __( 'Exclude First Content Image', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'lazy_images'        => true,
						'lazy_iframes'       => true,
						'lazy_avatars'       => true,
						'lazy_thumbnails'    => true,
						'exclude_first_image' => false,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'Image Lazy Loading feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
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

		// Force lazy loading for all images and iframes if enabled.
		if ( get_option( 'wpshadow_image-lazy-loading_lazy_images', true ) || get_option( 'wpshadow_image-lazy-loading_lazy_iframes', true ) ) {
			add_filter( 'wp_lazy_loading_enabled', array( $this, 'enable_lazy_loading' ), 10, 2 );
		}

		// Add loading attribute to content images if enabled.
		if ( get_option( 'wpshadow_image-lazy-loading_lazy_images', true ) ) {
			add_filter( 'the_content', array( $this, 'add_loading_attribute_to_images' ), 20 );
		}

		// Add loading attribute to post thumbnails if enabled.
		if ( get_option( 'wpshadow_image-lazy-loading_lazy_thumbnails', true ) ) {
			add_filter( 'post_thumbnail_html', array( $this, 'add_loading_lazy' ), 10, 1 );
		}

		// Add loading attribute to avatar images if enabled.
		if ( get_option( 'wpshadow_image-lazy-loading_lazy_avatars', true ) ) {
			add_filter( 'get_avatar', array( $this, 'add_loading_lazy' ), 10, 1 );
		}
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Enable lazy loading for all supported tag names and contexts.
	 *
	 * @param bool   $default Default lazy loading value.
	 * @param string $tag_name Tag name (img, iframe).
	 * @return bool
	 */
	public function enable_lazy_loading( bool $default, string $tag_name ): bool {
		// Always enable for images and iframes.
		if ( in_array( $tag_name, array( 'img', 'iframe' ), true ) ) {
			return true;
		}

		return $default;
	}

	/**
	 * Add loading="lazy" attribute to images in content.
	 *
	 * @param string $content Post content.
	 * @return string Modified content.
	 */
	public function add_loading_attribute_to_images( string $content ): string {
		// Skip if empty content.
		if ( empty( $content ) ) {
			return $content;
		}

		// Add loading="lazy" to img tags that don't already have it.
		$content = preg_replace_callback(
			'/<img([^>]+?)\/?>/',
			function ( $matches ) {
				$img_tag = $matches[0];

				// Skip if already has loading attribute.
				if ( strpos( $img_tag, 'loading=' ) !== false ) {
					return $img_tag;
				}

				// Add loading="lazy" before the closing bracket.
				return str_replace( '<img', '<img loading="lazy"', $img_tag );
			},
			$content
		);

		return $content;
	}

	/**
	 * Add loading="lazy" attribute to HTML string.
	 *
	 * @param string $html HTML string.
	 * @return string Modified HTML.
	 */
	public function add_loading_lazy( string $html ): string {
		// Skip if empty or already has loading attribute.
		if ( empty( $html ) || strpos( $html, 'loading=' ) !== false ) {
			return $html;
		}

		// Add loading="lazy" to img tags.
		return str_replace( '<img', '<img loading="lazy"', $html );
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_image_lazy_loading'] = array(
			'label' => __( 'Image Lazy Loading', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_image_lazy_loading' ),
		);
		return $tests;
	}

	/**
	 * Site Health test for image lazy loading.
	 *
	 * @return array<string, mixed>
	 */
	public function test_image_lazy_loading(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Image Lazy Loading', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'orange',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Image Lazy Loading is not enabled. Enabling lazy loading can improve page load times by deferring offscreen images.', 'plugin-wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_image_lazy_loading',
			);
		}

		// Count enabled sub-features.
		$enabled_features = 0;
		if ( get_option( 'wpshadow_image-lazy-loading_lazy_images', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_image-lazy-loading_lazy_iframes', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_image-lazy-loading_lazy_avatars', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_image-lazy-loading_lazy_thumbnails', true ) ) {
			++$enabled_features;
		}

		return array(
			'label'       => __( 'Image Lazy Loading', 'plugin-wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				/* translators: %d: number of enabled lazy loading features */
				sprintf(
					__( 'Image Lazy Loading is active with %d element types being lazy loaded.', 'plugin-wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_image_lazy_loading',
		);
	}
}
