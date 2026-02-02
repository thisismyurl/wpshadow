<?php
/**
 * External Media URL Handling Diagnostic
 *
 * Tests handling of external/remote media URLs.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_External_Media_URL_Handling Class
 *
 * Detects attachments pointing to external URLs and tests accessibility.
 * External URLs can break if hotlinking is blocked or remote hosts go down.
 *
 * @since 1.2601.2148
 */
class Diagnostic_External_Media_URL_Handling extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'external-media-url-handling';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'External Media URL Handling';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests handling of external/remote media URLs';

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
	 * - External media URLs
	 * - Hotlink accessibility
	 * - Mixed protocol issues
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$external_count = 0;
		$failed_count   = 0;

		$upload_dir = wp_upload_dir();
		$base_host  = wp_parse_url( $upload_dir['baseurl'], PHP_URL_HOST );

		global $wpdb;

		// Find attachments with GUIDs that do not match the uploads base URL.
		$external_attachments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, guid
				FROM {$wpdb->posts}
				WHERE post_type = %s
				AND guid NOT LIKE %s
				ORDER BY post_date DESC
				LIMIT 15",
				'attachment',
				'%' . $wpdb->esc_like( $base_host ) . '%'
			)
		);

		foreach ( $external_attachments as $attachment ) {
			$external_count++;

			if ( empty( $attachment->guid ) ) {
				$failed_count++;
				continue;
			}

			$response = wp_remote_head(
				$attachment->guid,
				array(
					'timeout'     => 5,
					'redirection' => 2,
				)
			);

			if ( is_wp_error( $response ) ) {
				$failed_count++;
				continue;
			}

			$status = wp_remote_retrieve_response_code( $response );
			if ( 400 <= $status ) {
				$failed_count++;
			}
		}

		if ( 0 < $external_count ) {
			$issues[] = sprintf(
				/* translators: %d: number of attachments */
				_n(
					'%d attachment points to an external URL',
					'%d attachments point to external URLs',
					$external_count,
					'wpshadow'
				),
				$external_count
			);
		}

		if ( 0 < $failed_count ) {
			$issues[] = sprintf(
				/* translators: %d: number of failures */
				_n(
					'%d external media URL failed to respond',
					'%d external media URLs failed to respond',
					$failed_count,
					'wpshadow'
				),
				$failed_count
			);
		}

		// Check for protocol mismatch in external URLs.
		if ( is_ssl() ) {
			$http_external = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->posts}
					WHERE post_type = %s
					AND guid LIKE %s
					AND guid NOT LIKE %s",
					'attachment',
					'http://%',
					'%' . $wpdb->esc_like( $base_host ) . '%'
				)
			);

			if ( 0 < $http_external ) {
				$issues[] = sprintf(
					/* translators: %d: number of attachments */
					_n(
						'%d external media URL uses HTTP on an HTTPS site',
						'%d external media URLs use HTTP on an HTTPS site',
						$http_external,
						'wpshadow'
					),
					$http_external
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d external media URL issue detected',
						'%d external media URL issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/external-media-url-handling',
				'details'      => array(
					'issues'         => $issues,
					'external_count' => $external_count,
					'failed_count'   => $failed_count,
				),
			);
		}

		return null;
	}
}
