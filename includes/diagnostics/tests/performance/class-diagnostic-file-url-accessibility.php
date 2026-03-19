<?php
/**
 * File URL Accessibility Diagnostic
 *
 * Tests whether uploaded media files are accessible via their URLs.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_File_URL_Accessibility Class
 *
 * Validates that media URLs return valid HTTP responses. Broken URLs can
 * indicate 404s, CDN misconfiguration, or permission issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_File_URL_Accessibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'file-url-accessibility';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'File URL Accessibility';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether uploaded media files are accessible via their URLs';

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
	 * - HTTP status of media URLs
	 * - Base upload URL correctness
	 * - CDN or offload configuration signals
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$inaccessible = 0;
		$errors       = array();

		global $wpdb;

		// Sample recent attachments.
		$attachment_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID
				FROM {$wpdb->posts}
				WHERE post_type = %s
				ORDER BY post_date DESC
				LIMIT 15",
				'attachment'
			)
		);

		if ( empty( $attachment_ids ) ) {
			return null;
		}

		// Check base upload URL.
		$upload_dir = wp_upload_dir();
		if ( empty( $upload_dir['baseurl'] ) ) {
			$issues[] = __( 'Upload base URL is empty - media URLs may be invalid', 'wpshadow' );
		}

		foreach ( $attachment_ids as $attachment_id ) {
			$url = wp_get_attachment_url( $attachment_id );
			if ( empty( $url ) ) {
				$inaccessible++;
				$errors[] = __( 'Missing attachment URL', 'wpshadow' );
				continue;
			}

			$response = Diagnostic_Request_Helper::head_result(
				$url,
				array(
					'timeout'     => 5,
					'redirection' => 2,
				)
			);

			if ( ! $response['success'] ) {
				$inaccessible++;
				$errors[] = $response['error_message'];
				continue;
			}

			$status = (int) $response['code'];
			if ( 400 <= $status ) {
				$inaccessible++;
				$errors[] = sprintf(
					/* translators: %d: HTTP status */
					__( 'HTTP %d returned for media URL', 'wpshadow' ),
					$status
				);
			}
		}

		if ( 0 < $inaccessible ) {
			$issues[] = sprintf(
				/* translators: %d: number of files */
				_n(
					'%d media URL is not accessible (404/timeout)',
					'%d media URLs are not accessible (404/timeout)',
					$inaccessible,
					'wpshadow'
				),
				$inaccessible
			);
		}

		// Check for HTTPS mismatch.
		if ( is_ssl() && 0 === strpos( $upload_dir['baseurl'], 'http://' ) ) {
			$issues[] = __( 'Site uses HTTPS but upload base URL is HTTP - may cause mixed content or 404s', 'wpshadow' );
		}

		// Detect offload/CDN plugins that might need configuration.
		$active_plugins = get_option( 'active_plugins', array() );
		$cdn_plugins = array(
			'amazon-s3-and-cloudfront' => __( 'Media offload to S3 - ensure bucket permissions and CDN URLs are correct', 'wpshadow' ),
			'wp-offload-media'         => __( 'Media offload enabled - verify remote URLs are accessible', 'wpshadow' ),
			'cloudflare'               => __( 'Cloudflare active - cached 404s may block media URLs', 'wpshadow' ),
		);

		foreach ( $cdn_plugins as $plugin_slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$issues[] = sprintf(
						/* translators: %s: message */
						__( 'Plugin note: %s', 'wpshadow' ),
						$message
					);
					break;
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d accessibility issue detected for media URLs',
						'%d accessibility issues detected for media URLs',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-url-accessibility',
				'details'      => array(
					'issues'       => $issues,
					'inaccessible' => $inaccessible,
					'error_sample' => array_slice( $errors, 0, 3 ),
				),
			);
		}

		return null;
	}
}
