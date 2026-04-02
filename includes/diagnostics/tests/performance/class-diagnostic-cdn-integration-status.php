<?php
/**
 * CDN Integration Status Diagnostic
 *
 * Tests if CDN is properly serving media files and URLs are rewritten.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CDN_Integration_Status Class
 *
 * Validates that CDN URL rewriting is working and media URLs resolve correctly.
 *
 * @since 1.6093.1200
 */
class Diagnostic_CDN_Integration_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'cdn-integration-status';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'CDN Integration Status';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests if CDN is properly serving media files and URLs are rewritten';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - URL rewriting for media
	 * - CDN host availability
	 * - Common CDN plugin settings
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$cdn_hits = 0;
		$cdn_errors = 0;

		$upload_dir = wp_upload_dir();
		$base_host  = Diagnostic_URL_And_Pattern_Helper::get_domain( $upload_dir['baseurl'] );

		// Detect active CDN plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$cdn_plugins = array(
			'wp-rocket'                 => __( 'WP Rocket CDN settings', 'wpshadow' ),
			'w3-total-cache'            => __( 'W3 Total Cache CDN settings', 'wpshadow' ),
			'cdn-enabler'               => __( 'CDN Enabler active', 'wpshadow' ),
			'jetpack'                   => __( 'Jetpack Site Accelerator', 'wpshadow' ),
			'amazon-s3-and-cloudfront'  => __( 'S3/CloudFront offload', 'wpshadow' ),
			'wp-offload-media'          => __( 'Offload Media active', 'wpshadow' ),
			'cloudflare'                => __( 'Cloudflare plugin active', 'wpshadow' ),
		);

		$active_cdn_plugins = array();
		foreach ( $cdn_plugins as $slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $slug ) ) {
					$active_cdn_plugins[ $slug ] = $message;
					break;
				}
			}
		}

		if ( empty( $active_cdn_plugins ) ) {
			$issues[] = __( 'No CDN plugin detected - media is served directly from origin', 'wpshadow' );
		}

		// Check sample media URLs to see if host differs (indicates CDN rewriting).
		global $wpdb;
		$attachment_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID
				FROM {$wpdb->posts}
				WHERE post_type = %s
				ORDER BY post_date DESC
				LIMIT 10",
				'attachment'
			)
		);

		foreach ( $attachment_ids as $attachment_id ) {
			$url = wp_get_attachment_url( $attachment_id );
			if ( empty( $url ) ) {
				continue;
			}

			$url_host = Diagnostic_URL_And_Pattern_Helper::get_domain( $url );
			if ( ! empty( $url_host ) && $base_host !== $url_host ) {
				$cdn_hits++;
			}

			$response = Diagnostic_Request_Helper::head_result(
				$url,
				array(
					'timeout'     => 5,
					'redirection' => 2,
				)
			);

			if ( ! $response['success'] ) {
				$cdn_errors++;
				continue;
			}

			$status = (int) $response['code'];
			if ( 400 <= $status ) {
				$cdn_errors++;
			}
		}

		if ( ! empty( $active_cdn_plugins ) && 0 === $cdn_hits ) {
			$issues[] = __( 'CDN plugin is active but media URLs are not rewritten', 'wpshadow' );
		}

		if ( 0 < $cdn_errors ) {
			$issues[] = sprintf(
				/* translators: %d: number of errors */
				_n(
					'%d media URL failed when testing CDN responses',
					'%d media URLs failed when testing CDN responses',
					$cdn_errors,
					'wpshadow'
				),
				$cdn_errors
			);
		}

		// Check for upload_url_path override (can break CDN rewriting).
		$upload_url_path = get_option( 'upload_url_path' );
		if ( ! empty( $upload_url_path ) && false === strpos( $upload_url_path, $base_host ) ) {
			$issues[] = __( 'upload_url_path is set to a different host - verify CDN configuration', 'wpshadow' );
		}

		foreach ( $active_cdn_plugins as $message ) {
			$issues[] = sprintf(
				/* translators: %s: message */
				__( 'Plugin note: %s', 'wpshadow' ),
				$message
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d CDN integration issue detected',
						'%d CDN integration issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cdn-integration-status',
				'details'      => array(
					'issues'          => $issues,
					'cdn_hits'        => $cdn_hits,
					'cdn_errors'      => $cdn_errors,
					'cdn_plugins'     => array_keys( $active_cdn_plugins ),
				),
			);
		}

		return null;
	}
}
