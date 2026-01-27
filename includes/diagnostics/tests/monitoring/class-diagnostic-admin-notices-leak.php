<?php
/**
 * Admin Notices Leak Diagnostic
 *
 * Detects admin notices rendered on the front end.
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
 * Diagnostic_Admin_Notices_Leak
 *
 * Checks if admin notices are visible on the public site, which can leak configuration details.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Admin_Notices_Leak extends Diagnostic_Base {

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

		$front_page = home_url( '/' );
		$response   = wp_remote_get( $front_page, array(
			'timeout'   => 5,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		) );

		if ( is_wp_error( $response ) ) {
			return null; // Unable to fetch front end.
		}

		$body = wp_remote_retrieve_body( $response );

		// Look for common admin notice classes in public HTML.
		$leak = false !== strpos( $body, 'class="notice' ) || false !== strpos( $body, 'class="update-nag' );

		if ( $leak ) {
			return array(
				'id'           => 'admin-notices-leak',
				'title'        => __( 'Admin Notices Visible on Front End', 'wpshadow' ),
				'description'  => __( 'Admin notices are being rendered on the public site. This can leak configuration details or debugging information to visitors. Restrict notices to the dashboard only.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin_notices_leak',
				'meta'         => array(
					'front_page' => $front_page,
				),
			);
		}

		return null;
	}
}
