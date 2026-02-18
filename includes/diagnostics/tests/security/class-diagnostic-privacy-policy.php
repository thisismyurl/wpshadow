<?php
/**
 * Privacy Policy Diagnostic
 *
 * Tests if privacy policy exists and is current.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1522
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Diagnostic Class
 *
 * Evaluates whether the site has a current, accessible privacy policy.
 * Checks for GDPR compliance features and privacy management tools.
 *
 * @since 1.6035.1522
 */
class Diagnostic_Privacy_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'maintains_privacy_policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Privacy Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if privacy policy exists and is current';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1522
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$stats         = array();
		$issues        = array();
		$warnings      = array();
		$score         = 0;
		$total_points  = 0;
		$earned_points = 0;

		// Check for WordPress privacy policy page.
		$total_points += 30;
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );

		if ( $privacy_page_id > 0 ) {
			$privacy_page = get_post( $privacy_page_id );

			if ( $privacy_page && 'publish' === $privacy_page->post_status ) {
				$earned_points += 30;
				$stats['privacy_page_exists'] = true;
				$stats['privacy_page_id'] = $privacy_page_id;

				// Check when it was last modified.
				$last_modified = strtotime( $privacy_page->post_modified );
				$stats['privacy_page_modified'] = $privacy_page->post_modified;

				$days_since_update = floor( ( time() - $last_modified ) / DAY_IN_SECONDS );
				$stats['days_since_update'] = $days_since_update;

				if ( $days_since_update > 365 ) {
					$warnings[] = sprintf(
						/* translators: %d: number of days since last update */
						__( 'Privacy policy has not been updated in %d days (consider reviewing annually)', 'wpshadow' ),
						$days_since_update
					);
				}
			} else {
				$stats['privacy_page_exists'] = false;
				$issues[] = __( 'Privacy policy page is set but not published', 'wpshadow' );
			}
		} else {
			$stats['privacy_page_exists'] = false;
			$issues[] = __( 'No privacy policy page configured', 'wpshadow' );
		}

		// Check for GDPR compliance plugins.
		$total_points += 20;
		$gdpr_plugins = array(
			'gdpr-cookie-compliance/gdpr-cookie-compliance.php' => 'GDPR Cookie Compliance',
			'cookie-notice/cookie-notice.php'                   => 'Cookie Notice',
			'wp-gdpr-compliance/wp-gdpr-compliance.php'         => 'WP GDPR Compliance',
			'gdpr-cookie-consent/gdpr-cookie-consent.php'       => 'GDPR Cookie Consent',
			'complianz-gdpr/complianz-gpdr.php'                 => 'Complianz GDPR',
			'cookiebot/cookiebot.php'                           => 'Cookiebot',
		);

		$active_gdpr_plugins = array();
		foreach ( $gdpr_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_gdpr_plugins[] = $name;
			}
		}

		if ( ! empty( $active_gdpr_plugins ) ) {
			$earned_points += 20;
		}

		$stats['gdpr_plugins'] = array(
			'found' => count( $active_gdpr_plugins ),
			'list'  => $active_gdpr_plugins,
		);

		if ( empty( $active_gdpr_plugins ) ) {
			$warnings[] = __( 'No GDPR compliance plugin detected', 'wpshadow' );
		}

		// Check for cookie consent mechanism.
		$total_points += 15;
		$cookie_plugins = array(
			'cookie-notice/cookie-notice.php'           => 'Cookie Notice',
			'gdpr-cookie-compliance/gdpr-cookie-compliance.php' => 'GDPR Cookie Compliance',
			'cookiebot/cookiebot.php'                   => 'Cookiebot',
			'cookie-law-info/cookie-law-info.php'       => 'Cookie Law Info',
		);

		$active_cookie_plugins = array();
		foreach ( $cookie_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_cookie_plugins[] = $name;
			}
		}

		if ( ! empty( $active_cookie_plugins ) ) {
			$earned_points += 15;
		}

		$stats['cookie_consent'] = array(
			'found' => count( $active_cookie_plugins ),
			'list'  => $active_cookie_plugins,
		);

		if ( empty( $active_cookie_plugins ) ) {
			$warnings[] = __( 'No cookie consent mechanism detected', 'wpshadow' );
		}

		// Check for data export/erasure tools (WordPress core feature).
		$total_points += 15;
		// WordPress 4.9.6+ includes privacy tools.
		global $wp_version;
		if ( version_compare( $wp_version, '4.9.6', '>=' ) ) {
			$earned_points += 15;
			$stats['privacy_tools_available'] = true;
		} else {
			$stats['privacy_tools_available'] = false;
		}

		// Check for privacy-related pages (terms, cookies, etc.).
		$total_points += 10;
		$privacy_keywords = array( 'terms', 'cookie', 'data protection', 'privacy' );
		$privacy_pages = array();

		foreach ( $privacy_keywords as $keyword ) {
			$pages = get_posts(
				array(
					'post_type'      => 'page',
					'posts_per_page' => 5,
					'post_status'    => 'publish',
					's'              => $keyword,
				)
			);
			$privacy_pages = array_merge( $privacy_pages, $pages );
		}

		// Remove duplicates.
		$privacy_pages = array_unique( $privacy_pages, SORT_REGULAR );
		$stats['privacy_related_pages'] = count( $privacy_pages );

		if ( count( $privacy_pages ) >= 2 ) {
			$earned_points += 10;
		} elseif ( count( $privacy_pages ) === 1 ) {
			$earned_points += 5;
		}

		// Check for SSL/HTTPS (important for privacy).
		$total_points += 10;
		if ( is_ssl() ) {
			$earned_points += 10;
			$stats['ssl_enabled'] = true;
		} else {
			$stats['ssl_enabled'] = false;
			$issues[] = __( 'Site is not using HTTPS (critical for privacy and data protection)', 'wpshadow' );
		}

		// Calculate final score.
		if ( $total_points > 0 ) {
			$score = round( ( $earned_points / $total_points ) * 100 );
		}

		$stats['score']         = $score;
		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;

		// Determine severity.
		$severity     = 'high';
		$threat_level = 60;

		if ( $score < 40 ) {
			$severity     = 'high';
			$threat_level = 65;
		} elseif ( $score >= 40 && $score < 70 ) {
			$severity     = 'medium';
			$threat_level = 45;
		} else {
			$severity     = 'low';
			$threat_level = 25;
		}

		// Return finding if privacy policy is insufficient.
		if ( $score < 70 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: privacy score percentage */
					__( 'Privacy policy score: %d%%. A current, accessible privacy policy is required by law in many jurisdictions (GDPR, CCPA, etc.).', 'wpshadow' ),
					$score
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/privacy-policy',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
