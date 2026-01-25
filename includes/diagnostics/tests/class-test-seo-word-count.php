<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Word_Count extends Diagnostic_Base {


	protected static $slug        = 'test-seo-word-count';
	protected static $title       = 'Word Count Test';
	protected static $description = 'Tests for adequate content length';

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
		// Extract main content
		if ( preg_match( '/<article[^>]*>(.*?)<\/article>/is', $html, $article_match ) ) {
			$content = $article_match[1];
		} elseif ( preg_match( '/<main[^>]*>(.*?)<\/main>/is', $html, $main_match ) ) {
			$content = $main_match[1];
		} else {
			// Try to get body content, exclude nav/footer
			$content = preg_replace( '/<nav[^>]*>.*?<\/nav>/is', '', $html );
			$content = preg_replace( '/<footer[^>]*>.*?<\/footer>/is', '', $content );
		}

		$text       = strip_tags( $content );
		$word_count = str_word_count( $text );

		// Check if this looks like a blog post or content page
		$is_post = preg_match( '/<article[^>]*class=["\'][^"\']*post|<article[^>]*class=["\'][^"\']*entry/i', $html );

		if ( $is_post && $word_count < 300 ) {
			return array(
				'id'            => 'seo-word-count-low',
				'title'         => 'Low Word Count',
				'description'   => sprintf( 'Content has only %d words. For better SEO, aim for at least 300 words (1000+ for competitive topics).', $word_count )
				'kb_link' => 'https://wpshadow.com/kb/content-length/',
				'training_link' => 'https://wpshadow.com/training/seo-writing/',
				'auto_fixable'  => false,
				'threat_level'  => 35,
				'module'        => 'Content',
				'priority'      => 3,
				'meta'          => array(
					'word_count'  => $word_count,
					'is_post'     => $is_post,
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
		return __( 'Word Count', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for adequate content length (300+ words).', 'wpshadow' );
	}
}
