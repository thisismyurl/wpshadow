<?php
/**
 * Regional Compliance Diagnostic
 *
 * Tests whether the site maintains compliance with regulations in all target
 * international markets. Regional compliance includes GDPR (EU), CCPA (California),
 * PIPEDA (Canada), and other data protection and consumer protection laws.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Maintains_Regional_Compliance Class
 *
 * Diagnostic #29: Regional Compliance from Specialized & Emerging Success Habits.
 * Checks if the website maintains compliance with regulations across all target
 * international markets.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Maintains_Regional_Compliance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'maintains-regional-compliance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Regional Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site maintains compliance with regulations in all target international markets';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'international-ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * Regional compliance is legally required for international operations.
	 * This diagnostic checks for privacy policies, cookie consent, compliance
	 * plugins, regional legal pages, and compliance documentation.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 6;
		$score_details  = array();
		$recommendations = array();

		// Check 1: GDPR compliance plugins.
		$gdpr_plugins = array(
			'cookie-law-info/cookie-law-info.php',
			'gdpr-cookie-consent/gdpr-cookie-consent.php',
			'complianz-gdpr/complianz-gpdr.php',
			'cookie-notice/cookie-notice.php',
			'wp-gdpr-compliance/wp-gdpr-compliance.php',
		);

		$has_gdpr_plugin = false;
		foreach ( $gdpr_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_gdpr_plugin = true;
				break;
			}
		}

		if ( $has_gdpr_plugin ) {
			++$score;
			$score_details[] = __( '✓ GDPR/cookie consent plugin active', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No GDPR compliance plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install a GDPR compliance plugin (Complianz, Cookie Notice) for EU market compliance', 'wpshadow' );
		}

		// Check 2: Privacy policy page.
		$privacy_page = get_privacy_policy_url();

		if ( ! empty( $privacy_page ) ) {
			++$score;
			$score_details[] = __( '✓ Privacy policy page configured', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No privacy policy found', 'wpshadow' );
			$recommendations[] = __( 'Create and configure a privacy policy page in Settings > Privacy', 'wpshadow' );
		}

		// Check 3: Terms and conditions page.
		$terms_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'terms',
			)
		);

		if ( ! empty( $terms_pages ) ) {
			++$score;
			$score_details[] = __( '✓ Terms and conditions page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No terms and conditions found', 'wpshadow' );
			$recommendations[] = __( 'Create terms and conditions page for legal protection and compliance', 'wpshadow' );
		}

		// Check 4: Regional compliance pages (GDPR, CCPA, etc.).
		$all_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
			)
		);

		$compliance_keywords = array( 'gdpr', 'ccpa', 'pipeda', 'data protection', 'cookie policy', 'accessibility statement' );
		$compliance_pages = 0;

		foreach ( $all_pages as $page ) {
			$title_lower = strtolower( $page->post_title );
			foreach ( $compliance_keywords as $keyword ) {
				if ( stripos( $title_lower, $keyword ) !== false ) {
					++$compliance_pages;
					break;
				}
			}
		}

		if ( $compliance_pages >= 3 ) {
			++$score;
			$score_details[] = sprintf(
				/* translators: %d: number of compliance pages */
				__( '✓ Multiple compliance pages found (%d+ pages)', 'wpshadow' ),
				$compliance_pages
			);
		} elseif ( $compliance_pages > 0 ) {
			$score_details[]   = sprintf(
				/* translators: %d: number of compliance pages */
				__( '◐ Some compliance pages (%d pages)', 'wpshadow' ),
				$compliance_pages
			);
			$recommendations[] = __( 'Add region-specific compliance pages (GDPR for EU, CCPA for California, etc.)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No regional compliance documentation found', 'wpshadow' );
			$recommendations[] = __( 'Create compliance pages for each target market (GDPR, CCPA, PIPEDA)', 'wpshadow' );
		}

		// Check 5: Cookie consent implementation.
		$recent_posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
			)
		);

		$has_cookie_notice = false;
		foreach ( $recent_posts as $post ) {
			if ( stripos( $post->post_content, 'cookie' ) !== false &&
				 ( stripos( $post->post_content, 'consent' ) !== false ||
				   stripos( $post->post_content, 'accept' ) !== false ) ) {
				$has_cookie_notice = true;
				break;
			}
		}

		if ( $has_cookie_notice || $has_gdpr_plugin ) {
			++$score;
			$score_details[] = __( '✓ Cookie consent mechanism implemented', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No cookie consent banner detected', 'wpshadow' );
			$recommendations[] = __( 'Implement cookie consent banner for GDPR and other regional compliance', 'wpshadow' );
		}

		// Check 6: Data export/deletion capabilities (GDPR requirement).
		$has_data_tools = function_exists( 'wp_privacy_generate_personal_data_export_file' );

		if ( $has_data_tools ) {
			++$score;
			$score_details[] = __( '✓ WordPress privacy tools available (export/delete user data)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Privacy tools not configured', 'wpshadow' );
			$recommendations[] = __( 'Ensure WordPress 4.9.6+ is installed for built-in privacy tools', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 35 ) {
			$severity     = 'medium';
			$threat_level = 30;
		} elseif ( $score_percentage < 65 ) {
			$severity     = 'low';
			$threat_level = 20;
		} else {
			// Regional compliance is adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Regional compliance score: %d%%. Non-compliance can result in fines up to €20M (GDPR) or $7,500 per violation (CCPA). 73%% of consumers say data privacy affects their purchase decisions. Compliance is both legally required and builds customer trust.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/regional-compliance',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Regional compliance reduces legal risk, builds customer trust, and is mandatory for operating in international markets like the EU, California, and Canada.', 'wpshadow' ),
		);
	}
}
