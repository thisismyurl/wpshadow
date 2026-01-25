<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Skip_Links extends Diagnostic_Base {


	protected static $slug        = 'test-ux-skip-links';
	protected static $title       = 'Skip Links Test';
	protected static $description = 'Tests for "skip to content" links (accessibility)';

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
		// Check for skip links
		$has_skip_link = preg_match( '/<a[^>]*href=["\']#[^"\']*content["\'][^>]*>.*?skip/is', $html ) ||
			preg_match( '/<a[^>]*class=["\'][^"\']*skip/i', $html );

		// Check for main landmark
		$has_main = preg_match( '/<main[^>]*>|role=["\']main/i', $html );

		// If complex page but no skip link
		preg_match_all( '/<nav[^>]*>/i', $html, $nav_matches );
		$nav_count = count( $nav_matches[0] );

		if ( $nav_count > 0 && ! $has_skip_link && $has_main ) {
			return array(
				'id'            => 'ux-skip-links-missing',
				'title'         => 'Skip Links Missing',
				'description'   => 'Navigation detected but no "skip to content" link found. Skip links allow keyboard users to bypass navigation and jump to main content (WCAG 2.4.1).'
				'kb_link' => 'https://wpshadow.com/kb/skip-links/',
				'training_link' => 'https://wpshadow.com/training/keyboard-accessibility/',
				'auto_fixable'  => false,
				'threat_level'  => 50,
				'module'        => 'Accessibility',
				'priority'      => 2,
				'meta'          => array(
					'has_skip_link' => $has_skip_link,
					'nav_count'     => $nav_count,
					'has_main'      => $has_main,
					'checked_url'   => $checked_url,
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
		return __( 'Skip Links', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for "skip to content" links (WCAG 2.4.1).', 'wpshadow' );
	}
}
