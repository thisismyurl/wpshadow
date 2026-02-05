<?php
/**
 * Post Meta Serialization Issues Diagnostic
 *
 * Detects improperly serialized post meta. Tests for serialization errors that cause
 * data corruption and identifies meta values with broken serialization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Meta Serialization Issues Diagnostic Class
 *
 * Checks for serialization issues in post meta data.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Post_Meta_Serialization_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-meta-serialization-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Meta Serialization Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects improperly serialized post meta and data corruption issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Sample meta values to check for serialization issues.
		$sample_meta = $wpdb->get_results(
			"SELECT meta_id, post_id, meta_key, meta_value
			FROM {$wpdb->postmeta}
			WHERE meta_value LIKE 'a:%'
			OR meta_value LIKE 'O:%'
			OR meta_value LIKE 's:%'
			LIMIT 500",
			ARRAY_A
		);

		$broken_serialization = 0;
		$double_serialized = 0;
		$corrupted_strings = 0;

		foreach ( $sample_meta as $meta ) {
			$value = $meta['meta_value'];

			// Check if value looks serialized.
			if ( $this->is_serialized_string( $value ) ) {
				// Try to unserialize.
				$unserialized = @unserialize( $value ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

				if ( false === $unserialized && 'b:0;' !== $value ) {
					++$broken_serialization;
				} elseif ( is_string( $unserialized ) && $this->is_serialized_string( $unserialized ) ) {
					// Double serialization detected.
					++$double_serialized;
				}

				// Check for string length mismatches in serialized data.
				if ( preg_match( '/s:(\d+):"/', $value, $matches ) ) {
					$declared_length = (int) $matches[1];
					// Extract the actual string.
					$pattern = '/s:' . $declared_length . ':"([^"]*)"/';
					if ( preg_match( $pattern, $value, $string_matches ) ) {
						$actual_length = strlen( $string_matches[1] );
						if ( $declared_length !== $actual_length ) {
							++$corrupted_strings;
						}
					}
				}
			}
		}

		if ( $broken_serialization > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of broken entries */
				__( '%d post meta entries have broken serialization (cannot be unserialized)', 'wpshadow' ),
				$broken_serialization
			);
		}

		if ( $double_serialized > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of double serialized entries */
				__( '%d post meta entries are double-serialized (data corruption issue)', 'wpshadow' ),
				$double_serialized
			);
		}

		if ( $corrupted_strings > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of corrupted strings */
				__( '%d serialized strings have length mismatches (corrupted data)', 'wpshadow' ),
				$corrupted_strings
			);
		}

		// Check for object serialization (potential security issue).
		$object_serialization = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_value LIKE 'O:%'
			LIMIT 100"
		);

		if ( $object_serialization > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of object serializations */
				__( '%d post meta entries contain serialized objects (potential security risk)', 'wpshadow' ),
				$object_serialization
			);
		}

		// Check for meta values with null bytes (database corruption).
		$null_byte_meta = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta}
				WHERE meta_value LIKE %s
				LIMIT 100",
				'%' . $wpdb->esc_like( "\x00" ) . '%'
			)
		);

		if ( $null_byte_meta > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of entries with null bytes */
				__( '%d post meta entries contain null bytes (database corruption)', 'wpshadow' ),
				$null_byte_meta
			);
		}

		// Check for extremely long serialized values (performance issue).
		$long_serialized = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE LENGTH(meta_value) > 65535
			AND (meta_value LIKE 'a:%' OR meta_value LIKE 'O:%')"
		);

		if ( $long_serialized > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of long values */
				__( '%d post meta entries have extremely long serialized values (>64KB, performance issue)', 'wpshadow' ),
				$long_serialized
			);
		}

		// Check for UTF-8 encoding issues in serialized data.
		$encoding_issues = 0;
		foreach ( array_slice( $sample_meta, 0, 100 ) as $meta ) {
			if ( ! mb_check_encoding( $meta['meta_value'], 'UTF-8' ) ) {
				++$encoding_issues;
			}
		}

		if ( $encoding_issues > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of encoding issues */
				__( '%d post meta entries have UTF-8 encoding issues', 'wpshadow' ),
				$encoding_issues
			);
		}

		// Check for array vs object confusion.
		$array_object_confusion = 0;
		foreach ( array_slice( $sample_meta, 0, 100 ) as $meta ) {
			$value = $meta['meta_value'];
			if ( $this->is_serialized_string( $value ) ) {
				$unserialized = @unserialize( $value ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				// Check if it's an object that looks like it should be an array.
				if ( is_object( $unserialized ) && property_exists( $unserialized, '0' ) ) {
					++$array_object_confusion;
				}
			}
		}

		if ( $array_object_confusion > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of confused entries */
				__( '%d post meta entries have array/object confusion (serialization issue)', 'wpshadow' ),
				$array_object_confusion
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-meta-serialization-issues',
			);
		}

		return null;
	}

	/**
	 * Check if a string is serialized.
	 *
	 * @since  1.6030.2148
	 * @param  string $data Data to check.
	 * @return bool True if serialized, false otherwise.
	 */
	private static function is_serialized_string( $data ) {
		// If it isn't a string, it isn't serialized.
		if ( ! is_string( $data ) ) {
			return false;
		}
		$data = trim( $data );
		if ( 'N;' === $data ) {
			return true;
		}
		if ( strlen( $data ) < 4 ) {
			return false;
		}
		if ( ':' !== $data[1] ) {
			return false;
		}
		$lastc = substr( $data, -1 );
		if ( ';' !== $lastc && '}' !== $lastc ) {
			return false;
		}
		$token = $data[0];
		switch ( $token ) {
			case 's':
				if ( '"' !== substr( $data, -2, 1 ) ) {
					return false;
				}
				return true;
			case 'a':
			case 'O':
				return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
			case 'b':
			case 'i':
			case 'd':
				return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$/", $data );
		}
		return false;
	}
}
