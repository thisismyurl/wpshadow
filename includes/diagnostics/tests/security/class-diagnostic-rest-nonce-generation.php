<?php
/**
 * Diagnostic: REST Nonce Generation
 *
 * Checks that REST nonce generation is available for logged-in sessions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
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
 * Tests REST nonce generation capability.
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
	protected static $description = 'Checks if REST nonces can be generated for logged-in sessions';

	/**
	 * Check REST nonce generation.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! is_user_logged_in() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST nonce generation cannot be validated because no user is logged in. Generate a nonce during an authenticated session.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_nonce_generation',
				'meta'        => array(
					'is_logged_in' => false,
				),
			);
		}

		$nonce = wp_create_nonce( 'wp_rest' );

		if ( empty( $nonce ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST nonce could not be generated. Check nonce salts and authentication configuration.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_nonce_generation',
				'meta'        => array(
					'is_logged_in' => true,
					'nonce_empty'  => true,
				),
			);
		}

		return null;
	}
}
