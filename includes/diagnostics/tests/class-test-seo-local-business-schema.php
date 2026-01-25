<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Local_Business_Schema extends Diagnostic_Base {


	protected static $slug        = 'test-seo-local-business-schema';
	protected static $title       = 'Local Business Schema Test';
	protected static $description = 'Tests for LocalBusiness schema markup';

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
		$has_local_business = preg_match( '/"@type"\s*:\s*"LocalBusiness"/i', $html );

		if ( $has_local_business ) {
			// Check completeness
			$has_name    = preg_match( '/"name"\s*:\s*"[^"]+"/i', $html );
			$has_address = preg_match( '/"address"\s*:\s*{/i', $html );
			$has_phone   = preg_match( '/"telephone"\s*:\s*"[^"]+"/i', $html );

			$missing = array();
			if ( ! $has_name ) {
				$missing[] = 'name';
			}
			if ( ! $has_address ) {
				$missing[] = 'address';
			}
			if ( ! $has_phone ) {
				$missing[] = 'telephone';
			}

			if ( ! empty( $missing ) ) {
				return array(
					'id'            => 'seo-local-business-schema',
					'title'         => 'Incomplete Local Business Schema',
					'description'   => sprintf( 'Local Business schema missing: %s', implode( ', ', $missing ) )
					'kb_link' => 'https://wpshadow.com/kb/local-business-schema/',
					'training_link' => 'https://wpshadow.com/training/local-seo/',
					'auto_fixable'  => false,
					'threat_level'  => 40,
					'module'        => 'SEO',
					'priority'      => 2,
					'meta'          => array(
						'missing'     => $missing,
						'checked_url' => $checked_url,
					),
				);
			}

			return null; // Complete
		}

		// Check if this is likely a local business site
		$has_local_indicators = preg_match( '/\b(hours?|address|phone|visit us|location|directions)\b/i', $html );
		if ( ! $has_local_indicators ) {
			return null; // Not a local business
		}

		return array(
			'id'            => 'seo-local-business-schema',
			'title'         => 'Missing Local Business Schema',
			'description'   => 'This appears to be a local business but lacks LocalBusiness schema. Adding this markup improves visibility in local search results and Google Maps.'
			'kb_link' => 'https://wpshadow.com/kb/local-business-schema/',
			'training_link' => 'https://wpshadow.com/training/local-seo/',
			'auto_fixable'  => false,
			'threat_level'  => 50,
			'module'        => 'SEO',
			'priority'      => 2,
			'meta'          => array( 'checked_url' => $checked_url ),
		);
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
		return __( 'Local Business Schema', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for LocalBusiness schema (local SEO).', 'wpshadow' );
	}
}
