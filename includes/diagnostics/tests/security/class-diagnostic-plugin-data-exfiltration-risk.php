<?php
/**
 * Plugin Data Exfiltration Risk Diagnostic
 *
 * Detects plugins sending data to external servers without consent.
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
 * Diagnostic_Plugin_Data_Exfiltration_Risk Class
 *
 * Identifies plugins sending data to external servers.
 */
class Diagnostic_Plugin_Data_Exfiltration_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-data-exfiltration-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Data Exfiltration Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins sending data to external servers without consent';

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
		$exfil_concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		$data_sending = array();

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for wp_remote_post/get to external URLs
			if ( preg_match( '/wp_remote_(?:post|get).*(?:https?:\/\/)?(?:api\.|analytics\.|tracking\.|telemetry\.)/', $content ) ) {
				$data_sending[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Sends data to external analytics/tracking services.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for sending post content/metadata
			if ( preg_match( '/wp_remote_post.*(\$post|post_content|post_title|post_meta|user_email)/', $content ) ) {
				$data_sending[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: May send post content or user data to external servers.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for sending database backups/exports
			if ( preg_match( '/wp_remote_post.*(?:backup|export|database|sql)/', $content ) ) {
				$data_sending[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: May send database backups to external servers.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for sending admin email or user information
			if ( preg_match( '/wp_remote_(?:post|get).*(?:admin_email|get_option.*email|get_userdata)/', $content ) ) {
				$data_sending[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Sends admin email or user information to external servers.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $data_sending ) ) {
			$exfil_concerns = $data_sending;
		}

		if ( ! empty( $exfil_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count, %s: details */
					__( '%d plugins send data to external servers: %s', 'wpshadow' ),
					count( $exfil_concerns ),
					implode( ' | ', array_slice( $exfil_concerns, 0, 2 ) )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'      => array(
					'data_exfil_plugins' => $exfil_concerns,
				),
				'kb_link'      => 'https://wpshadow.com/kb/data-exfiltration',
			);
		}

		return null;
	}
}
