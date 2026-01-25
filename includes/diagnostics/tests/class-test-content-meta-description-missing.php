<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Meta_Description_Missing extends Diagnostic_Base {


	protected static $slug        = 'test-content-meta-description-missing';
	protected static $title       = 'Meta Description Missing Test';
	protected static $description = 'Tests for missing meta description tag.';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$body = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		if ( $body === false ) {
			return null;
		}

		$meta_description = self::extract_description( $body );

		if ( $meta_description === '' ) {
			return array(
				'id'            => 'content-no-meta-description',
				'title'         => 'Missing Meta Description',
				'description'   => 'No meta description found. Meta descriptions show in search results and can improve click-through rate by 5-15%.'
				'kb_link' => 'https://wpshadow.com/kb/meta-description/',
				'training_link' => 'https://wpshadow.com/training/seo-fundamentals/',
				'auto_fixable'  => false,
				'threat_level'  => 50,
				'module'        => 'Content Quality',
				'priority'      => 2,
				'meta'          => array( 'has_description' => false ),
			);
		}

		return null;
	}

	protected static function extract_description( string $html ): string {
		if ( preg_match( '/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $desc_match ) ) {
			return trim( $desc_match[1] );
		}

		return '';
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
		return __( 'Meta Description Missing', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for presence of a meta description tag.', 'wpshadow' );
	}
}
