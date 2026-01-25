<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Responsive_Images extends Diagnostic_Base {


	protected static $slug        = 'test-mobile-responsive-images';
	protected static $title       = 'Responsive Images Test';
	protected static $description = 'Tests for responsive image techniques (srcset, sizes)';

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
		// Count all images
		preg_match_all( '/<img[^>]+>/i', $html, $all_images );
		$total_images = count( $all_images[0] );

		if ( $total_images === 0 ) {
			return null; // No images
		}

		// Count images with srcset or picture element
		preg_match_all( '/<img[^>]+srcset=/i', $html, $srcset_images );
		preg_match_all( '/<picture[^>]*>/i', $html, $picture_elements );

		$responsive_count = count( $srcset_images[0] ) + count( $picture_elements[0] );
		$percentage       = round( ( $responsive_count / $total_images ) * 100 );

		if ( $percentage >= 80 ) {
			return null; // PASS - most images are responsive
		}

		$threat_level = 35;
		if ( $percentage < 30 ) {
			$threat_level = 55;
		}

		return array(
			'id'            => 'mobile-responsive-images',
			'title'         => 'Missing Responsive Images',
			'description'   => sprintf(
				'Only %d%% of images (%d/%d) use responsive techniques (srcset/picture). Responsive images reduce bandwidth on mobile devices.',
				$percentage,
				$responsive_count,
				$total_images
			)
			'kb_link' => 'https://wpshadow.com/kb/responsive-images/',
			'training_link' => 'https://wpshadow.com/training/mobile-performance/',
			'auto_fixable'  => false,
			'threat_level'  => $threat_level,
			'module'        => 'Mobile',
			'priority'      => 2,
			'meta'          => array(
				'total_images'      => $total_images,
				'responsive_images' => $responsive_count,
				'percentage'        => $percentage,
				'checked_url'       => $checked_url,
			),
		);
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
		return __( 'Responsive Images', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for srcset/picture for responsive images.', 'wpshadow' );
	}
}
