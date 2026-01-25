<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Content_Overflow extends Diagnostic_Base {


	protected static $slug        = 'test-mobile-content-overflow';
	protected static $title       = 'Content Overflow Test';
	protected static $description = 'Tests for content that overflows containers on mobile';

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
		// Check for overflow: hidden (might hide content)
		preg_match_all( '/overflow:\s*hidden|overflow-x:\s*hidden|overflow-y:\s*hidden/i', $html, $overflow_hidden );
		$hidden_count = count( $overflow_hidden[0] );

		// Check for white-space: nowrap (might cause horizontal overflow)
		preg_match_all( '/white-space:\s*nowrap/i', $html, $nowrap );
		$nowrap_count = count( $nowrap[0] );

		// Check for text-overflow: ellipsis (good, but needs monitoring)
		preg_match_all( '/text-overflow:\s*ellipsis/i', $html, $ellipsis );
		$ellipsis_count = count( $ellipsis[0] );

		// Too many overflow:hidden without ellipsis might indicate problems
		if ( $hidden_count > 5 && $ellipsis_count < 2 ) {
			return array(
				'id'            => 'mobile-content-overflow',
				'title'         => 'Potential Content Overflow Issues',
				'description'   => sprintf(
					'Found %d instances of overflow:hidden and %d nowrap properties. On mobile, this might hide important content. Consider using text-overflow:ellipsis or responsive design.',
					$hidden_count,
					$nowrap_count
				)
				'kb_link' => 'https://wpshadow.com/kb/content-overflow/',
				'training_link' => 'https://wpshadow.com/training/mobile-layout/',
				'auto_fixable'  => false,
				'threat_level'  => 35,
				'module'        => 'Mobile',
				'priority'      => 3,
				'meta'          => array(
					'overflow_hidden_count' => $hidden_count,
					'nowrap_count'          => $nowrap_count,
					'ellipsis_count'        => $ellipsis_count,
					'checked_url'           => $checked_url,
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
		return __( 'Content Overflow', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for content overflow issues on mobile.', 'wpshadow' );
	}
}
