<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Google_Fonts_Display extends Diagnostic_Base {


	protected static $slug        = 'test-performance-google-fonts-display';
	protected static $title       = 'Google Fonts Display Parameter Test';
	protected static $description = 'Tests Google Fonts URLs for display parameter.';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$body = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		if ( $body === false ) {
			return null;
		}

		$has_google_fonts  = preg_match( '/fonts\.googleapis\.com/i', $body );
		$has_display_param = preg_match( '/fonts\.googleapis\.com[^"\']*[?&]display=(swap|fallback|optional)/i', $body );

		if ( $has_google_fonts && ! $has_display_param ) {
			return array(
				'id'            => 'performance-google-fonts-no-display',
				'title'         => 'Google Fonts Missing Display Parameter',
				'description'   => 'Google Fonts loaded without &display=swap parameter. Can cause text to be invisible during font load.'
				'kb_link' => 'https://wpshadow.com/kb/google-fonts-optimization/',
				'training_link' => 'https://wpshadow.com/training/font-optimization/',
				'auto_fixable'  => false,
				'threat_level'  => 30,
				'module'        => 'Performance',
				'priority'      => 3,
				'meta'          => array( 'has_google_fonts' => true ),
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
		return __( 'Google Fonts Display Parameter', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks that Google Fonts requests include a display parameter.', 'wpshadow' );
	}
}
