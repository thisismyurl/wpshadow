<?php
/**
 * Media CDN Not Configured Diagnostic
 *
 * Checks if media files are served via CDN.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_CDN_Not_Configured Class
 *
 * Detects when media files lack CDN delivery.
 * CDN improves page load speed and reduces server bandwidth costs.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Media_CDN_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-cdn-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media CDN Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media is served via CDN';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * - Jetpack Photon/Image CDN
	 * - WP Rocket CDN integration
	 * - Cloudflare configuration
	 * - Custom CDN URL settings
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_cdn = false;

		// Check for Jetpack Photon/Image CDN.
		if ( class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'is_module_active' ) ) {
			if ( \Jetpack::is_module_active( 'photon' ) ) {
				$has_cdn = true;
			}
		}

		// Check for WP Rocket CDN.
		if ( defined( 'WP_ROCKET_CDN_CNAMES' ) && ! empty( WP_ROCKET_CDN_CNAMES ) ) {
			$has_cdn = true;
		}

		// Check for CDN Enabler plugin.
		if ( is_plugin_active( 'cdn-enabler/cdn-enabler.php' ) ) {
			$options = get_option( 'cdn_enabler' );
			if ( isset( $options['url'] ) && ! empty( $options['url'] ) ) {
				$has_cdn = true;
			}
		}

		// Check for custom CDN URL in upload URL.
		$upload_dir = wp_upload_dir();
		if ( isset( $upload_dir['baseurl'] ) && false !== strpos( $upload_dir['baseurl'], 'cdn' ) ) {
			$has_cdn = true;
		}

		if ( ! $has_cdn ) {
			// Count media files to determine severity.
			$media_count = wp_count_attachments();
			$total_media = array_sum( (array) $media_count );

			// Only flag if site has significant media (50+ files).
			if ( $total_media > 50 ) {
				$finding = array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %d: number of media files */
						__( 'Your %d media files are served directly from your web server instead of a CDN. This results in: slower page loads (especially for international visitors), higher bandwidth costs (you pay for every image view), and poor mobile performance (slow connections suffer most). CDN delivery: reduces load time by 50-70%%, cuts bandwidth costs by 60%%, and improves SEO rankings (Google prioritizes fast sites).', 'wpshadow' ),
						number_format_i18n( $total_media )
					),
					'severity'     => 'medium',
					'threat_level' => 60,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/media-cdn-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'media_count' => $total_media,
					),
				);

				// Add upgrade path to Media modules (includes CDN integration).
				if ( ! Upgrade_Path_Helper::has_pro_product( 'media-image' ) ) {
					$finding = Upgrade_Path_Helper::add_upgrade_path(
						$finding,
						'media-image',
						'social-optimization',
						'https://wpshadow.com/kb/manual-cdn-setup'
					);
				}

				return $finding;
			}
		}

		return null;
	}
}
