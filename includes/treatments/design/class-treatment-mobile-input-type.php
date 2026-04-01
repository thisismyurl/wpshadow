<?php
/**
 * Mobile Input Type Support
 *
 * Validates HTML5 input types for optimal mobile keyboard experience.
 *
 * @package    WPShadow
 * @subpackage Treatments\Forms
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_HTML_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Input Type Support
 *
 * Ensures forms use appropriate HTML5 input types (email, tel, url, number)
 * to trigger correct mobile keyboards, improving user experience.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Input_Type extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-input-type-support';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Input Type Support';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates HTML5 input types for mobile keyboards';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'forms';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Input_Type' );
	}

	/**
	 * Find input type issues in forms.
	 *
	 * @since 0.6093.1200
	 * @return array Issues found.
	 */
	private static function find_input_type_issues(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array( 'all' => array() );
		}

		$issues = array();
		$field_types = array();

		// Find email fields using type="text"
		$email_issues = self::check_email_fields( $html );
		$issues = array_merge( $issues, $email_issues );
		if ( ! empty( $email_issues ) ) {
			$field_types[] = 'email';
		}

		// Find phone fields using type="text"
		$phone_issues = self::check_phone_fields( $html );
		$issues = array_merge( $issues, $phone_issues );
		if ( ! empty( $phone_issues ) ) {
			$field_types[] = 'tel';
		}

		// Find URL fields using type="text"
		$url_issues = self::check_url_fields( $html );
		$issues = array_merge( $issues, $url_issues );
		if ( ! empty( $url_issues ) ) {
			$field_types[] = 'url';
		}

		// Find number/quantity fields using type="text"
		$number_issues = self::check_number_fields( $html );
		$issues = array_merge( $issues, $number_issues );
		if ( ! empty( $number_issues ) ) {
			$field_types[] = 'number';
		}

		return array(
			'all'         => $issues,
			'field_types' => array_unique( $field_types ),
		);
	}

	/**
	 * Check for email fields using type="text".
	 *
	 * @since 0.6093.1200
	 * @param  string $html HTML content.
	 * @return array Email field issues.
	 */
	private static function check_email_fields( string $html ): array {
		$issues = array();

		// Look for inputs with email-related names but type="text"
		$patterns = array(
			'/<input[^>]*name\s*=\s*["\'][^"\']*email[^"\']*["\'][^>]*type\s*=\s*["\']text["\']/i',
			'/<input[^>]*type\s*=\s*["\']text["\'][^>]*name\s*=\s*["\'][^"\']*email[^"\']*["\']/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match_all( $pattern, $html, $matches ) ) {
				foreach ( $matches[0] as $match ) {
					preg_match( '/name\s*=\s*["\']([^"\']+)["\']/', $match, $name_match );
					$issues[] = array(
						'type'            => 'email-field',
						'field_name'      => $name_match[1] ?? 'unknown',
						'current_type'    => 'text',
						'recommended_type' => 'email',
						'benefit'         => 'Shows @ and . on mobile keyboard',
					);
				}
			}
		}

		return $issues;
	}

	/**
	 * Check for phone fields using type="text".
	 *
	 * @since 0.6093.1200
	 * @param  string $html HTML content.
	 * @return array Phone field issues.
	 */
	private static function check_phone_fields( string $html ): array {
		$issues = array();

		$patterns = array(
			'/<input[^>]*name\s*=\s*["\'][^"\']*(?:phone|tel|mobile)[^"\']*["\'][^>]*type\s*=\s*["\']text["\']/i',
			'/<input[^>]*type\s*=\s*["\']text["\'][^>]*name\s*=\s*["\'][^"\']*(?:phone|tel|mobile)[^"\']*["\']/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match_all( $pattern, $html, $matches ) ) {
				foreach ( $matches[0] as $match ) {
					preg_match( '/name\s*=\s*["\']([^"\']+)["\']/', $match, $name_match );
					$issues[] = array(
						'type'             => 'phone-field',
						'field_name'       => $name_match[1] ?? 'unknown',
						'current_type'     => 'text',
						'recommended_type' => 'tel',
						'benefit'          => 'Shows numeric keypad on mobile',
					);
				}
			}
		}

		return $issues;
	}

	/**
	 * Check for URL fields using type="text".
	 *
	 * @since 0.6093.1200
	 * @param  string $html HTML content.
	 * @return array URL field issues.
	 */
	private static function check_url_fields( string $html ): array {
		$issues = array();

		$patterns = array(
			'/<input[^>]*name\s*=\s*["\'][^"\']*(?:url|website|link)[^"\']*["\'][^>]*type\s*=\s*["\']text["\']/i',
			'/<input[^>]*type\s*=\s*["\']text["\'][^>]*name\s*=\s*["\'][^"\']*(?:url|website|link)[^"\']*["\']/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match_all( $pattern, $html, $matches ) ) {
				foreach ( $matches[0] as $match ) {
					preg_match( '/name\s*=\s*["\']([^"\']+)["\']/', $match, $name_match );
					$issues[] = array(
						'type'             => 'url-field',
						'field_name'       => $name_match[1] ?? 'unknown',
						'current_type'     => 'text',
						'recommended_type' => 'url',
						'benefit'          => 'Shows .com and / on mobile keyboard',
					);
				}
			}
		}

		return $issues;
	}

	/**
	 * Check for number fields using type="text".
	 *
	 * @since 0.6093.1200
	 * @param  string $html HTML content.
	 * @return array Number field issues.
	 */
	private static function check_number_fields( string $html ): array {
		$issues = array();

		$patterns = array(
			'/<input[^>]*name\s*=\s*["\'][^"\']*(?:quantity|qty|amount|age|zip|postal)[^"\']*["\'][^>]*type\s*=\s*["\']text["\']/i',
			'/<input[^>]*type\s*=\s*["\']text["\'][^>]*name\s*=\s*["\'][^"\']*(?:quantity|qty|amount|age|zip|postal)[^"\']*["\']/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match_all( $pattern, $html, $matches ) ) {
				foreach ( $matches[0] as $match ) {
					preg_match( '/name\s*=\s*["\']([^"\']+)["\']/', $match, $name_match );
					$issues[] = array(
						'type'             => 'number-field',
						'field_name'       => $name_match[1] ?? 'unknown',
						'current_type'     => 'text',
						'recommended_type' => 'number',
						'benefit'          => 'Shows numeric keyboard on mobile',
					);
				}
			}
		}

		return $issues;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since 0.6093.1200
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Treatment_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
