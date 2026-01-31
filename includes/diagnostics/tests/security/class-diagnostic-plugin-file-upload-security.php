<?php
/**
 * Plugin File Upload Security Diagnostic
 *
 * Detects plugins with insecure file upload handling.
 *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_File_Upload_Security Class
 *
 * Identifies plugins with insecure file upload handling.
 */
class Diagnostic_Plugin_File_Upload_Security extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-file-upload-security';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin File Upload Security';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins with insecure file upload handling';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$upload_concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for move_uploaded_file without proper validation
			if ( preg_match( '/move_uploaded_file\s*\(/', $content ) ) {
				// Check if it has MIME type checking
				if ( ! preg_match( '/wp_check_filetype|mime_type|getimagesize/', $content ) ) {
					$upload_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Uploads files without MIME type validation.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}

				// Check for extension whitelist
				if ( ! preg_match( '/allowed_extensions|file_types|extension/', $content ) ) {
					$upload_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: No file extension whitelist detected.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for $_FILES access without sanitization
			if ( preg_match( '/\$_FILES/', $content ) && ! preg_match( '/sanitize_file_name|wp_handle_upload/', $content ) ) {
				$upload_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Accesses $_FILES without proper sanitization.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $upload_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: concern count, %s: details */
					__( '%d file upload security concerns detected: %s', 'wpshadow' ),
					count( $upload_concerns ),
					implode( ' ', array_slice( $upload_concerns, 0, 3 ) )
				),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'details'      => array(
					'concerns' => $upload_concerns,
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-file-upload-security',
			);
		}

		return null;
	}
}
