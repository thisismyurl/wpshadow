<?php
/**
 * Enqueued Script Missing File Diagnostic
 *
 * Confirms enqueued JS files exist on disk.
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
 * Diagnostic_Enqueued_Script_Missing_File
 *
 * Detects enqueued scripts that reference missing files.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Enqueued_Script_Missing_File extends Diagnostic_Base {

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

		if ( ! did_action( 'wp_enqueue_scripts' ) && ! did_action( 'admin_enqueue_scripts' ) ) {
			return null; // Need enqueue phase completed to inspect.
		}

		$scripts = wp_scripts();
		if ( ! $scripts ) {
			return null;
		}

		$missing   = array();
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );

		foreach ( (array) $scripts->queue as $handle ) {
			$script = $scripts->registered[ $handle ] ?? null;
			if ( ! $script || empty( $script->src ) ) {
				continue;
			}

			$src = $script->src;
			if ( 0 === strpos( $src, '//' ) ) {
				$src = ( is_ssl() ? 'https:' : 'http:' ) . $src;
			}

			$parsed = wp_parse_url( $src );
			if ( empty( $parsed['host'] ) || $parsed['host'] === $site_host ) {
				$path = $parsed['path'] ?? '';
				if ( empty( $path ) ) {
					continue;
				}
				$full_path = wp_normalize_path( ABSPATH . ltrim( $path, '/' ) );
				if ( ! file_exists( $full_path ) ) {
					$missing[] = array(
						'handle' => $handle,
						'src'    => $src,
						'path'   => $full_path,
					);
				}
			}
		}

		if ( ! empty( $missing ) ) {
			return array(
				'id'           => 'enqueued-script-missing-file',
				'title'        => __( 'Enqueued Scripts Reference Missing Files', 'wpshadow' ),
				'description'  => __( 'One or more enqueued scripts point to files that do not exist on disk. This can break site functionality. Update or remove the missing assets.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/enqueued_script_missing_file',
				'meta'         => array(
					'missing_count' => count( $missing ),
					'sample'        => array_slice( $missing, 0, 3 ),
				),
			);
		}

		return null;
	}
}
