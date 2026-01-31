<?php
/**
 * Block Metadata Parsing Diagnostic
 *
 * Verifies block.json files load and parse correctly.
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
 * Diagnostic_Block_Metadata_Parsing
 *
 * Checks that block.json files in theme and plugins parse correctly.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Block_Metadata_Parsing extends Diagnostic_Base {

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

		$parse_errors = array();

		// Check theme block.json files.
		$theme_dir = get_template_directory();
		$block_jsons = self::find_block_json_files( $theme_dir );

		foreach ( $block_jsons as $file ) {
			if ( ! self::is_valid_json( $file ) ) {
				$parse_errors[] = str_replace( ABSPATH, '', $file );
			}
		}

		// Check plugin block.json files.
		$plugins = get_plugins();
		foreach ( array_keys( $plugins ) as $plugin_file ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
			$block_jsons = self::find_block_json_files( $plugin_dir );

			foreach ( $block_jsons as $file ) {
				if ( ! self::is_valid_json( $file ) ) {
					$parse_errors[] = str_replace( ABSPATH, '', $file );
				}
			}
		}

		if ( ! empty( $parse_errors ) ) {
			return array(
				'id'           => 'block-metadata-parsing',
				'title'        => __( 'Invalid block.json Files Detected', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: files */
					__( 'Found %d block.json files with invalid JSON: %s. Fix JSON syntax errors to enable block registration.', 'wpshadow' ),
					count( $parse_errors ),
					implode( ', ', array_slice( $parse_errors, 0, 3 ) ) . ( count( $parse_errors ) > 3 ? ' +' . ( count( $parse_errors ) - 3 ) . ' more' : '' )
				),
				'severity'     => 'high',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/block_metadata_parsing',
				'meta'         => array(
					'invalid_files' => count( $parse_errors ),
					'sample_files'  => array_slice( $parse_errors, 0, 5 ),
				),
			);
		}

		return null;
	}

	/**
	 * Find all block.json files in directory.
	 *
	 * @since  1.2601.2112
	 * @param  string $dir Directory path.
	 * @return array File paths.
	 */
	private static function find_block_json_files( $dir ) {
		$files = array();

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		try {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
				\RecursiveIteratorIterator::LEAVES_ONLY
			);

			foreach ( $iterator as $file ) {
				if ( $file->isFile() && 'block.json' === $file->getFilename() ) {
					$files[] = $file->getRealPath();
				}
			}
		} catch ( \Exception $e ) {
			// Directory not accessible.
		}

		return $files;
	}

	/**
	 * Check if JSON file is valid.
	 *
	 * @since  1.2601.2112
	 * @param  string $file File path.
	 * @return bool True if valid JSON, false otherwise.
	 */
	private static function is_valid_json( $file ) {
		if ( ! is_readable( $file ) ) {
			return false;
		}

		$content = file_get_contents( $file );
		if ( false === $content ) {
			return false;
		}

		json_decode( $content );
		return JSON_ERROR_NONE === json_last_error();
	}
}
