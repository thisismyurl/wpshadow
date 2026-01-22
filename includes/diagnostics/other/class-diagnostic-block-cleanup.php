<?php
declare(strict_types=1);
/**
 * Block Asset Cleanup Diagnostic
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if optional block assets are being enqueued unnecessarily on the front-end.
 */
class Diagnostic_Block_Cleanup extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! is_admin() && self::has_block_assets() ) {
			return array(
				'id'           => 'block-assets-loaded',
				'title'        => 'Gutenberg Block Assets Loading Everywhere',
				'description'  => 'Block library styles/scripts load on all pages. Disable them on front-end pages that don’t use blocks.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/disable-gutenberg-assets/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=block-cleanup',
				'auto_fixable' => true,
				'threat_level' => 30,
			);
		}

		return null;
	}

	private static function has_block_assets() {
		wp_enqueue_scripts(); // Populate scripts/styles queue.
		global $wp_styles, $wp_scripts;
		$block_handles = array( 'wp-block-library', 'wp-block-library-theme', 'wc-blocks-style' );

		if ( isset( $wp_styles ) ) {
			foreach ( $block_handles as $handle ) {
				if ( isset( $wp_styles->registered[ $handle ] ) ) {
					return true;
				}
			}
		}

		if ( isset( $wp_scripts ) ) {
			foreach ( $block_handles as $handle ) {
				if ( isset( $wp_scripts->registered[ $handle ] ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
