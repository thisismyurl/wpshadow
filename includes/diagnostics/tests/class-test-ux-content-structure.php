<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Content_Structure extends Diagnostic_Base {


	protected static $slug        = 'test-ux-content-structure';
	protected static $title       = 'Content Structure Test';
	protected static $description = 'Tests for well-structured content (lists, paragraphs, etc)';

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
		// Count content elements
		preg_match_all( '/<p[^>]*>/i', $html, $paragraphs );
		$p_count = count( $paragraphs[0] );

		preg_match_all( '/<ul[^>]*>|<ol[^>]*>/i', $html, $lists );
		$list_count = count( $lists[0] );

		preg_match_all( '/<h[1-6][^>]*>/i', $html, $headings );
		$heading_count = count( $headings[0] );

		// Count long text blocks (potential wall of text)
		preg_match_all( '/<p[^>]*>([^<]{500,})<\/p>/is', $html, $long_paragraphs );
		$long_p_count = count( $long_paragraphs[0] );

		// If many paragraphs but few headings/lists (poor structure)
		$structure_ratio = $p_count > 0 ? ( $heading_count + $list_count ) / $p_count : 1;

		if ( $p_count > 10 && $structure_ratio < 0.3 ) {
			return array(
				'id'            => 'ux-content-structure-poor',
				'title'         => 'Poor Content Structure',
				'description'   => sprintf(
					'Found %d paragraphs but only %d headings and %d lists. Break up content with headings, lists, and shorter paragraphs for better readability.',
					$p_count,
					$heading_count,
					$list_count
				)
				'kb_link' => 'https://wpshadow.com/kb/content-structure/',
				'training_link' => 'https://wpshadow.com/training/content-writing/',
				'auto_fixable'  => false,
				'threat_level'  => 30,
				'module'        => 'UX',
				'priority'      => 3,
				'meta'          => array(
					'paragraph_count' => $p_count,
					'heading_count'   => $heading_count,
					'list_count'      => $list_count,
					'long_paragraphs' => $long_p_count,
					'structure_ratio' => round( $structure_ratio, 2 ),
					'checked_url'     => $checked_url,
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
		return __( 'Content Structure', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for well-structured content (headings, lists, paragraphs).', 'wpshadow' );
	}
}
