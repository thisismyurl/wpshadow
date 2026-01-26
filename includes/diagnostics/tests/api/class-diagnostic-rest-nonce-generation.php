<?php
/**
 * Diagnostic: REST Nonce Generation
 *
 * Verifies that WordPress can generate valid nonces for REST API requests.
 * Nonces protect against Cross-Site Request Forgery (CSRF) attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\API
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Rest_Nonce_Generation
 *
 * Tests REST API nonce generation functionality.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Rest_Nonce_Generation extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rest-nonce-generation';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'REST Nonce Generation';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies nonce generation works for REST requests';

	/**
	 * Check REST API nonce generation.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Test standard nonce generation.
		$nonce = wp_create_nonce( 'wp_rest' );

		if ( empty( $nonce ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WordPress is unable to generate nonces for REST API requests. This may indicate a session or security configuration issue.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-nonce-generation',
				'meta'        => array(
					'nonce_empty' => true,
				),
			);
		}

		// Verify nonce format (should be 10 characters).
		if ( strlen( $nonce ) !== 10 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Length of generated nonce */
					__( 'Generated nonce has unexpected length (%d characters). Expected 10 characters.', 'wpshadow' ),
					strlen( $nonce )
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-nonce-generation',
				'meta'        => array(
					'nonce_length' => strlen( $nonce ),
					'nonce'        => $nonce,
				),
			);
		}

		// Test if nonce can be verified.
		$verified = wp_verify_nonce( $nonce, 'wp_rest' );

		if ( ! $verified ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Generated nonce could not be verified. This indicates a session or security configuration problem.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-nonce-generation',
				'meta'        => array(
					'nonce'         => $nonce,
					'verified'      => $verified,
				),
			);
		}

		// Nonce generation is working correctly.
		return null;
	}
}
