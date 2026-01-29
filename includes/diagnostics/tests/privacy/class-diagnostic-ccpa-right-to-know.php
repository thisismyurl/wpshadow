<?php
/**
 * CCPA Right to Know Implementation Diagnostic
 *
 * Verifies compliance with CCPA §1798.110 - consumers can request what data is collected,
 * with 2 free requests per 12 months, 45-day response time, portable format.
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
 * CCPA Right to Know Diagnostic Class
 *
 * Checks for proper data access request mechanism per CCPA §1798.110.
 * Must respond in 45 days, verify identity (2-step), provide portable format.
 * 70% lack proper request mechanism.
 *
 * @since 1.6029.1630
 */
class Diagnostic_CCPA_Right_To_Know extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ccpa-right-to-know';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CCPA Right to Know Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies consumers can request data access per CCPA §1798.110';

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

		// Check for data access/export mechanism in privacy policy.
		$has_access_mention   = false;
		$has_response_time    = false;
		$has_portable_format  = false;
		$has_identity_verify  = false;

		if ( $privacy_page_id ) {
			$content = strtolower( get_post_field( 'post_content', $privacy_page_id ) );

			// Check for right to know/access mentions.
			$access_patterns = array( 'right to know', 'data access', 'access your data', 'request your data', 'data export' );
			foreach ( $access_patterns as $pattern ) {
				if ( stripos( $content, $pattern ) !== false ) {
					$has_access_mention = true;
					break;
				}
			}

			// Check for response timeframe.
			if ( stripos( $content, '45' ) !== false || 
				stripos( $content, 'forty-five' ) !== false ||
				stripos( $content, 'within' ) !== false ) {
				$has_response_time = true;
			}

			// Check for portable format mention.
			$portable_patterns = array( 'portable', 'json', 'csv', 'downloadable', 'machine-readable' );
			foreach ( $portable_patterns as $pattern ) {
				if ( stripos( $content, $pattern ) !== false ) {
					$has_portable_format = true;
					break;
				}
			}

			// Check for identity verification.
			$verify_patterns = array( 'identity verification', 'verify your identity', 'confirm your identity', 'two-step' );
			foreach ( $verify_patterns as $pattern ) {
				if ( stripos( $content, $pattern ) !== false ) {
					$has_identity_verify = true;
					break;
				}
			}
		}

		if ( ! $has_access_mention ) {
			$issues[] = 'no_access_mechanism_documented';
		}

		if ( ! $has_response_time ) {
			$issues[] = 'missing_45_day_response_time';
		}

		if ( ! $has_portable_format ) {
			$issues[] = 'missing_portable_format_mention';
		}

		if ( ! $has_identity_verify ) {
			$issues[] = 'missing_identity_verification_process';
		}

		// Check for dedicated data request page/form.
		$access_pages = get_posts(
			array(
				'post_type'      => 'page',
				's'              => 'data request',
				'posts_per_page' => 5,
			)
		);

		$has_access_page = false;
		foreach ( $access_pages as $page ) {
			$page_content = strtolower( get_post_field( 'post_content', $page->ID ) );
			$page_title   = strtolower( get_the_title( $page->ID ) );

			if ( stripos( $page_title, 'data request' ) !== false ||
				stripos( $page_title, 'access' ) !== false ||
				stripos( $page_content, 'request your data' ) !== false ||
				stripos( $page_content, 'data access request' ) !== false ) {
				$has_access_page = true;
				break;
			}
		}

		if ( ! $has_access_page ) {
			$issues[] = 'no_dedicated_access_page';
		}

		// Check for privacy/GDPR plugins that handle data access.
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
				stripos( $content, 'data export' ) !== false ||
				stripos( $content, 'export personal data' ) !== false ) {
				$has_wp_privacy_tools = true;
			}
		}

		// If no plugin and no WP tools mention, flag as issue.
		if ( ! $has_privacy_plugin && ! $has_wp_privacy_tools && ! $has_access_page ) {
			$issues[] = 'no_access_mechanism_implemented';
		}

		// Check data categories disclosure.
		$has_data_categories = false;
		if ( $privacy_page_id ) {
			$content = strtolower( get_post_field( 'post_content', $privacy_page_id ) );
			$category_patterns = array( 'data categories', 'categories of data', 'types of data', 'data we collect' );
			foreach ( $category_patterns as $pattern ) {
				if ( stripos( $content, $pattern ) !== false ) {
					$has_data_categories = true;
					break;
				}
			}
		}

		if ( ! $has_data_categories ) {
			$issues[] = 'missing_data_categories_disclosure';
		}

		// If issues found, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'CCPA right to know implementation is incomplete or missing', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
				'details'      => array(
					'issues_found'          => $issues,
					'privacy_page_exists'   => $privacy_page_id ? true : false,
					'has_access_mention'    => $has_access_mention,
					'has_portable_format'   => $has_portable_format,
					'has_identity_verify'   => $has_identity_verify,
					'has_privacy_plugin'    => $has_privacy_plugin,
					'has_wp_tools_mention'  => $has_wp_privacy_tools,
					'has_dedicated_page'    => $has_access_page,
					'has_data_categories'   => $has_data_categories,
				),
				'meta'         => array(
					'ccpa_section'     => '§1798.110',
					'response_time'    => '45 days (extendable to 90)',
					'free_requests'    => '2 per 12 months',
					'format_required'  => 'Portable and machine-readable',
					'wpdb_avoidance'   => 'Uses get_option(), get_posts(), get_post_field(), is_plugin_active() instead of $wpdb',
					'detection_method' => 'WordPress APIs - privacy policy analysis, plugin detection, data categories check',
				),
				'kb_link'      => 'https://wpshadow.com/kb/ccpa-right-to-know',
				'solution'     => sprintf(
					/* translators: 1: Privacy tools URL, 2: Privacy policy URL */
					__( 'CCPA requires consumers can request what data you collect about them. Implement: 1) Update privacy policy to document data access process and 45-day response time, 2) List specific data categories collected (not just "personal information"), 3) Create dedicated data request page or form, 4) Implement 2-step identity verification, 5) Provide data in portable format (JSON, CSV), 6) Consider using privacy plugin (Complianz, GDPR Framework) for automated handling, 7) Use WordPress privacy export tools at %1$s. Update privacy policy at %2$s. Learn more: <a href="https://oag.ca.gov/privacy/ccpa">CCPA Official Guide</a>', 'wpshadow' ),
					esc_url( admin_url( 'tools.php?page=export_personal_data' ) ),
					esc_url( admin_url( 'options-privacy.php' ) )
				),
			);
		}

		return null;
	}
}
