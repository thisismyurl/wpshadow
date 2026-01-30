<?php
/**
 * Language Pack Update Capability Diagnostic
 *
 * Verifies availability of translation updates.
 *
 * @since   1.2601.2112
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Language_Pack_Update_Capability
 *
 * Checks if WordPress can check for and apply language pack updates.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Language_Pack_Update_Capability extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2112
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		// Check if translations can be updated.
		$can_update_core = wp_can_install_language_pack();

		if ( ! $can_update_core ) {
			// Check why translation updates aren't available.
			$reasons = array();

			// Check if WordPress.org API is reachable.
			$response = wp_remote_head( 'https://api.wordpress.org/languages/1.0/', array(
				'timeout'   => 5,
				'sslverify' => false,
			) );

			if ( is_wp_error( $response ) ) {
				$reasons[] = __( 'Cannot reach WordPress.org language API', 'wpshadow' );
			}

			// Check if translations directory is writable.
			$lang_dir = WP_LANG_DIR;
			if ( ! is_writable( $lang_dir ) ) {
				$reasons[] = __( 'Language directory is not writable', 'wpshadow' );
			}

			// Check if wp_remote_post is available.
			if ( ! function_exists( 'wp_remote_post' ) ) {
				$reasons[] = __( 'WordPress remote post function not available', 'wpshadow' );
			}

			return array(
				'id'           => 'language-pack-update-capability',
				'title'        => __( 'Language Pack Updates Not Available', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: reasons */
					__( 'WordPress cannot check for or install language pack updates. Reasons: %s. Site translations may not be kept up to date.', 'wpshadow' ),
					implode( ', ', $reasons ?: array( 'unknown' ) )
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/language_pack_update_capability',
				'meta'         => array(
					'can_update'    => false,
					'reasons'       => $reasons,
					'lang_dir'      => $lang_dir,
				),
			);
		}

		return null;
	}
}
