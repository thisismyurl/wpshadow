<?php
/**
 * Terms of Service Diagnostic
 *
 * Tests if terms of service are current and reviewed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1523
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Terms of Service Diagnostic Class
 *
 * Evaluates whether the site has current, accessible terms of service.
 * Checks for ToS pages, legal compliance features, and review frequency.
 *
 * @since 1.6035.1523
 */
class Diagnostic_Terms_Of_Service extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'has_current_terms_of_service';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Terms of Service';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if terms of service are current and reviewed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1523
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$stats         = array();
		$issues        = array();
		$warnings      = array();
		$score         = 0;
		$total_points  = 0;
		$earned_points = 0;

		// Search for Terms of Service pages.
		$total_points += 40;
		$tos_keywords = array( 'terms of service', 'terms and conditions', 'tos', 'terms of use' );
		$tos_pages    = array();

		foreach ( $tos_keywords as $keyword ) {
			$pages = get_posts(
				array(
					'post_type'      => 'page',
					'posts_per_page' => 5,
					'post_status'    => 'publish',
					's'              => $keyword,
				)
			);
			$tos_pages = array_merge( $tos_pages, $pages );
		}

		// Remove duplicates.
		$tos_pages = array_unique( $tos_pages, SORT_REGULAR );

		if ( ! empty( $tos_pages ) ) {
			$earned_points += 40;
			$stats['tos_page_exists'] = true;
			$stats['tos_pages_found'] = count( $tos_pages );

			// Get the most recently modified ToS page.
			$newest_tos = null;
			$newest_date = 0;

			foreach ( $tos_pages as $page ) {
				$modified = strtotime( $page->post_modified );
				if ( $modified > $newest_date ) {
					$newest_date = $modified;
					$newest_tos  = $page;
				}
			}

			if ( $newest_tos ) {
				$stats['tos_last_modified'] = $newest_tos->post_modified;
				$days_since_update = floor( ( time() - $newest_date ) / DAY_IN_SECONDS );
				$stats['days_since_update'] = $days_since_update;

				if ( $days_since_update > 365 ) {
					$warnings[] = sprintf(
						/* translators: %d: number of days since last update */
						__( 'Terms of Service have not been updated in %d days (consider reviewing annually)', 'wpshadow' ),
						$days_since_update
					);
				}
			}
		} else {
			$stats['tos_page_exists'] = false;
			$issues[] = __( 'No Terms of Service page found', 'wpshadow' );
		}

		// Check for legal compliance plugins.
		$total_points += 20;
		$legal_plugins = array(
			'wp-terms-popup/wp-terms-popup.php'             => 'WP Terms Popup',
			'terms-of-service-popup/terms-of-service.php'   => 'Terms of Service Popup',
			'wp-legal-pages/legal-pages.php'                => 'WP Legal Pages',
			'auto-terms-of-service-and-privacy-policy/auto-terms-privacy-policy.php' => 'Auto Terms & Privacy',
		);

		$active_legal_plugins = array();
		foreach ( $legal_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_legal_plugins[] = $name;
			}
		}

		if ( ! empty( $active_legal_plugins ) ) {
			$earned_points += 20;
		}

		$stats['legal_plugins'] = array(
			'found' => count( $active_legal_plugins ),
			'list'  => $active_legal_plugins,
		);

		// Check for related legal pages.
		$total_points += 15;
		$legal_keywords = array( 'disclaimer', 'refund policy', 'return policy', 'legal' );
		$legal_pages    = array();

		foreach ( $legal_keywords as $keyword ) {
			$pages = get_posts(
				array(
					'post_type'      => 'page',
					'posts_per_page' => 5,
					'post_status'    => 'publish',
					's'              => $keyword,
				)
			);
			$legal_pages = array_merge( $legal_pages, $pages );
		}

		$legal_pages = array_unique( $legal_pages, SORT_REGULAR );
		$stats['legal_pages_count'] = count( $legal_pages );

		if ( count( $legal_pages ) >= 2 ) {
			$earned_points += 15;
		} elseif ( count( $legal_pages ) === 1 ) {
			$earned_points += 10;
		}

		// Check for eCommerce (requires more legal documentation).
		$total_points += 10;
		$ecommerce_plugins = array(
			'woocommerce/woocommerce.php',
			'easy-digital-downloads/easy-digital-downloads.php',
		);

		$has_ecommerce = false;
		foreach ( $ecommerce_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_ecommerce = true;
				break;
			}
		}

		$stats['has_ecommerce'] = $has_ecommerce;

		if ( $has_ecommerce ) {
			// eCommerce sites need comprehensive legal docs.
			if ( count( $legal_pages ) >= 3 ) {
				$earned_points += 10;
			} else {
				$warnings[] = __( 'eCommerce site detected - comprehensive legal documentation recommended', 'wpshadow' );
			}
		} else {
			$earned_points += 10; // Not applicable, give full credit.
		}

		// Check for GDPR compliance (related to legal requirements).
		$total_points += 15;
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );

		if ( $privacy_page_id > 0 ) {
			$earned_points += 15;
			$stats['privacy_policy_exists'] = true;
		} else {
			$stats['privacy_policy_exists'] = false;
			$warnings[] = __( 'No privacy policy page configured (often required alongside ToS)', 'wpshadow' );
		}

		// Calculate final score.
		if ( $total_points > 0 ) {
			$score = round( ( $earned_points / $total_points ) * 100 );
		}

		$stats['score']         = $score;
		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;

		// Determine severity.
		$severity     = 'medium';
		$threat_level = 45;

		if ( $score < 40 ) {
			$severity     = 'high';
			$threat_level = 55;
		} elseif ( $score >= 40 && $score < 70 ) {
			$severity     = 'medium';
			$threat_level = 40;
		} else {
			$severity     = 'low';
			$threat_level = 25;
		}

		// Return finding if ToS is insufficient.
		if ( $score < 70 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: ToS score percentage */
					__( 'Terms of Service score: %d%%. Current, comprehensive terms of service protect both you and your users legally.', 'wpshadow' ),
					$score
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/terms-of-service',
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
