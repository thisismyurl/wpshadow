<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Author_Bio extends Diagnostic_Base {


	protected static $slug        = 'test-content-author-bio';
	protected static $title       = 'Author Bio Test';
	protected static $description = 'Tests for author bio/information on posts';

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
		// Check if it's a blog post/article
		$is_article = ( preg_match( '/<article[^>]*>/i', $html ) ||
			preg_match( '/class=["\'][^"\']*(?:single|post|entry|article)[^"\']*["\']/i', $html ) );

		if ( ! $is_article ) {
			return null;
		}

		// Look for author bio indicators
		$author_patterns = array(
			'/class=["\'][^"\']*author[_-]?(?:bio|box|info|card|profile)[^"\']*["\']/i',
			'/<div[^>]+class=["\'][^"\']*about[_-]?author[^"\']*["\']/i',
			'/rel=["\']author["\']/i',
			'/<span[^>]+class=["\'][^"\']*(?:by|author)[_-]?name[^"\']*["\']/i',
		);

		$has_author_bio = false;
		foreach ( $author_patterns as $pattern ) {
			if ( preg_match( $pattern, $html ) ) {
				// Check if it's substantial (not just a name)
				if ( preg_match( '/<(?:div|section)[^>]+class=["\'][^"\']*author[_-]?bio[^"\']*["\']/i', $html ) ) {
					$has_author_bio = true;
					break;
				}
			}
		}

		if ( $is_article && ! $has_author_bio ) {
			return array(
				'id'            => 'content-no-author-bio',
				'title'         => 'No Author Bio',
				'description'   => 'Article without author bio/information. Author bios build trust, improve E-A-T (SEO), and humanize content.'
				'kb_link' => 'https://wpshadow.com/kb/author-bio/',
				'training_link' => 'https://wpshadow.com/training/content-authority/',
				'auto_fixable'  => false,
				'threat_level'  => 30,
				'module'        => 'Content Quality',
				'priority'      => 3,
				'meta'          => array( 'has_author_bio' => false ),
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
		return __( 'Author Bio', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for author bio/information on posts.', 'wpshadow' );
	}
}
