<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Tap_Spacing extends Diagnostic_Base {


	protected static $slug        = 'test-mobile-tap-spacing';
	protected static $title       = 'Tap Target Spacing Test';
	protected static $description = 'Tests for adequate spacing between tap targets';

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
		// Check for margin/padding around interactive elements
		preg_match_all( '/<a[^>]*style=["\']([^"\']+)["\']/i', $html, $link_styles );
		preg_match_all( '/<button[^>]*style=["\']([^"\']+)["\']/i', $html, $button_styles );

		$all_styles = array_merge( $link_styles[1], $button_styles[1] );

		$tight_spacing = 0;

		foreach ( $all_styles as $style ) {
			// Check for very small or no margin/padding
			if ( preg_match( '/margin:\s*0|padding:\s*0|margin:\s*[0-5]px|padding:\s*[0-5]px/i', $style ) ) {
				++$tight_spacing;
			}
		}

		// Also check for line-height issues (buttons too close vertically)
		preg_match_all( '/line-height:\s*([0-9.]+)(px|em|rem)?/i', $html, $line_heights );
		$small_line_heights = 0;

		foreach ( $line_heights[1] as $idx => $height ) {
			$unit = $line_heights[2][ $idx ] ?? '';
			if ( ( $unit === 'px' && (float) $height < 20 ) ||
				( ( $unit === 'em' || $unit === 'rem' ) && (float) $height < 1.2 )
			) {
				++$small_line_heights;
			}
		}

		if ( $tight_spacing > 5 || $small_line_heights > 5 ) {
			return array(
				'id'            => 'mobile-tap-spacing',
				'title'         => 'Insufficient Tap Target Spacing',
				'description'   => sprintf(
					'Found %d elements with tight spacing. Google recommends 8px minimum spacing between tap targets for good mobile UX.',
					$tight_spacing + $small_line_heights
				)
				'kb_link' => 'https://wpshadow.com/kb/tap-spacing/',
				'training_link' => 'https://wpshadow.com/training/mobile-ux/',
				'auto_fixable'  => false,
				'threat_level'  => 40,
				'module'        => 'Mobile',
				'priority'      => 3,
				'meta'          => array(
					'tight_spacing'      => $tight_spacing,
					'small_line_heights' => $small_line_heights,
					'checked_url'        => $checked_url,
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
		return __( 'Tap Target Spacing', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for adequate spacing between tap targets (8px minimum).', 'wpshadow' );
	}
}
