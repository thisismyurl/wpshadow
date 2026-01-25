<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Image_Format extends Diagnostic_Base {


	protected static $slug        = 'test-performance-image-format';
	protected static $title       = 'Modern Image Format Test';
	protected static $description = 'Tests for modern image formats (WebP, AVIF)';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		if ( $html !== null ) {
			return self::analyze_html( $html, $url ?? 'provided-html' );
		}

		$html = self::fetch_html( $url ?? home_url( '/' ) );
		if ( $html === false ) {
			return null;
		}

		return self::analyze_html( $html, $url ?? home_url( '/' ) );
	}

	protected static function analyze_html( string $html, string $checked_url ): ?array {
		// Count total images
		preg_match_all( '/<img[^>]+src=["\']([^"\']+\.(jpg|jpeg|png|gif))["\']/i', $html, $traditional_images );
		$traditional_count = count( $traditional_images[0] );

		// Count modern formats
		preg_match_all( '/<img[^>]+src=["\']([^"\']+\.(webp|avif))["\']/i', $html, $modern_images );
		preg_match_all( '/<picture[^>]*>/i', $html, $picture_elements );

		$modern_count = count( $modern_images[0] ) + count( $picture_elements[0] );

		if ( $traditional_count > 5 && $modern_count === 0 ) {
			return array(
				'id'            => 'performance-no-modern-images',
				'title'         => 'No Modern Image Formats',
				'description'   => sprintf( '%d images found using traditional formats (JPG/PNG). WebP images are 25-35%% smaller with same quality.', $traditional_count )
				'kb_link' => 'https://wpshadow.com/kb/webp-images/',
				'training_link' => 'https://wpshadow.com/training/image-optimization/',
				'auto_fixable'  => false,
				'threat_level'  => 40,
				'module'        => 'Performance',
				'priority'      => 2,
				'meta'          => array(
					'traditional_count' => $traditional_count,
					'modern_count'      => $modern_count,
				),
			);
		}

		return null;
	}

	protected static function fetch_html( string $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
			)
		);
		return is_wp_error( $response ) ? false : wp_remote_retrieve_body( $response );
	}

	public static function get_name(): string {
		return __( 'Modern Image Formats', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for modern image formats (WebP, AVIF).', 'wpshadow' );
	}
}
