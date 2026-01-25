<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_CSS_File_Count extends Diagnostic_Base {


	protected static $slug        = 'test-performance-css-file-count';
	protected static $title       = 'CSS File Count Test';
	protected static $description = 'Tests for too many CSS files on a page.';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$body = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		if ( $body === false ) {
			return null;
		}

		preg_match_all( '/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', $body, $stylesheets );
		$css_file_count = count( $stylesheets[0] );

		if ( $css_file_count > 10 ) {
			return array(
				'id'            => 'performance-excessive-css-files',
				'title'         => 'Excessive CSS Files',
				'description'   => sprintf( '%d CSS files detected. Each requires HTTP request. Consider combining/concatenating stylesheets.', $css_file_count )
				'kb_link' => 'https://wpshadow.com/kb/css-optimization/',
				'training_link' => 'https://wpshadow.com/training/asset-optimization/',
				'auto_fixable'  => false,
				'threat_level'  => 40,
				'module'        => 'Performance',
				'priority'      => 3,
				'meta'          => array( 'css_file_count' => $css_file_count ),
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
		return __( 'CSS File Count', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks how many CSS files are loaded on the page.', 'wpshadow' );
	}
}
