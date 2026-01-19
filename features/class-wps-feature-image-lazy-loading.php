<?php declare(strict_types=1);
/**
 * Feature: Enhanced Image Lazy Loading
 *
 * Adds flexible lazy loading controls for images, iframes, avatars, and thumbnails
 * while allowing the first content image to stay eager when desired.
 *
 * @package WPShadow\CoreSupport
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Image_Lazy_Loading extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'image-lazy-loading',
				'name'               => __( 'Enhanced Image Lazy Loading', 'wpshadow' ),
				'description'        => __( 'Enable native lazy loading for images, thumbnails, avatars, and iframes to reduce initial payload.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'seo-social-media',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-format-image',
				'category'           => 'performance',
				'priority'           => 25,
				'sub_features'       => array(
					'lazy_images'         => array(
						'name'            => __( 'Lazy Load Images', 'wpshadow' ),
						'description'     => __( 'Add loading="lazy" to content images.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'lazy_iframes'        => array(
						'name'            => __( 'Lazy Load Iframes', 'wpshadow' ),
						'description'     => __( 'Enable lazy loading on iframes.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'lazy_avatars'        => array(
						'name'            => __( 'Lazy Load Avatars', 'wpshadow' ),
						'description'     => __( 'Add lazy loading to avatar images.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'lazy_thumbnails'     => array(
						'name'            => __( 'Lazy Load Post Thumbnails', 'wpshadow' ),
						'description'     => __( 'Add lazy loading to featured images.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'exclude_first_image' => array(
						'name'            => __( 'Exclude First Content Image', 'wpshadow' ),
						'description'     => __( 'Keep the first content image eager for LCP.', 'wpshadow' ),
						'default_enabled' => false,
					),
				),
			)
		);
	}

	public function has_details_page(): bool {
		return true;
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$enable_images     = $this->is_sub_feature_enabled( 'lazy_images', true );
		$enable_iframes    = $this->is_sub_feature_enabled( 'lazy_iframes', true );
		$enable_avatars    = $this->is_sub_feature_enabled( 'lazy_avatars', true );
		$enable_thumbnails = $this->is_sub_feature_enabled( 'lazy_thumbnails', true );

		if ( $enable_images || $enable_iframes ) {
			add_filter( 'wp_lazy_loading_enabled', array( $this, 'enable_lazy_loading' ), 10, 2 );
		}

		if ( $enable_images ) {
			add_filter( 'the_content', array( $this, 'add_loading_attribute_to_images' ), 20 );
		}

		if ( $enable_thumbnails ) {
			add_filter( 'post_thumbnail_html', array( $this, 'add_loading_lazy' ), 10, 1 );
		}

		if ( $enable_avatars ) {
			add_filter( 'get_avatar', array( $this, 'add_loading_lazy' ), 10, 1 );
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	public function enable_lazy_loading( bool $default, string $tag_name ): bool {
		$enable_images  = $this->is_sub_feature_enabled( 'lazy_images', true );
		$enable_iframes = $this->is_sub_feature_enabled( 'lazy_iframes', true );

		if ( 'img' === $tag_name ) {
			return $enable_images || $default;
		}

		if ( 'iframe' === $tag_name ) {
			return $enable_iframes || $default;
		}

		return $default;
	}

	public function add_loading_attribute_to_images( string $content ): string {
		if ( '' === $content ) {
			return $content;
		}

		$skip_first = $this->is_sub_feature_enabled( 'exclude_first_image', false );
		$seen       = 0;

		return (string) preg_replace_callback(
			'/<img([^>]+?)\/>|<img([^>]+?)>/',
			function ( array $matches ) use ( $skip_first, &$seen ) {
				$img_tag = $matches[0];
				++$seen;

				if ( $skip_first && 1 === $seen ) {
					return $img_tag;
				}

				if ( strpos( $img_tag, 'loading=' ) !== false ) {
					return $img_tag;
				}

				return str_replace( '<img', '<img loading="lazy"', $img_tag );
			},
			$content
		);
	}

	public function add_loading_lazy( string $html ): string {
		if ( '' === $html || strpos( $html, 'loading=' ) !== false ) {
			return $html;
		}

		return str_replace( '<img', '<img loading="lazy"', $html );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_image_lazy_loading'] = array(
			'label' => __( 'Image Lazy Loading', 'wpshadow' ),
			'test'  => array( $this, 'test_image_lazy_loading' ),
		);
		return $tests;
	}

	public function test_image_lazy_loading(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Image Lazy Loading', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'orange',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Image Lazy Loading is not enabled. Enabling lazy loading can improve page load times by deferring offscreen images.', 'wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_image_lazy_loading',
			);
		}

		$enabled = 0;
		$enabled += $this->is_sub_feature_enabled( 'lazy_images', true ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'lazy_iframes', true ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'lazy_avatars', true ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'lazy_thumbnails', true ) ? 1 : 0;

		return array(
			'label'       => __( 'Image Lazy Loading', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled lazy loading options */
					__( 'Lazy loading is active with %d optimizations enabled.', 'wpshadow' ),
					$enabled
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_image_lazy_loading',
		);
	}
}
