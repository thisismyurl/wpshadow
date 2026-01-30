<?php
/**
 * Block Editor Script Dependencies Diagnostic
 *
 * Detects missing or incorrect block script dependencies.
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
 * Diagnostic_Block_Editor_Script_Dependencies
 *
 * Checks for missing script dependencies in block registrations.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Block_Editor_Script_Dependencies extends Diagnostic_Base {

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

		// Can't check if block registry not available.
		if ( ! function_exists( 'get_block_editor_settings' ) ) {
			return null;
		}

		$missing_deps = array();

		// Check registered block types for dependency issues.
		if ( function_exists( 'get_registered_block_types' ) ) {
			$block_types = get_registered_block_types();

			foreach ( $block_types as $block_type ) {
				if ( empty( $block_type->script ) && empty( $block_type->style ) ) {
					continue; // Block has no assets.
				}

				// Verify script dependencies.
				if ( ! empty( $block_type->script ) ) {
					$script = wp_scripts()->query( $block_type->script );
					if ( $script && ! empty( $script->deps ) ) {
						foreach ( $script->deps as $dep ) {
							if ( ! wp_scripts()->query( $dep ) ) {
								$missing_deps[] = array(
									'block'      => $block_type->name,
									'script'     => $block_type->script,
									'missing_dep' => $dep,
								);
							}
						}
					}
				}
			}
		}

		if ( ! empty( $missing_deps ) ) {
			return array(
				'id'           => 'block-editor-script-dependencies',
				'title'        => __( 'Block Editor Has Missing Script Dependencies', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: count */
					__( 'Found %d missing script dependencies in block registrations. This can cause blocks to malfunction in the editor.', 'wpshadow' ),
					count( $missing_deps )
				),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/block_editor_script_dependencies',
				'meta'         => array(
					'missing_deps_count' => count( $missing_deps ),
					'sample_issues'      => array_slice( $missing_deps, 0, 3 ),
				),
			);
		}

		return null;
	}
}
