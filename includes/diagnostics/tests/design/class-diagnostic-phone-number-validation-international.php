<?php
/**
 * Phone Number Validation International
 *
 * Detects phone fields that are restricted to a fixed, US-only format.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Forms
 * @since      1.6035.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Phone Number Validation International
 *
 * Ensures phone inputs accept international formats (e.g., +44, +33) and
 * do not force a fixed 10-digit pattern that blocks global users.
 *
 * @since 1.6035.1430
 */
class Diagnostic_Phone_Number_Validation_International extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'phone-number-validation-international';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Phone Number Validation Too Restrictive';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if phone fields accept international formats';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'forms';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_phone_validation_issues();

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of phone inputs with restrictive validation */
				__( 'Found %d phone field(s) with restrictive validation that can block international numbers', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 60,
			'issues'       => array_slice( $issues, 0, 10 ),
			'total_issues' => count( $issues ),
			'user_impact'  => __( 'International visitors may not be able to enter their phone numbers, which can block registrations and purchases.', 'wpshadow' ),
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/international-phone-validation',
		);
	}

	/**
	 * Find phone inputs with restrictive validation.
	 *
	 * @since  1.6035.1430
	 * @return array Restrictive phone validation issues.
	 */
	private static function find_phone_validation_issues(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array();
		}

		$dom = Diagnostic_HTML_Helper::parse_html( $html );
		if ( ! $dom ) {
			return array();
		}

		$xpath = Diagnostic_HTML_Helper::create_xpath( $dom );
		$inputs = $xpath->query( '//input' );
		if ( ! $inputs ) {
			return array();
		}

		$issues = array();

		foreach ( $inputs as $input ) {
			if ( ! $input instanceof \DOMElement ) {
				continue;
			}

			$type = strtolower( $input->getAttribute( 'type' ) );
			$name = $input->getAttribute( 'name' );
			$id = $input->getAttribute( 'id' );
			$placeholder = $input->getAttribute( 'placeholder' );

			if ( ! self::is_phone_field( $type, $name, $id, $placeholder ) ) {
				continue;
			}

			$pattern = $input->getAttribute( 'pattern' );
			$maxlength = $input->getAttribute( 'maxlength' );
			$minlength = $input->getAttribute( 'minlength' );

			$restrictions = self::get_restrictive_rules( $pattern, $maxlength, $minlength );
			if ( empty( $restrictions ) ) {
				continue;
			}

			$issues[] = array(
				'type'         => 'restrictive-phone-validation',
				'field_name'   => self::get_field_label( $name, $id, $placeholder ),
				'pattern'      => $pattern ? $pattern : 'none',
				'maxlength'    => $maxlength ? $maxlength : 'none',
				'minlength'    => $minlength ? $minlength : 'none',
				'restrictions' => $restrictions,
				'impact'       => __( 'International phone numbers often use country codes and longer lengths (up to 15 digits).', 'wpshadow' ),
			);
		}

		return $issues;
	}

	/**
	 * Determine if an input is a phone field.
	 *
	 * @since  1.6035.1430
	 * @param  string $type Input type.
	 * @param  string $name Input name.
	 * @param  string $id Input id.
	 * @param  string $placeholder Input placeholder.
	 * @return bool True if the input appears to be a phone field.
	 */
	private static function is_phone_field( string $type, string $name, string $id, string $placeholder ): bool {
		$haystack = strtolower( $name . ' ' . $id . ' ' . $placeholder );
		$is_phone = (bool) preg_match( '/\b(phone|tel|mobile|cell)\b/i', $haystack );

		if ( $is_phone ) {
			return true;
		}

		return in_array( $type, array( 'tel' ), true );
	}

	/**
	 * Identify restrictive validation rules.
	 *
	 * @since  1.6035.1430
	 * @param  string $pattern Pattern attribute value.
	 * @param  string $maxlength Maxlength attribute value.
	 * @param  string $minlength Minlength attribute value.
	 * @return array Restriction descriptions.
	 */
	private static function get_restrictive_rules( string $pattern, string $maxlength, string $minlength ): array {
		$restrictions = array();

		if ( '' !== $pattern && self::is_us_only_pattern( $pattern ) ) {
			$restrictions[] = __( 'Pattern enforces a fixed 10-digit format', 'wpshadow' );
		}

		$max_value = absint( $maxlength );
		$min_value = absint( $minlength );

		if ( $max_value > 0 && $max_value <= 10 ) {
			$restrictions[] = __( 'Maxlength is set to 10 digits', 'wpshadow' );
		}

		if ( $min_value > 0 && $max_value > 0 && $min_value === $max_value && $max_value <= 10 ) {
			$restrictions[] = __( 'Min and max length force a 10-digit number', 'wpshadow' );
		}

		return array_unique( $restrictions );
	}

	/**
	 * Detect common US-only phone patterns.
	 *
	 * @since  1.6035.1430
	 * @param  string $pattern Pattern attribute value.
	 * @return bool True when the pattern looks US-only.
	 */
	private static function is_us_only_pattern( string $pattern ): bool {
		$normalized = trim( $pattern );

		if ( '' === $normalized ) {
			return false;
		}

		$us_patterns = array(
			'/\\d\{10\}/',
			'/\[0-9\]\{10\}/',
			'/\{3\}[^\{\}]*\{3\}[^\{\}]*\{4\}/',
			'/^\^?\[0-9\]\{10\}\$?$/',
		);

		foreach ( $us_patterns as $regex ) {
			if ( 1 === preg_match( $regex, $normalized ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Build a readable label for the field.
	 *
	 * @since  1.6035.1430
	 * @param  string $name Input name.
	 * @param  string $id Input id.
	 * @param  string $placeholder Input placeholder.
	 * @return string Field label.
	 */
	private static function get_field_label( string $name, string $id, string $placeholder ): string {
		if ( '' !== $name ) {
			return $name;
		}

		if ( '' !== $id ) {
			return $id;
		}

		if ( '' !== $placeholder ) {
			return $placeholder;
		}

		return __( 'Unknown phone field', 'wpshadow' );
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since  1.6035.1430
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Diagnostic_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
