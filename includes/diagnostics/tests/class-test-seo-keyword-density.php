<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Keyword_Density extends Diagnostic_Base {


	protected static $slug        = 'test-seo-keyword-density';
	protected static $title       = 'Keyword Density Test';
	protected static $description = 'Tests for appropriate keyword usage (not stuffing)';

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
		// Extract body content (remove scripts, styles, nav, footer)
		$content = preg_replace( '/<script[^>]*>.*?<\/script>/is', '', $html );
		$content = preg_replace( '/<style[^>]*>.*?<\/style>/is', '', $content );
		$content = preg_replace( '/<nav[^>]*>.*?<\/nav>/is', '', $content );
		$content = preg_replace( '/<footer[^>]*>.*?<\/footer>/is', '', $content );

		$text  = strip_tags( $content );
		$text  = strtolower( $text );
		$words = str_word_count( $text, 1 );

		if ( count( $words ) < 50 ) {
			return null; // Too short to analyze
		}

		// Count word frequency
		$word_counts = array_count_values( $words );
		arsort( $word_counts );

		// Remove common words
		$stop_words = array( 'the', 'be', 'to', 'of', 'and', 'a', 'in', 'that', 'have', 'i', 'it', 'for', 'not', 'on', 'with', 'he', 'as', 'you', 'do', 'at', 'this', 'but', 'his', 'by', 'from' );

		foreach ( $stop_words as $stop_word ) {
			unset( $word_counts[ $stop_word ] );
		}

		// Check top keyword density
		$top_words   = array_slice( $word_counts, 0, 5, true );
		$total_words = count( $words );

		foreach ( $top_words as $word => $count ) {
			$density = ( $count / $total_words ) * 100;

			if ( $density > 5 && $count > 10 ) { // More than 5% density
				return array(
					'id'            => 'seo-keyword-stuffing',
					'title'         => 'Possible Keyword Stuffing',
					'description'   => sprintf( 'The word "%s" appears %d times (%.1f%% density). Keyword density over 5%% may be considered stuffing by search engines.', $word, $count, $density )
					'kb_link' => 'https://wpshadow.com/kb/keyword-density/',
					'training_link' => 'https://wpshadow.com/training/seo-writing/',
					'auto_fixable'  => false,
					'threat_level'  => 40,
					'module'        => 'SEO',
					'priority'      => 3,
					'meta'          => array(
						'keyword'     => $word,
						'count'       => $count,
						'density'     => round( $density, 1 ),
						'total_words' => $total_words,
					),
				);
			}
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
		return __( 'Keyword Density', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for appropriate keyword usage (not stuffing).', 'wpshadow' );
	}
}
