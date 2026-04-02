<?php
/**
 * Network-Wide Media Library Access Diagnostic
 *
 * Tests global media library functionality across network.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Network-Wide Media Library Access Diagnostic Class
 *
 * Verifies global media library functionality for network installations,
 * including network admin capabilities and cross-site access.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Network_Wide_Media_Library_Access extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'network-wide-media-library-access';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Network-Wide Media Library Access';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests global media library functionality across network';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check if network admin can access all site media.
		if ( ! current_user_can( 'manage_network' ) && is_network_admin() ) {
			// In network admin but don't have capability.
			$issues[] = __( 'Network admin capabilities not properly configured', 'wpshadow' );
		}

		// Check for network media library plugins.
		$network_plugins = array(
			'multisite-global-media/multisite-global-media.php',
			'network-media-library/network-media-library.php',
		);

		$has_network_library = false;
		foreach ( $network_plugins as $plugin ) {
			if ( is_plugin_active_for_network( $plugin ) ) {
				$has_network_library = true;
				break;
			}
		}

		if ( ! $has_network_library ) {
			$issues[] = __( 'No network-wide media library plugin detected', 'wpshadow' );
		}

		// Check if switch_to_blog is available.
		if ( ! function_exists( 'switch_to_blog' ) ) {
			$issues[] = __( 'Blog switching functions not available for cross-site media access', 'wpshadow' );
		}

		// Check network upload settings.
		$upload_space_check_disabled = get_site_option( 'upload_space_check_disabled' );
		if ( empty( $upload_space_check_disabled ) ) {
			// Space check is enabled - verify per-site quotas exist.
			$blog_upload_space = get_site_option( 'blog_upload_space' );
			if ( empty( $blog_upload_space ) ) {
				$issues[] = __( 'Network upload space limits not configured', 'wpshadow' );
			}
		}

		// Check if main site can serve media for other sites.
		$current_blog_id = get_current_blog_id();
		if ( $current_blog_id !== 1 ) {
			// On subsite - check if main site media is accessible.
			switch_to_blog( 1 );
			$main_upload_dir = wp_upload_dir();
			restore_current_blog();

			if ( ! empty( $main_upload_dir['error'] ) ) {
				$issues[] = sprintf(
					/* translators: %s: error message */
					__( 'Main site upload directory error: %s', 'wpshadow' ),
					$main_upload_dir['error']
				);
			}
		}

		// Check for network-wide media admin page.
		if ( is_network_admin() ) {
			$network_admin_url = network_admin_url( 'upload.php' );
			if ( empty( $network_admin_url ) ) {
				$issues[] = __( 'Network media admin page not accessible', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/network-wide-media-library-access',
			);
		}

		return null;
	}
}
