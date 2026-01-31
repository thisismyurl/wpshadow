<?php
/**
 * External Link Quality Check Diagnostic
 *
 * Validates outbound links for quality, security, and functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * External Link Quality Check Class
 *
 * Tests external links for quality and security issues.
 *
 * @since 1.26028.1905
 */
class Diagnostic_External_Link_Quality_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'external-link-quality-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'External Link Quality Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates outbound links for quality, security, and functionality';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$link_audit = self::audit_external_links();
		
		if ( $link_audit['total_issues'] > 0 ) {
			$issues = array();
			
			if ( $link_audit['broken_links'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of broken links */
					__( '%d broken external links', 'wpshadow' ),
					$link_audit['broken_links']
				);
			}

			if ( $link_audit['http_on_https'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of HTTP links */
					__( '%d HTTP links on HTTPS site (mixed content)', 'wpshadow' ),
					$link_audit['http_on_https']
				);
			}

			if ( $link_audit['redirect_chains'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of redirect chains */
					__( '%d links with redirect chains', 'wpshadow' ),
					$link_audit['redirect_chains']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/external-link-quality-check',
				'meta'         => array(
					'total_external_links' => $link_audit['total_external_links'],
					'broken_links'         => $link_audit['broken_links'],
					'http_on_https'        => $link_audit['http_on_https'],
					'redirect_chains'      => $link_audit['redirect_chains'],
					'issues_found'         => $link_audit['total_issues'],
				),
			);
		}

		return null;
	}

	/**
	 * Audit external links.
	 *
	 * @since  1.26028.1905
	 * @return array Audit results.
	 */
	private static function audit_external_links() {
		global $wpdb;

		$audit = array(
			'total_external_links' => 0,
			'broken_links'         => 0,
			'http_on_https'        => 0,
			'redirect_chains'      => 0,
			'total_issues'         => 0,
		);

		$site_url = get_site_url();
		$parsed_site_url = wp_parse_url( $site_url );
		$site_host = isset( $parsed_site_url['host'] ) ? $parsed_site_url['host'] : '';
		$is_https = is_ssl();

		// Sample recent posts to avoid performance issues.
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_content
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				ORDER BY post_modified DESC
				LIMIT 30",
				'publish'
			)
		);

		$checked_urls = array();

		foreach ( $posts as $post ) {
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches );
			
			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $url ) {
					$parsed_url = wp_parse_url( $url );
					
					// Skip internal links.
					if ( ! isset( $parsed_url['host'] ) || $parsed_url['host'] === $site_host ) {
						continue;
					}

					// Skip if already checked.
					if ( in_array( $url, $checked_urls, true ) ) {
						continue;
					}

					$checked_urls[] = $url;
					++$audit['total_external_links'];

					// Check if HTTP on HTTPS site.
					if ( $is_https && 0 === strpos( $url, 'http://' ) ) {
						++$audit['http_on_https'];
						++$audit['total_issues'];
					}

					// Test link status (sample only to avoid timeout).
					if ( $audit['total_external_links'] <= 20 ) {
						$link_check = self::check_external_link( $url );
						
						if ( $link_check['broken'] ) {
							++$audit['broken_links'];
							++$audit['total_issues'];
						}

						if ( $link_check['has_redirects'] ) {
							++$audit['redirect_chains'];
							++$audit['total_issues'];
						}
					}
				}
			}
		}

		return $audit;
	}

	/**
	 * Check external link status.
	 *
	 * @since  1.26028.1905
	 * @param  string $url URL to check.
	 * @return array Check results.
	 */
	private static function check_external_link( $url ) {
		$result = array(
			'broken'        => false,
			'has_redirects' => false,
		);

		$response = wp_remote_head( $url, array( 'timeout' => 5, 'redirection' => 5 ) );
		
		if ( is_wp_error( $response ) ) {
			$result['broken'] = true;
			return $result;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		
		if ( $status_code >= 400 ) {
			$result['broken'] = true;
		}

		// Check for redirects.
		$redirect_count = isset( $response['http_response']->get_response_object()->redirect_count ) 
			? $response['http_response']->get_response_object()->redirect_count 
			: 0;

		if ( $redirect_count > 1 ) {
			$result['has_redirects'] = true;
		}

		return $result;
	}
}
