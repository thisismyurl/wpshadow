<?php
/**
 * Post Guid Consistency Validation Diagnostic
 *
 * Checks post GUIDs for consistency and detects migration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Guid Consistency Validation Class
 *
 * Tests GUID consistency.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Post_Guid_Consistency_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-guid-consistency-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Guid Consistency Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks post GUIDs for consistency and detects migration issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$guid_check = self::check_guid_consistency();
		
		if ( $guid_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $guid_check['issues'] ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-guid-consistency-validation',
				'meta'         => array(
					'mismatched_guids' => $guid_check['mismatched_guids'],
					'old_domains'      => $guid_check['old_domains'],
				),
			);
		}

		return null;
	}

	/**
	 * Check GUID consistency.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_guid_consistency() {
		global $wpdb;

		$check = array(
			'has_issues'       => false,
			'issues'           => array(),
			'mismatched_guids' => 0,
			'old_domains'      => array(),
		);

		$current_site_url = get_site_url();
		$current_domain = wp_parse_url( $current_site_url, PHP_URL_HOST );

		// Check for GUIDs not matching current domain.
		$mismatched_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE guid NOT LIKE %s
				AND post_status = 'publish'
				AND post_type IN ('post', 'page')",
				'%' . $wpdb->esc_like( $current_domain ) . '%'
			)
		);

		$check['mismatched_guids'] = $mismatched_count;

		// Get sample of old domains in GUIDs.
		$old_domain_samples = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT guid
				FROM {$wpdb->posts}
				WHERE guid NOT LIKE %s
				AND post_status = 'publish'
				AND post_type IN ('post', 'page')
				LIMIT 10",
				'%' . $wpdb->esc_like( $current_domain ) . '%'
			),
			ARRAY_A
		);

		if ( ! empty( $old_domain_samples ) ) {
			foreach ( $old_domain_samples as $sample ) {
				if ( ! empty( $sample['guid'] ) ) {
					$domain = wp_parse_url( $sample['guid'], PHP_URL_HOST );
					if ( $domain && ! in_array( $domain, $check['old_domains'], true ) ) {
						$check['old_domains'][] = $domain;
					}
				}
			}
		}

		// Detect issues.
		if ( $mismatched_count > 0 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of posts */
				__( '%d published posts have GUIDs from old domains (migration artifact)', 'wpshadow' ),
				$mismatched_count
			);
		}

		if ( ! empty( $check['old_domains'] ) ) {
			$check['issues'][] = sprintf(
				/* translators: %s: old domain names */
				__( 'Old domains detected: %s', 'wpshadow' ),
				implode( ', ', $check['old_domains'] )
			);
		}

		return $check;
	}
}
