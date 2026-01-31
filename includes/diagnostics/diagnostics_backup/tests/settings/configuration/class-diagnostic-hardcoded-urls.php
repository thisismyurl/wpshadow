<?php
/**
 * Diagnostic: Hardcoded URLs Detection
 *
 * Scans database for hardcoded URLs (http:// or domain-specific).
 * Hardcoded URLs cause issues when migrating or switching domains.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Hardcoded_Urls
 *
 * Detects hardcoded URLs in database content.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Hardcoded_Urls extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'hardcoded-urls';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Hardcoded URLs Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Scans database for hardcoded URLs';

	/**
	 * Check for hardcoded URLs.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$site_url     = get_site_url();
		$parsed_url   = wp_parse_url( $site_url );
		$domain       = $parsed_url['host'] ?? '';
		$scheme       = $parsed_url['scheme'] ?? 'https';

		if ( empty( $domain ) ) {
			return null;
		}

		// Search for hardcoded URLs in post content and meta.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$hardcoded_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_content LIKE %s
				OR post_excerpt LIKE %s",
				'%' . $wpdb->esc_like( 'http://' . $domain ) . '%',
				'%' . $wpdb->esc_like( 'http://' . $domain ) . '%'
			)
		);

		// Search for hardcoded URLs in post meta.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$hardcoded_meta = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta}
				WHERE meta_value LIKE %s",
				'%' . $wpdb->esc_like( 'http://' . $domain ) . '%'
			)
		);

		// Search for hardcoded URLs in options.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$hardcoded_options = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->options}
				WHERE option_value LIKE %s",
				'%' . $wpdb->esc_like( 'http://' . $domain ) . '%'
			)
		);

		$total_hardcoded = (int) $hardcoded_posts + (int) $hardcoded_meta + (int) $hardcoded_options;

		// Report if HTTP hardcoded URLs found (should be HTTPS if site is HTTPS).
		if ( 'https' === $scheme && $total_hardcoded > 10 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Number of hardcoded URLs found */
					_n(
						'%d hardcoded HTTP URL found in database. This can cause mixed content warnings.',
						'%d hardcoded HTTP URLs found in database. This can cause mixed content warnings.',
						$total_hardcoded,
						'wpshadow'
					),
					$total_hardcoded
				),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/hardcoded_urls',
				'meta'        => array(
					'total_hardcoded'    => $total_hardcoded,
					'hardcoded_posts'    => (int) $hardcoded_posts,
					'hardcoded_meta'     => (int) $hardcoded_meta,
					'hardcoded_options'  => (int) $hardcoded_options,
					'domain'             => $domain,
				),
			);
		}

		// Informational: Few hardcoded URLs.
		if ( $total_hardcoded > 0 && $total_hardcoded <= 10 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Number of hardcoded URLs */
					__( '%d hardcoded URLs found. Consider using relative URLs for easier site migration.', 'wpshadow' ),
					$total_hardcoded
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/hardcoded_urls',
				'meta'        => array(
					'total_hardcoded' => $total_hardcoded,
				),
			);
		}

		// No hardcoded URLs detected.
		return null;
	}
}
