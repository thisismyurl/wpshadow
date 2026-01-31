<?php
/**
 * Plugin Information Disclosure Diagnostic
 *
 * Detects plugins leaking sensitive system information.
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
 * Diagnostic_Plugin_Information_Disclosure Class
 *
 * Identifies plugins that leak sensitive information.
 */
class Diagnostic_Plugin_Information_Disclosure extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-information-disclosure';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Information Disclosure';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins leaking sensitive system information';

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
		$disclosure_concerns = array();

		// Check for exposed PHP version (remove_action wp_head)
		global $wp_scripts;
		if ( ! has_action( 'wp_head', 'wp_generator' ) ) {
			// WordPress version is hidden
		} else {
			$disclosure_concerns[] = __( 'WordPress version publicly disclosed in <meta generator> tag.', 'wpshadow' );
		}

		// Check for plugins exposing their versions
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		$exposed_versions = 0;
		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for wp_enqueue_script/style with version parameter
			if ( preg_match( '/wp_enqueue_(?:script|style).*\$plugin_version|\$version/', $content ) ) {
				// This exposes version in CSS/JS URLs
				$exposed_versions++;
			}

			// Check for API endpoints returning version
			if ( preg_match( '/rest_ensure_response|wp_send_json.*version/', $content ) ) {
				// REST API might expose version
				$exposed_versions++;
			}
		}

		if ( $exposed_versions > 0 ) {
			$disclosure_concerns[] = sprintf(
				/* translators: %d: plugin count */
				__( '%d plugins may expose version numbers in asset URLs (facilitates targeted attacks).', 'wpshadow' ),
				$exposed_versions
			);
		}

		// Check for directory listing exposure
		$plugin_readme_count = 0;
		foreach ( $active_plugins as $plugin ) {
			$plugin_dir = dirname( $plugins_dir . '/' . $plugin );
			if ( file_exists( $plugin_dir . '/readme.txt' ) ) {
				$plugin_readme_count++;
			}
		}

		if ( $plugin_readme_count > 0 ) {
			$disclosure_concerns[] = sprintf(
				/* translators: %d: count */
				__( '%d plugins have publicly accessible readme.txt files (exposes version and functionality).', 'wpshadow' ),
				$plugin_readme_count
			);
		}

		// Check for sensitive data in meta tags
		if ( preg_match( '/<meta\s+name\s*=\s*["\']author["\']|admin_email|siteurl/', wp_get_document_title() ) ) {
			$disclosure_concerns[] = __( 'Sensitive data may be exposed in HTML meta tags or comments.', 'wpshadow' );
		}

		if ( ! empty( $disclosure_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', array_slice( $disclosure_concerns, 0, 3 ) ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'details'      => array(
					'concerns'       => $disclosure_concerns,
					'exposed_versions' => $exposed_versions,
				),
				'kb_link'      => 'https://wpshadow.com/kb/information-disclosure',
			);
		}

		return null;
	}
}
