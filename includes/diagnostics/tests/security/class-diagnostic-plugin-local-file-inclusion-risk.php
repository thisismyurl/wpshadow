<?php
/**
 * Plugin Local File Inclusion Risk Diagnostic
 *
 * Detects plugins vulnerable to Local File Inclusion attacks.
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
 * Diagnostic_Plugin_Local_File_Inclusion_Risk Class
 *
 * Identifies plugins vulnerable to LFI attacks.
 */
class Diagnostic_Plugin_Local_File_Inclusion_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-local-file-inclusion-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Local File Inclusion Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins vulnerable to Local File Inclusion attacks';

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
		$lfi_concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for file path from user input
			if ( preg_match( '/include\s*\(|require\s*\(|include_once\s*\(|require_once\s*\(/', $content ) ) {
				// Check for user input in path
				if ( preg_match( '/\$_(?:GET|POST|REQUEST|COOKIE)\[["\'].*["\'][^;]*include|require/', $content ) ) {
					$lfi_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Includes files based on user input ($_GET/$_POST).', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}

				// Check for path traversal (../)
				if ( preg_match( '/\.\.\/' ) ) {
					$lfi_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: May be vulnerable to path traversal (../) attacks.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for dynamic file loading from templates
			if ( preg_match( '/locate_template.*\$_(?:GET|POST)/', $content ) ) {
				$lfi_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Loads templates based on user input.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for plugin loading from user input
			if ( preg_match( '/load_plugin_textdomain.*\$_(?:GET|POST)/', $content ) ) {
				$lfi_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Loads text domains based on user input.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for stream wrappers (php://, file://, zip://)
			if ( preg_match( '/(?:php|file|zip|phar):\/\/.*\$_(?:GET|POST|REQUEST)/', $content ) ) {
				$lfi_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Uses stream wrappers with user input (LFI vector).', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $lfi_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count, %s: details */
					__( '%d local file inclusion risks detected: %s', 'wpshadow' ),
					count( $lfi_concerns ),
					implode( ' | ', array_slice( $lfi_concerns, 0, 2 ) )
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'details'      => array(
					'lfi_concerns' => $lfi_concerns,
				),
				'kb_link'      => 'https://wpshadow.com/kb/lfi-prevention',
			);
		}

		return null;
	}
}
