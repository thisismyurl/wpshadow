<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Alt_Text_Quality extends Diagnostic_Base {


	protected static $slug        = 'test-content-alt-text-quality';
	protected static $title       = 'Alt Text Quality Test';
	protected static $description = 'Tests for meaningful alt text on images';

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
		// Find all images
		preg_match_all( '/<img[^>]+>/i', $html, $all_images );
		$total_images = count( $all_images[0] );

		if ( $total_images === 0 ) {
			return null;
		}

		$poor_alt_count   = 0;
		$generic_patterns = '/^(image|photo|picture|img|icon|logo|banner|\d+|screenshot|untitled|dsc|img_\d+)$/i';

		foreach ( $all_images[0] as $img ) {
			// Extract alt attribute
			if ( preg_match( '/alt=["\']([^"\']*)["\']/', $img, $alt_match ) ) {
				$alt_text = trim( $alt_match[1] );

				// Check for poor quality alt text
				if (
					empty( $alt_text ) ||
					preg_match( $generic_patterns, $alt_text ) ||
					strlen( $alt_text ) < 5 ||
					preg_match( '/\.(jpg|jpeg|png|gif|webp)$/i', $alt_text )
				) {
					++$poor_alt_count;
				}
			}
		}

		if ( $poor_alt_count > 2 && ( $poor_alt_count / $total_images ) > 0.3 ) {
			return array(
				'id'            => 'content-poor-alt-text',
				'title'         => 'Poor Quality Alt Text',
				'description'   => sprintf(
					'%d of %d images have poor/generic alt text (e.g., "image", "photo", "DSC_1234"). Alt text should describe the image content.',
					$poor_alt_count,
					$total_images
				)
				'kb_link' => 'https://wpshadow.com/kb/alt-text-best-practices/',
				'training_link' => 'https://wpshadow.com/training/accessibility/',
				'auto_fixable'  => false,
				'threat_level'  => 40,
				'module'        => 'Content Quality',
				'priority'      => 2,
				'meta'          => array(
					'poor_alt_count' => $poor_alt_count,
					'total_images'   => $total_images,
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
		return __( 'Alt Text Quality', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for meaningful alt text on images.', 'wpshadow' );
	}
}
