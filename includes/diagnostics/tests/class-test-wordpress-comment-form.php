<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Comment_Form extends Diagnostic_Base {


	protected static $slug        = 'test-wordpress-comment-form';
	protected static $title       = 'Comment Form Test';
	protected static $description = 'Tests for comment form spam protection';

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
		// Check if comments are enabled
		$has_comment_form = preg_match( '/<form[^>]+id=["\']commentform["\']/i', $html ) ||
			preg_match( '/<form[^>]+class=["\'][^"\']*comment-form[^"\']*["\']/i', $html );

		if ( ! $has_comment_form ) {
			return null; // No comment form, test not applicable
		}

		// Check for CAPTCHA or similar protection
		$has_captcha = preg_match( '/recaptcha|hcaptcha|captcha/i', $html );

		// Check for honeypot fields
		$has_honeypot = preg_match( '/style=["\'][^"\']*display\s*:\s*none[^"\']*["\'][^>]*<input[^>]+name=/i', $html );

		// Check for Akismet
		$has_akismet = preg_match( '/akismet/i', $html );

		if ( ! $has_captcha && ! $has_honeypot && ! $has_akismet ) {
			return array(
				'id'            => 'wordpress-comment-form-no-protection',
				'title'         => 'Comment Form Not Protected',
				'description'   => 'Comment form has no visible spam protection (no CAPTCHA, honeypot, or Akismet). Open to spam bots.'
				'kb_link' => 'https://wpshadow.com/kb/comment-spam-protection/',
				'training_link' => 'https://wpshadow.com/training/spam-prevention/',
				'auto_fixable'  => false,
				'threat_level'  => 35,
				'module'        => 'WordPress',
				'priority'      => 3,
				'meta'          => array( 'has_protection' => false ),
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
		return __( 'Comment Form', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for comment form spam protection.', 'wpshadow' );
	}
}
