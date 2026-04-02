<?php
/**
 * Network Admin Media Oversight Diagnostic
 *
 * Tests network admin ability to view/manage all site media.
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
 * Network Admin Media Oversight Diagnostic Class
 *
 * Verifies network super admin ability to view and manage media
 * across all sites in the network with proper permissions.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Network_Admin_Media_Oversight extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'network-admin-media-oversight';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Network Admin Media Oversight';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests network admin ability to view/manage all site media';

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

		// Check if network admin upload page exists.
		if ( is_network_admin() ) {
			$network_upload_url = network_admin_url( 'upload.php' );
			if ( empty( $network_upload_url ) ) {
				$issues[] = __( 'Network admin media page is not accessible', 'wpshadow' );
			}
		}

		// Check for network media management plugins.
		$oversight_plugins = array(
			'network-media-library/network-media-library.php',
			'multisite-media-display/multisite-media-display.php',
		);

		$has_oversight = false;
		foreach ( $oversight_plugins as $plugin ) {
			if ( is_plugin_active_for_network( $plugin ) ) {
				$has_oversight = true;
				break;
			}
		}

		if ( ! $has_oversight ) {
			$issues[] = __( 'No network media oversight plugin detected', 'wpshadow' );
		}

		// Check super admin capabilities.
		if ( is_super_admin() ) {
			// Super admin should have manage_network capability.
			if ( ! current_user_can( 'manage_network' ) ) {
				$issues[] = __( 'Super admin missing manage_network capability', 'wpshadow' );
			}

			// Super admin should be able to delete_sites.
			if ( ! current_user_can( 'delete_sites' ) ) {
				$issues[] = __( 'Super admin missing delete_sites capability', 'wpshadow' );
			}
		}

		// Check if network admin can switch to any blog.
		if ( ! function_exists( 'switch_to_blog' ) ) {
			$issues[] = __( 'Blog switching functions not available for oversight', 'wpshadow' );
		}

		// Check for network admin media queries.
		if ( is_network_admin() ) {
			// Verify ability to query attachments from all sites.
			$has_network_query = has_filter( 'pre_get_posts' );
			if ( ! $has_network_query ) {
				// May not be able to query cross-site media.
			}
		}

		// Check for proper upload filtering by super admins.
		$upload_file_types = get_site_option( 'upload_filetypes' );
		if ( empty( $upload_file_types ) ) {
			$issues[] = __( 'Network-wide upload file types not configured', 'wpshadow' );
		}

		// Check if network admin can modify upload settings.
		if ( is_super_admin() ) {
			$can_manage_options = current_user_can( 'manage_network_options' );
			if ( ! $can_manage_options ) {
				$issues[] = __( 'Network admin cannot manage network options', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/network-admin-media-oversight',
			);
		}

		return null;
	}
}
