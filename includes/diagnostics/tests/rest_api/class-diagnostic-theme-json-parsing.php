<?php
/**
 * Theme JSON Parsing Diagnostic
 *
 * Confirms theme.json loads and validates correctly.
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
 * Diagnostic_Theme_Json_Parsing
 *
 * Checks that theme.json file parses and loads correctly for block editor settings.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Theme_Json_Parsing extends Diagnostic_Base {

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

		$theme_dir = get_template_directory();
		$theme_json_file = $theme_dir . '/theme.json';

		// If no theme.json, that's OK (optional file).
		if ( ! is_file( $theme_json_file ) ) {
			return null;
		}

		// Check if theme.json is readable and valid JSON.
		if ( ! is_readable( $theme_json_file ) ) {
			return array(
				'id'           => 'theme-json-parsing',
				'title'        => __( 'theme.json Not Readable', 'wpshadow' ),
				'description'  => __( 'theme.json file exists but is not readable. Check file permissions. The block editor may not load theme settings correctly.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme_json_parsing',
				'meta'         => array(
					'file'       => 'theme.json',
					'permission' => 'not-readable',
				),
			);
		}

		$content = file_get_contents( $theme_json_file );
		if ( false === $content ) {
			return null;
		}

		// Validate JSON syntax.
		$decoded = json_decode( $content );
		if ( null === $decoded && JSON_ERROR_NONE !== json_last_error() ) {
			return array(
				'id'           => 'theme-json-parsing',
				'title'        => __( 'theme.json Has Invalid JSON', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: JSON error */
					__( 'Your theme.json file contains invalid JSON: %s. The block editor will not load theme settings. Fix the JSON syntax.', 'wpshadow' ),
					json_last_error_msg()
				),
				'severity'     => 'high',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme_json_parsing',
				'meta'         => array(
					'file'  => 'theme.json',
					'error' => json_last_error_msg(),
				),
			);
		}

		return null;
	}
}
