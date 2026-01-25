<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Accessibility_Empty_Links extends Diagnostic_Base {


	protected static $slug        = 'test-accessibility-empty-links';
	protected static $title       = 'Empty Links Test';
	protected static $description = 'Tests for links with no accessible text';

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
		// Find all links
		preg_match_all( '/<a[^>]+href=[^>]*>(.*?)<\/a>/is', $html, $links, PREG_SET_ORDER );

		$empty_links = 0;

		foreach ( $links as $link ) {
			$full_tag     = $link[0];
			$link_content = $link[1];

			// Check if link has aria-label or aria-labelledby
			$has_aria = preg_match( '/aria-label(?:ledby)?=/i', $full_tag );

			// Check if link has title attribute
			$has_title = preg_match( '/title=/i', $full_tag );

			// Check if content is empty or only whitespace
			$text_content = strip_tags( $link_content );
			$text_content = trim( $text_content );

			// Check for image alt text
			$has_img_alt = preg_match( '/<img[^>]+alt=["\']([^"\']+)["\']/i', $link_content, $alt_match ) && ! empty( trim( $alt_match[1] ) );

			if ( empty( $text_content ) && ! $has_aria && ! $has_title && ! $has_img_alt ) {
				++$empty_links;
			}
		}

		if ( $empty_links > 0 ) {
			return array(
				'id'            => 'accessibility-empty-links',
				'title'         => 'Empty Links Detected',
				'description'   => sprintf( '%d link(s) have no accessible text. Screen readers announce these as "link" with no context.', $empty_links )
				'kb_link' => 'https://wpshadow.com/kb/empty-links/',
				'training_link' => 'https://wpshadow.com/training/accessible-links/',
				'auto_fixable'  => false,
				'threat_level'  => 40,
				'module'        => 'Accessibility',
				'priority'      => 2,
				'meta'          => array( 'empty_links' => $empty_links ),
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
		return __( 'Empty Links', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for links with no accessible text.', 'wpshadow' );
	}
}
