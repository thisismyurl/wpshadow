<?php
/**
 * Block Asset Registration Diagnostic
 *
 * Ensures blocks register CSS/JS properly.
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
 * Diagnostic_Block_Asset_Registration
 *
 * Verifies block assets (CSS, JS) are properly registered.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Block_Asset_Registration extends Diagnostic_Base {

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

		if ( ! function_exists( 'get_registered_block_types' ) ) {
			return null;
		}

		$unregistered_assets = array();
		$block_types         = get_registered_block_types();

		foreach ( $block_types as $block_type ) {
			// Check script is registered.
			if ( ! empty( $block_type->script ) ) {
				if ( ! wp_script_is( $block_type->script, 'registered' ) ) {
					$unregistered_assets[] = array(
						'block'  => $block_type->name,
						'type'   => 'script',
						'handle' => $block_type->script,
					);
				}
			}

			// Check style is registered.
			if ( ! empty( $block_type->style ) ) {
				if ( ! wp_style_is( $block_type->style, 'registered' ) ) {
					$unregistered_assets[] = array(
						'block'  => $block_type->name,
						'type'   => 'style',
						'handle' => $block_type->style,
					);
				}
			}
		}

		if ( ! empty( $unregistered_assets ) ) {
			return array(
				'id'           => 'block-asset-registration',
				'title'        => __( 'Block Assets Not Properly Registered', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: count */
					__( 'Found %d block assets (CSS/JS) that are not registered. This can cause blocks to appear broken or non-functional in the editor.', 'wpshadow' ),
					count( $unregistered_assets )
				),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/block_asset_registration',
				'meta'         => array(
					'unregistered_count' => count( $unregistered_assets ),
					'sample_assets'      => array_slice( $unregistered_assets, 0, 3 ),
				),
			);
		}

		return null;
	}
}
