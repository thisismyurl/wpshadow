<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Noindex_Tag extends Diagnostic_Base {


	protected static $slug        = 'test-seo-noindex-tag';
	protected static $title       = 'Noindex Tag Test';
	protected static $description = 'Tests for accidental noindex on important pages';

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
		// Check for noindex in meta robots
		$has_noindex = preg_match( '/<meta[^>]+name=["\']robots["\'][^>]+content=["\'][^"\']*noindex/i', $html );

		// Check if this is homepage or important page
		$is_homepage = trailingslashit( $checked_url ) === trailingslashit( home_url( '/' ) );

		if ( $has_noindex && $is_homepage ) {
			return array(
				'id'            => 'seo-homepage-noindex',
				'title'         => 'Homepage Has Noindex Tag',
				'description'   => 'Your homepage has a noindex meta tag, preventing it from appearing in search results. This is likely unintentional and will harm your SEO.'
				'kb_link' => 'https://wpshadow.com/kb/noindex-tag/',
				'training_link' => 'https://wpshadow.com/training/indexability/',
				'auto_fixable'  => false,
				'threat_level'  => 80,
				'module'        => 'SEO',
				'priority'      => 1,
				'meta'          => array(
					'has_noindex' => true,
					'is_homepage' => true,
					'checked_url' => $checked_url,
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
		return __( 'Noindex Tag Detection', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for accidental noindex on important pages.', 'wpshadow' );
	}
}
