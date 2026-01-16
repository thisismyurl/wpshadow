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

		// Force lazy loading for all images.
		add_filter( 'wp_lazy_loading_enabled', array( $this, 'enable_lazy_loading' ), 10, 2 );

		// Add loading attribute to content images.
		add_filter( 'the_content', array( $this, 'add_loading_attribute_to_images' ), 20 );

		// Add loading attribute to post thumbnails.
		add_filter( 'post_thumbnail_html', array( $this, 'add_loading_lazy' ), 10, 1 );

		// Add loading attribute to avatar images.
		add_filter( 'get_avatar', array( $this, 'add_loading_lazy' ), 10, 1 );
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
}
