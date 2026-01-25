<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Person extends Diagnostic_Base {


	protected static $slug        = 'test-schema-person';
	protected static $title       = 'Person Schema Test';
	protected static $description = 'Tests for Person structured data (author profiles)';

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
		// Check for author/person indicators
		$has_author = preg_match( '/\b(author|written by|posted by|by:|profile|bio|biography)\b/i', $html );
		$has_avatar = preg_match( '/class=["\'][^"\']*avatar|gravatar/i', $html );

		// Check for author archive URL pattern
		$is_author_page = preg_match( '/\/author\/|\/profile\/|\/team\//i', $checked_url );

		// Check for Person schema
		$has_person_schema = preg_match( '/"@type"\s*:\s*"Person"/i', $html );

		// If looks like author/profile page but no schema
		if ( ( $has_author || $is_author_page ) && ! $has_person_schema ) {
			return array(
				'id'            => 'schema-person-missing',
				'title'         => 'Person Schema Missing',
				'description'   => 'Author or profile page detected but no Person structured data found. Person schema helps search engines understand author identity and expertise.'
				'kb_link' => 'https://wpshadow.com/kb/person-schema/',
				'training_link' => 'https://wpshadow.com/training/author-seo/',
				'auto_fixable'  => false,
				'threat_level'  => 30,
				'module'        => 'SEO',
				'priority'      => 3,
				'meta'          => array(
					'has_author'     => $has_author,
					'has_avatar'     => $has_avatar,
					'is_author_page' => $is_author_page,
					'has_schema'     => $has_person_schema,
					'checked_url'    => $checked_url,
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
		return __( 'Person Schema', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for Person structured data (author profiles).', 'wpshadow' );
	}
}
