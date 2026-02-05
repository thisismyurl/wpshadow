<?php
/**
 * Media CDN Integration Status Treatment
 *
 * Tests whether a CDN is serving media files by validating
 * URL rewriting and CDN plugin configuration.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1615
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Treatments\Helpers\Treatment_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_CDN_Integration_Status Class
 *
 * Checks for CDN plugins and verifies media URLs are
 * being rewritten to CDN hosts.
 *
 * @since 1.6033.1615
 */
class Treatment_Media_CDN_Integration_Status extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-cdn-integration-status';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CDN Integration Status';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if CDN is properly serving media files';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$cdn_plugins = array(
			'cdn-enabler/cdn-enabler.php'                 => 'CDN Enabler',
			'w3-total-cache/w3-total-cache.php'           => 'W3 Total Cache',
			'wp-super-cache/wp-cache.php'                 => 'WP Super Cache',
			'wp-rocket/wp-rocket.php'                     => 'WP Rocket',
			'jetpack/jetpack.php'                         => 'Jetpack',
			'swift-performance-lite/performance.php'      => 'Swift Performance',
		);

		$active_cdn = array();
		foreach ( $cdn_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$active_cdn[] = $plugin_name;
			}
		}

		$site_host = Treatment_URL_And_Pattern_Helper::get_domain( home_url() );
		$sample = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 3,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$cdn_rewrite_detected = false;
		foreach ( $sample as $attachment ) {
			$url = wp_get_attachment_url( $attachment->ID );
			if ( empty( $url ) ) {
				continue;
			}
			$url_host = Treatment_URL_And_Pattern_Helper::get_domain( $url );
			if ( ! empty( $url_host ) && $url_host !== $site_host ) {
				$cdn_rewrite_detected = true;
				break;
			}
		}

		if ( ! empty( $active_cdn ) && ! $cdn_rewrite_detected ) {
			$issues[] = __( 'CDN plugin is active but media URLs are not being rewritten to a CDN host', 'wpshadow' );
		}

		if ( empty( $active_cdn ) ) {
			global $wpdb;
			$total = (int) $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
			);
			if ( $total > 5000 ) {
				$issues[] = __( 'Large media library detected without CDN; consider using a CDN for faster delivery', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-cdn-integration-status',
			);
		}

		return null;
	}
}
