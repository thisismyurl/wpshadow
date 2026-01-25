<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Favicon extends Diagnostic_Base {


	protected static $slug        = 'test-wordpress-favicon';
	protected static $title       = 'Favicon Test';
	protected static $description = 'Tests for proper favicon configuration';

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
		// Check for favicon link tags
		$has_favicon = preg_match( '/<link[^>]+rel=["\'](?:icon|shortcut icon|apple-touch-icon)["\']/i', $html );

		if ( ! $has_favicon ) {
			return array(
				'id'            => 'wordpress-no-favicon',
				'title'         => 'No Favicon Configured',
				'description'   => 'No favicon detected. Favicons appear in browser tabs and bookmarks, building brand recognition.'
				'kb_link' => 'https://wpshadow.com/kb/favicon/',
				'training_link' => 'https://wpshadow.com/training/branding/',
				'auto_fixable'  => false,
				'threat_level'  => 25,
				'module'        => 'WordPress',
				'priority'      => 3,
				'meta'          => array( 'has_favicon' => false ),
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
		return __( 'Favicon', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for proper favicon configuration.', 'wpshadow' );
	}
}
