<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Accessibility_Focus_Visible extends Diagnostic_Base {


	protected static $slug        = 'test-accessibility-focus-visible';
	protected static $title       = 'Focus Indicator Test';
	protected static $description = 'Tests for visible focus indicators on interactive elements';

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
		// Extract all <style> blocks and linked CSS
		preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_blocks );

		$css_content = implode( "\n", $style_blocks[1] );

		// Check for outline:none or outline:0 on focus
		$removes_outline = preg_match( '/:focus[^{]*{[^}]*outline\s*:\s*(?:none|0)/i', $css_content );

		// Check if there's a custom focus style defined
		$has_custom_focus = preg_match( '/:focus[^{]*{[^}]*(?:box-shadow|border|background)/i', $css_content );

		if ( $removes_outline && ! $has_custom_focus ) {
			return array(
				'id'            => 'accessibility-no-focus-indicator',
				'title'         => 'Focus Indicators Removed',
				'description'   => 'CSS removes focus outlines (outline:none) without providing alternative indicators. Keyboard users cannot see where focus is.'
				'kb_link' => 'https://wpshadow.com/kb/focus-indicators/',
				'training_link' => 'https://wpshadow.com/training/keyboard-accessibility/',
				'auto_fixable'  => false,
				'threat_level'  => 50,
				'module'        => 'Accessibility',
				'priority'      => 2,
				'meta'          => array(
					'removes_outline'  => true,
					'has_custom_focus' => false,
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
		return __( 'Focus Indicator', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for visible focus indicators on interactive elements.', 'wpshadow' );
	}
}
