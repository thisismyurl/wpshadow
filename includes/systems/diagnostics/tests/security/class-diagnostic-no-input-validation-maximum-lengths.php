<?php
/**
 * No Input Validation Maximum Lengths Diagnostic
 *
 * Checks if form inputs have maximum length validation to prevent buffer overflows.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Input Validation Maximum Lengths Diagnostic
 *
 * Detects form inputs without maximum length validation. Unconstrained input fields
 * can accept gigabytes of data, causing buffer overflows, database corruption, or
 * DoS attacks. Always validate maximum input lengths on both client (HTML5) and
 * server (PHP).
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Input_Validation_Maximum_Lengths extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-input-validation-maximum-lengths';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Form Inputs Have Maximum Length Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if form inputs validate maximum length to prevent buffer overflows and DoS attacks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$unvalidated_inputs = self::check_unvalidated_inputs();

		if ( ! empty( $unvalidated_inputs ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of inputs without max length */
					__( 'Found %d form inputs without maximum length validation. Unconstrained inputs can accept gigabytes of data, causing database corruption, buffer overflows, or DoS attacks. Always set maxlength on HTML (client-side) and validate on server (PHP). Example: Email should max 254 chars, name max 100 chars.', 'wpshadow' ),
					count( $unvalidated_inputs )
				),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/input-validation-maximum-lengths',
				'details'     => array(
					'unvalidated_count' => count( $unvalidated_inputs ),
					'examples'          => array_slice( $unvalidated_inputs, 0, 5 ),
					'recommended_lengths' => self::get_recommended_lengths(),
					'recommendation'    => __( 'Add maxlength attribute and server-side PHP validation', 'wpshadow' ),
				),
			);
		}

		return null; // No issue found
	}

	/**
	 * Check for form inputs without maximum length validation
	 *
	 * @since 1.6093.1200
	 * @return array Array of unvalidated inputs
	 */
	private static function check_unvalidated_inputs(): array {
		$unvalidated = array();

		// Get all forms on homepage
		$response = wp_remote_get( home_url( '/' ) );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$body = wp_remote_retrieve_body( $response );

		// Find all input fields
		if ( preg_match_all( '/<input\s+([^>]*)>/i', $body, $matches ) ) {
			foreach ( $matches[1] as $attributes ) {
				// Check if this is a text-like input
				if ( ! preg_match( '/type\s*=\s*["\']?(text|email|search|tel|url)["\']?/i', $attributes ) ) {
					continue;
				}

				// Check if it has maxlength
				if ( preg_match( '/maxlength\s*=\s*["\']?(\d+)["\']?/i', $attributes, $length_match ) ) {
					// Has max length, skip
					continue;
				}

				// Extract name attribute if present
				$name = 'Unknown Input';
				if ( preg_match( '/name\s*=\s*["\']?([^\s"\']+)["\']?/i', $attributes, $name_match ) ) {
					$name = $name_match[1];
				}

				$unvalidated[] = array(
					'type'  => 'HTML Input',
					'name'  => $name,
					'issue' => 'Missing maxlength attribute',
				);
			}
		}

		// Also check textarea without maxlength
		if ( preg_match_all( '/<textarea\s+([^>]*)>/i', $body, $matches ) ) {
			foreach ( $matches[1] as $attributes ) {
				if ( preg_match( '/maxlength/i', $attributes ) ) {
					continue;
				}

				$name = 'Textarea';
				if ( preg_match( '/name\s*=\s*["\']?([^\s"\']+)["\']?/i', $attributes, $name_match ) ) {
					$name = $name_match[1];
				}

				$unvalidated[] = array(
					'type'  => 'Textarea',
					'name'  => $name,
					'issue' => 'Missing maxlength attribute',
				);
			}
		}

		return $unvalidated;
	}

	/**
	 * Get recommended maximum lengths by field type
	 *
	 * @since 1.6093.1200
	 * @return array Array of field types with recommended max lengths
	 */
	private static function get_recommended_lengths(): array {
		return array(
			array(
				'field'     => 'Email',
				'maxlength' => 254,
				'reason'    => 'RFC 5321 standard for email addresses',
			),
			array(
				'field'     => 'Password',
				'maxlength' => 128,
				'reason'    => 'Prevent excessively long passwords, usually hashed anyway',
			),
			array(
				'field'     => 'Name',
				'maxlength' => 100,
				'reason'    => 'Most human names under 100 characters',
			),
			array(
				'field'     => 'Phone',
				'maxlength' => 20,
				'reason'    => 'International phone numbers rarely exceed 15 digits',
			),
			array(
				'field'     => 'URL',
				'maxlength' => 2048,
				'reason'    => 'Browser URL length limit',
			),
			array(
				'field'     => 'Address',
				'maxlength' => 255,
				'reason'    => 'Standard database field length',
			),
			array(
				'field'     => 'Comment',
				'maxlength' => 10000,
				'reason'    => 'Prevent spam/abuse while allowing detailed feedback',
			),
		);
	}
}
