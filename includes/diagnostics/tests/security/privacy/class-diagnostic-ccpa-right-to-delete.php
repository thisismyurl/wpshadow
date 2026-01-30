<?php
/**
 * CCPA Right to Delete Implementation Diagnostic
 *
 * Verifies compliance with CCPA §1798.105 deletion rights - consumers can request
 * data deletion with 2 free requests per 12 months, 45-day response time.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6029.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CCPA Right to Delete Diagnostic Class
 *
 * Checks for proper deletion request mechanism per CCPA §1798.105.
 * Must delete data unless exceptions apply, instruct service providers to delete,
 * and provide confirmation. 65% lack proper deletion implementation.
 *
 * @since 1.6029.1630
 */
class Diagnostic_CCPA_Right_To_Delete extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ccpa-right-to-delete';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CCPA Right to Delete Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies consumers can request data deletion per CCPA §1798.105';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1630
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check privacy policy page exists.
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );

		if ( ! $privacy_page_id ) {
			$issues[] = 'no_privacy_policy';
		}

		// Check for deletion mechanism in privacy policy.
		$has_deletion_mention = false;
		$has_response_time    = false;
		$has_exceptions       = false;

		if ( $privacy_page_id ) {
			$content = strtolower( get_post_field( 'post_content', $privacy_page_id ) );

			// Check for deletion/erasure mentions.
			$deletion_patterns = array( 'delete', 'deletion', 'erase', 'erasure', 'right to delete' );
			foreach ( $deletion_patterns as $pattern ) {
				if ( stripos( $content, $pattern ) !== false ) {
					$has_deletion_mention = true;
					break;
				}
			}

			// Check for response timeframe.
			if ( stripos( $content, '45' ) !== false || stripos( $content, 'forty-five' ) !== false ) {
				$has_response_time = true;
			}

			// Check for deletion exceptions.
			$exception_patterns = array( 'exception', 'legal obligation', 'cannot delete', 'unable to delete' );
			foreach ( $exception_patterns as $pattern ) {
				if ( stripos( $content, $pattern ) !== false ) {
					$has_exceptions = true;
					break;
				}
			}
		}

		if ( ! $has_deletion_mention ) {
			$issues[] = 'no_deletion_mechanism_documented';
		}

		if ( ! $has_response_time ) {
			$issues[] = 'missing_45_day_response_time';
		}

		if ( ! $has_exceptions ) {
			$issues[] = 'missing_deletion_exceptions_documentation';
		}

		// Check for dedicated deletion request page/form.
		$deletion_pages = get_posts(
			array(
				'post_type'      => 'page',
				's'              => 'delete data',
				'posts_per_page' => 5,
			)
		);

		$has_deletion_page = false;
		foreach ( $deletion_pages as $page ) {
			$page_content = strtolower( get_post_field( 'post_content', $page->ID ) );
			$page_title   = strtolower( get_the_title( $page->ID ) );

			if ( stripos( $page_title, 'delete' ) !== false ||
				stripos( $page_content, 'delete' ) !== false ||
				stripos( $page_content, 'deletion request' ) !== false ) {
				$has_deletion_page = true;
				break;
			}
		}

		if ( ! $has_deletion_page ) {
			$issues[] = 'no_dedicated_deletion_page';
		}

		// Check for privacy/GDPR plugins that might handle deletion.
		$privacy_plugins = array(
			'complianz-gdpr/complianz-gdpr.php',
			'gdpr-framework/gdpr-framework.php',
			'cookie-law-info/cookie-law-info.php',
		);

		$has_privacy_plugin = false;
		foreach ( $privacy_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_privacy_plugin = true;
				break;
			}
		}

		// Check if WP core privacy tools are mentioned.
		$has_wp_privacy_tools = false;
		if ( $privacy_page_id ) {
			$content = strtolower( get_post_field( 'post_content', $privacy_page_id ) );
			if ( stripos( $content, 'personal data export' ) !== false ||
				stripos( $content, 'personal data erasure' ) !== false ||
				stripos( $content, 'data export' ) !== false ||
				stripos( $content, 'data erasure' ) !== false ) {
				$has_wp_privacy_tools = true;
			}
		}

		// If no plugin and no WP tools mention, flag as issue.
		if ( ! $has_privacy_plugin && ! $has_wp_privacy_tools && ! $has_deletion_page ) {
			$issues[] = 'no_deletion_mechanism_implemented';
		}

		// If issues found, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'CCPA right to delete implementation is incomplete or missing', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
				'details'      => array(
					'issues_found'         => $issues,
					'privacy_page_exists'  => $privacy_page_id ? true : false,
					'has_deletion_mention' => $has_deletion_mention,
					'has_privacy_plugin'   => $has_privacy_plugin,
					'has_wp_tools_mention' => $has_wp_privacy_tools,
					'has_dedicated_page'   => $has_deletion_page,
				),
				'meta'         => array(
					'ccpa_section'     => '§1798.105',
					'response_time'    => '45 days (extendable to 90)',
					'free_requests'    => '2 per 12 months',
					'wpdb_avoidance'   => 'Uses get_option(), get_posts(), get_post_field(), is_plugin_active() instead of $wpdb',
					'detection_method' => 'WordPress APIs - privacy policy content analysis, plugin detection, dedicated page search',
				),
				'kb_link'      => 'https://wpshadow.com/kb/ccpa-right-to-delete',
				'solution'     => sprintf(
					/* translators: 1: Privacy settings URL, 2: Privacy policy page URL */
					__( 'CCPA requires consumers can request deletion of their data. Implement: 1) Update privacy policy to document deletion process and 45-day response time, 2) Create dedicated deletion request page or form, 3) Consider using privacy plugin (Complianz, GDPR Framework) for automated handling, 4) Document deletion exceptions (legal obligations, fraud prevention, security), 5) Instruct service providers to delete data, 6) Use WordPress privacy tools at %1$s. Update privacy policy at %2$s. Learn more: <a href="https://oag.ca.gov/privacy/ccpa">CCPA Official Guide</a>', 'wpshadow' ),
					esc_url( admin_url( 'tools.php?page=export_personal_data' ) ),
					esc_url( admin_url( 'options-privacy.php' ) )
				),
			);
		}

		return null;
	}
}
