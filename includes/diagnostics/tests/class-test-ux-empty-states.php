<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Empty_States extends Diagnostic_Base {


	protected static $slug        = 'test-ux-empty-states';
	protected static $title       = 'Empty States Test';
	protected static $description = 'Tests for helpful empty state messaging';

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
		// Check for empty result indicators
		$empty_patterns = array(
			'no results',
			'nothing found',
			'no items',
			'empty',
			'0 results',
			'no posts',
		);

		$has_empty_message = false;
		$has_helpful_cta   = false;

		foreach ( $empty_patterns as $pattern ) {
			if ( stripos( $html, $pattern ) !== false ) {
				$has_empty_message = true;

				// Check if helpful CTA nearby (within 200 chars)
				$pos     = stripos( $html, $pattern );
				$context = substr( $html, $pos, 200 );

				if ( preg_match( '/<a[^>]*>|<button[^>]*>/i', $context ) ) {
					$has_helpful_cta = true;
				}
				break;
			}
		}

		// If empty state but no helpful CTA
		if ( $has_empty_message && ! $has_helpful_cta ) {
			return array(
				'id'            => 'ux-empty-states-no-cta',
				'title'         => 'Empty State Missing Call-to-Action',
				'description'   => 'Empty state message detected but no helpful call-to-action nearby. Guide users with links like "Browse all posts" or "Clear filters".'
				'kb_link' => 'https://wpshadow.com/kb/empty-states/',
				'training_link' => 'https://wpshadow.com/training/ux-patterns/',
				'auto_fixable'  => false,
				'threat_level'  => 30,
				'module'        => 'UX',
				'priority'      => 3,
				'meta'          => array(
					'has_empty_message' => $has_empty_message,
					'has_cta'           => $has_helpful_cta,
					'checked_url'       => $checked_url,
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
		return __( 'Empty States', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for helpful empty state messaging with CTAs.', 'wpshadow' );
	}
}
