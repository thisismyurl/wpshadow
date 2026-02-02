<?php
/**
 * Cross-Site Media Sharing Diagnostic
 *
 * Tests media library sharing between network sites.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cross-Site Media Sharing Diagnostic Class
 *
 * Verifies media library sharing functionality in multisite networks,
 * including proper permissions and access control.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Cross_Site_Media_Sharing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cross-site-media-sharing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cross-Site Media Sharing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media library sharing between network sites';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check for cross-site media sharing plugins.
		$media_sharing_plugins = array(
			'multisite-global-media/multisite-global-media.php',
			'network-shared-media/network-shared-media.php',
			'multisite-media-display/multisite-media-display.php',
		);

		$has_sharing = false;
		foreach ( $media_sharing_plugins as $plugin ) {
			if ( is_plugin_active_for_network( $plugin ) ) {
				$has_sharing = true;
				break;
			}
		}

		if ( ! $has_sharing ) {
			// Check for built-in switch_to_blog capability.
			if ( ! function_exists( 'switch_to_blog' ) ) {
				$issues[] = __( 'Multisite blog switching functions are not available', 'wpshadow' );
			}

			// No dedicated sharing plugin - may rely on custom implementation.
			$has_custom_sharing = has_filter( 'wp_get_attachment_url' );
			if ( ! $has_custom_sharing ) {
				$issues[] = __( 'No cross-site media sharing plugin detected and no custom implementation found', 'wpshadow' );
			}
		}

		// Check if upload directories are properly configured per-site.
		$upload_dir = wp_upload_dir();
		if ( empty( $upload_dir['baseurl'] ) || strpos( $upload_dir['baseurl'], '/sites/' ) === false ) {
			$issues[] = __( 'Upload directory structure may not support per-site media isolation', 'wpshadow' );
		}

		// Check for proper capability checks.
		if ( ! current_user_can( 'upload_files' ) && is_user_logged_in() ) {
			// User is logged in but can't upload.
			$issues[] = __( 'Current user lacks upload_files capability', 'wpshadow' );
		}

		// Check for network-wide media query support.
		if ( $has_sharing ) {
			// If sharing plugin exists, check if it modifies attachment queries.
			$has_query_filter = has_filter( 'pre_get_posts' );
			if ( ! $has_query_filter ) {
				// Sharing plugin may not be properly filtering queries.
			}
		}

		// Check for proper URL rewriting across sites.
		if ( is_subdomain_install() ) {
			// Subdomain multisite - check if media URLs are accessible.
			$current_blog_id = get_current_blog_id();
			switch_to_blog( 1 );
			$main_upload_dir = wp_upload_dir();
			restore_current_blog();

			// URLs should be accessible cross-domain.
			if ( strpos( $main_upload_dir['baseurl'], get_site_url( 1 ) ) === false ) {
				$issues[] = __( 'Media URLs may not be properly configured for cross-site access', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cross-site-media-sharing',
			);
		}

		return null;
	}
}
