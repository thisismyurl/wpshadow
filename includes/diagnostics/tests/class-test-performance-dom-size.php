<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_DOM_Size extends Diagnostic_Base {


	protected static $slug        = 'test-performance-dom-size';
	protected static $title       = 'DOM Size Test';
	protected static $description = 'Tests for excessive DOM elements';

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
		// Count DOM elements (rough approximation)
		preg_match_all( '/<[a-z][a-z0-9]*[^>]*>/i', $html, $elements );
		$element_count = count( $elements[0] );

		// Check DOM depth (nested divs as proxy)
		$max_nesting = 0;
		if ( preg_match_all( '/<div[^>]*>/i', $html, $div_opens ) ) {
			// Very rough nesting estimate
			$max_nesting = min( count( $div_opens[0] ), 30 );
		}

		$threat_level = 25;
		$severity     = 'moderate';

		if ( $element_count > 1500 ) {
			$threat_level = 60;
			$severity     = 'excessive';
		} elseif ( $element_count > 1000 ) {
			$threat_level = 40;
			$severity     = 'high';
		}

		if ( $element_count > 800 ) {
			return array(
				'id'            => 'performance-large-dom',
				'title'         => sprintf( 'Large DOM Size (%s)', ucfirst( $severity ) ),
				'description'   => sprintf( '%d DOM elements detected. Google recommends <800 elements. Large DOMs slow rendering and increase memory usage.', $element_count ),
				'kb_link'       => 'https://wpshadow.com/kb/dom-size/',
				'training_link' => 'https://wpshadow.com/training/html-optimization/',
				'auto_fixable'  => false,
				'threat_level'  => $threat_level,
				'module'        => 'Performance',
				'priority'      => 3,
				'meta'          => array(
					'element_count'        => $element_count,
					'max_nesting_estimate' => $max_nesting,
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
		return __( 'DOM Size', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for excessive DOM elements.', 'wpshadow' );
	}
}
