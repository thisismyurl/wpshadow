<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Friendly_Content extends Diagnostic_Base {


	protected static $slug        = 'test-mobile-friendly-content';
	protected static $title       = 'Mobile-Friendly Content Test';
	protected static $description = 'Tests for mobile-unfriendly content patterns';

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
		$issues = array();

		// Check for Flash content (unsupported on mobile)
		if ( preg_match( '/<embed[^>]*type=["\']application\/x-shockwave-flash|<object[^>]*type=["\']application\/x-shockwave-flash/i', $html ) ) {
			$issues[] = 'Flash content detected (unsupported on mobile)';
		}

		// Check for tables used for layout (not responsive)
		preg_match_all( '/<table[^>]*>/i', $html, $tables );
		$table_count = count( $tables[0] );
		if ( $table_count > 3 ) {
			// Check if tables have role="presentation" (layout tables)
			preg_match_all( '/<table[^>]*role=["\']presentation["\']/i', $html, $layout_tables );
			if ( count( $layout_tables[0] ) > 0 ) {
				$issues[] = sprintf( '%d layout tables (not responsive)', count( $layout_tables[0] ) );
			}
		}

		// Check for iframes (can cause mobile issues)
		preg_match_all( '/<iframe[^>]*>/i', $html, $iframes );
		$iframe_count = count( $iframes[0] );
		if ( $iframe_count > 2 ) {
			$issues[] = sprintf( '%d iframes (may not be responsive)', $iframe_count );
		}

		// Check for absolutely positioned elements (can overlap on mobile)
		preg_match_all( '/position:\s*absolute/i', $html, $absolute );
		$absolute_count = count( $absolute[0] );
		if ( $absolute_count > 5 ) {
			$issues[] = sprintf( '%d absolutely positioned elements (may overlap)', $absolute_count );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => 'mobile-unfriendly-content',
				'title'         => 'Mobile-Unfriendly Content Patterns',
				'description'   => 'Found mobile-unfriendly content: ' . implode( ', ', $issues ) . '. These patterns can cause display and usability issues on mobile devices.'
				'kb_link' => 'https://wpshadow.com/kb/mobile-content/',
				'training_link' => 'https://wpshadow.com/training/mobile-best-practices/',
				'auto_fixable'  => false,
				'threat_level'  => 45,
				'module'        => 'Mobile',
				'priority'      => 2,
				'meta'          => array(
					'issues'      => $issues,
					'issue_count' => count( $issues ),
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
		return __( 'Mobile-Friendly Content', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for mobile-unfriendly content (Flash, layout tables, etc).', 'wpshadow' );
	}
}
