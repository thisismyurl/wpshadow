<?php
/**
 * Treatment: Eliminate Mixed Content
 *
 * Handles two categories of mixed content detected by the diagnostic:
 *
 * 1. WordPress Site URL stored as http:// while the site runs on https://.
 *    Fix: updates the siteurl option to match the home URL scheme.
 *    Undo: restores the previous siteurl value.
 *
 * 2. HTTP asset references in page HTML (same-domain src/href over http://).
 *    This case requires a database-wide search-replace across post content
 *    which is beyond this treatment's scope. The treatment returns a message
 *    directing the user to use WP-CLI or the Better Search Replace plugin.
 *
 * Risk level: high — siteurl is a core WordPress option; an incorrect update
 * can break admin access. The previous value is stored before modification.
 *
 * @package WPShadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fixes siteurl http/https mismatch to eliminate mixed content.
 */
class Treatment_Mixed_Content_Eliminated extends Treatment_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'mixed-content-eliminated';

	/** @return string */
	public static function get_risk_level(): string {
		return 'high';
	}

	/**
	 * Apply the treatment.
	 *
	 * Only handles the siteurl mismatch case. Inline HTML mixed content
	 * requires a manual search-replace and is flagged with instructions.
	 *
	 * @return array
	 */
	public static function apply() {
		$home_url   = home_url();
		$siteurl    = get_option( 'siteurl', '' );

		// Case 1: siteurl is http:// but home is https://.
		if ( 0 === strpos( $home_url, 'https://' ) && 0 === strpos( $siteurl, 'http://' ) ) {
			$new_siteurl = 'https://' . substr( $siteurl, strlen( 'http://' ) );

			// Store previous value for undo.
			update_option( 'wpshadow_prev_siteurl', $siteurl, false );

			update_option( 'siteurl', $new_siteurl );

			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: 1: old URL, 2: new URL */
					__( 'Site URL updated from %1$s to %2$s. This resolves the http/https mismatch that was causing mixed content warnings.', 'wpshadow' ),
					$siteurl,
					$new_siteurl
				),
				'details' => array(
					'previous_siteurl' => $siteurl,
					'new_siteurl'      => $new_siteurl,
				),
			);
		}

		// Case 2: Mixed content found in page HTML — requires search-replace.
		return array(
			'success' => false,
			'message' => __( 'Mixed content in your page HTML cannot be fixed automatically. Use WP-CLI (wp search-replace "http://yourdomain.com" "https://yourdomain.com" --all-tables) or the Better Search Replace plugin to update stored http:// references to https://.', 'wpshadow' ),
		);
	}

	/**
	 * Restore the previous siteurl value.
	 *
	 * @return array
	 */
	public static function undo() {
		$previous = get_option( 'wpshadow_prev_siteurl' );

		if ( ! $previous ) {
			return array(
				'success' => false,
				'message' => __( 'No previous siteurl value stored — nothing to restore.', 'wpshadow' ),
			);
		}

		update_option( 'siteurl', $previous );
		delete_option( 'wpshadow_prev_siteurl' );

		return array(
			'success' => true,
			/* translators: %s: restored URL */
			'message' => sprintf(
				__( 'Site URL restored to %s.', 'wpshadow' ),
				$previous
			),
		);
	}
}
