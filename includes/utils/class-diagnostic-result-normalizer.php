<?php
/**
 * Normalize diagnostic results for consistent structure.
 *
 * @package WPShadow
 * @subpackage Core
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	// Allow CLI utilities to include this file without a full WordPress bootstrap.
	define( 'ABSPATH', __DIR__ );
}

class Diagnostic_Result_Normalizer {
	const VALID_SEVERITIES = array( 'critical', 'high', 'medium', 'low', 'info' );
	const DEFAULT_SEVERITY = 'medium';

	/**
	 * Ensure a diagnostic result has the required fields and sensible defaults.
	 *
	 * @param string     $class_name Diagnostic class name.
	 * @param array|null $result     Raw diagnostic result.
	 *
	 * @return array|null Normalized result or null if unusable.
	 */
	public static function normalize( string $class_name, $result ): ?array {
		if ( null === $result ) {
			return null; // Explicit pass
		}

		// Treat empty arrays and booleans as a clean pass for legacy stubs.
		if ( is_array( $result ) && 0 === count( $result ) ) {
			return null;
		}

		if ( is_bool( $result ) ) {
			return null;
		}

		if ( ! is_array( $result ) ) {
			return null;
		}

		$normalized = $result;

		$slug = self::call_optional( $class_name, 'get_slug' );

		$id = self::first_non_empty(
			$normalized['id'] ?? null,
			$normalized['finding_id'] ?? null,
			$slug
		);

		if ( '' === $id ) {
			return null;
		}

		$normalized['id'] = $id;

		if ( ! isset( $normalized['finding_id'] ) ) {
			$normalized['finding_id'] = $id; // Legacy compatibility for existing logs/UI.
		}

		if ( empty( $normalized['title'] ) ) {
			$normalized['title'] = self::call_optional( $class_name, 'get_title', $slug );
		}

		if ( empty( $normalized['description'] ) ) {
			$normalized['description'] = self::call_optional( $class_name, 'get_description', $slug );
		}

		$family                 = $normalized['family'] ?? self::call_optional( $class_name, 'get_family', '' );
		$category               = $normalized['category'] ?? Diagnostic_Lean_Checks::family_to_category( $family ?: 'general' );
		$normalized['category'] = $category;

		$severity = $normalized['severity'] ?? self::call_optional( $class_name, 'get_severity', '' );
		if ( ! in_array( $severity, self::VALID_SEVERITIES, true ) ) {
			$severity = self::DEFAULT_SEVERITY;
		}
		$normalized['severity'] = $severity;

		if ( isset( $normalized['threat_level'] ) ) {
			$normalized['threat_level'] = (int) $normalized['threat_level'];
		} else {
			$normalized['threat_level'] = ( 'security' === $category ) ? 60 : 50;
		}

		if ( ! isset( $normalized['auto_fixable'] ) ) {
			$normalized['auto_fixable'] = false;
		}

		$kb_slug = self::determine_kb_slug( $normalized, $slug );

		if ( empty( $normalized['kb_link'] ) ) {
			$normalized['kb_link'] = 'https://wpshadow.com/kb/' . rawurlencode( $kb_slug );
		}

		if ( empty( $normalized['training_link'] ) ) {
			$normalized['training_link'] = 'https://wpshadow.com/training/' . rawurlencode( $kb_slug );
		}

		return $normalized;
	}

	/**
	 * Call an optional diagnostic helper method and coerce to string.
	 *
	 * @param string $class_name Class name.
	 * @param string $method     Method to call if present.
	 * @param string $default    Default value if method missing or not string.
	 *
	 * @return string
	 */
	private static function call_optional( string $class_name, string $method, string $default = '' ): string {
		if ( method_exists( $class_name, $method ) ) {
			$value = call_user_func( array( $class_name, $method ) );
			return is_string( $value ) ? $value : $default;
		}

		return $default;
	}

	/**
	 * Return the first non-empty string value.
	 *
	 * @param mixed ...$values Candidate values.
	 *
	 * @return string
	 */
	private static function first_non_empty( ...$values ): string {
		foreach ( $values as $value ) {
			if ( is_string( $value ) && '' !== $value ) {
				return $value;
			}
		}

		return '';
	}

	/**
	 * Decide the KB/training slug to use for link building.
	 *
	 * @param array  $result   Normalized result array.
	 * @param string $fallback Fallback slug.
	 *
	 * @return string
	 */
	private static function determine_kb_slug( array $result, string $fallback ): string {
		if ( isset( $result['kb_slug'] ) && is_string( $result['kb_slug'] ) && '' !== $result['kb_slug'] ) {
			return $result['kb_slug'];
		}

		if ( isset( $result['id'] ) && is_string( $result['id'] ) && '' !== $result['id'] ) {
			return $result['id'];
		}

		return $fallback;
	}
}
