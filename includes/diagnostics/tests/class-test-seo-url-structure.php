<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_URL_Structure extends Diagnostic_Base {


	protected static $slug        = 'test-seo-url-structure';
	protected static $title       = 'URL Structure Test';
	protected static $description = 'Tests for SEO-friendly URL structure';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$check_url = $url ?? home_url( '/' );

		// Check for non-SEO-friendly patterns
		$has_query_params = preg_match( '/\?p=[0-9]+|\?page_id=[0-9]+/i', $check_url );
		$has_index_php    = preg_match( '/\/index\.php\//i', $check_url );

		if ( $has_query_params || $has_index_php ) {
			return array(
				'id'            => 'seo-url-structure-unfriendly',
				'title'         => 'Non-SEO-Friendly URL Structure',
				'description'   => 'URLs use query parameters (?p=123) or /index.php/. Switch to pretty permalinks (Settings > Permalinks) for better SEO.'
				'kb_link' => 'https://wpshadow.com/kb/permalink-structure/',
				'training_link' => 'https://wpshadow.com/training/wordpress-seo/',
				'auto_fixable'  => false,
				'threat_level'  => 50,
				'module'        => 'SEO',
				'priority'      => 2,
				'meta'          => array(
					'has_query_params' => $has_query_params,
					'has_index_php'    => $has_index_php,
					'checked_url'      => $check_url,
				),
			);
		}

		return null;
	}

	public static function get_name(): string {
		return __( 'URL Structure', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for SEO-friendly URL structure (pretty permalinks).', 'wpshadow' );
	}
}
