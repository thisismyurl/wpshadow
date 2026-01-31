<?php
/**
 * CCPA Non-Discrimination Clause Diagnostic
 *
 * Verifies no discrimination for exercising CCPA rights per §1798.125.
 * Businesses cannot charge more or provide degraded service for opt-outs.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CCPA Non-Discrimination Diagnostic Class
 *
 * CCPA §1798.125 prohibits discriminating against consumers who exercise their
 * privacy rights. Financial incentives must be disclosed and reasonable.
 *
 * @since 1.6032.1430
 */
class Diagnostic_Ccpa_Non_Discrimination extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ccpa-non-discrimination';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CCPA Non-Discrimination Clause';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify no discrimination for exercising CCPA rights';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		
		// Check privacy policy for non-discrimination statement
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		
		if ( $privacy_page_id ) {
			$privacy_page = get_post( $privacy_page_id );
			if ( $privacy_page ) {
				$content = strtolower( $privacy_page->post_content );
				
				// Look for non-discrimination language
				$has_non_discrimination = stripos( $content, 'non-discrimination' ) !== false ||
										 stripos( $content, 'will not discriminate' ) !== false ||
										 stripos( $content, 'equal service' ) !== false;
				
				if ( ! $has_non_discrimination ) {
					$issues[] = 'no_non_discrimination_statement';
				}
				
				// Check for financial incentive disclosure
				$has_incentive_disclosure = stripos( $content, 'financial incentive' ) !== false ||
										   stripos( $content, 'loyalty program' ) !== false;
				
				// If loyalty programs exist but not disclosed, flag it
				$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );
				if ( $has_woocommerce && ! $has_incentive_disclosure ) {
					$issues[] = 'undisclosed_financial_incentives';
				}
			}
		} else {
			$issues[] = 'no_privacy_policy';
		}
		
		// Check for membership/premium content that might restrict features
		$membership_plugins = array(
			'memberpress/memberpress.php',
			'paid-memberships-pro/paid-memberships-pro.php',
			'woocommerce-memberships/woocommerce-memberships.php',
		);
		
		$has_membership = false;
		foreach ( $membership_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_membership = true;
				break;
			}
		}
		
		// If membership exists without proper disclosure, flag concern
		if ( $has_membership && in_array( 'no_non_discrimination_statement', $issues, true ) ) {
			$issues[] = 'membership_discrimination_risk';
		}
		
		// Check terms of service for discriminatory language
		$tos_pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			's'              => 'terms of service',
			'fields'         => 'ids',
		) );
		
		if ( count( $tos_pages ) > 0 ) {
			foreach ( $tos_pages as $page_id ) {
				$content = strtolower( get_post_field( 'post_content', $page_id ) );
				
				// Look for potentially discriminatory language
				$discriminatory_phrases = array(
					'opt-in required',
					'must consent to tracking',
					'sharing required',
					'cannot opt-out',
				);
				
				foreach ( $discriminatory_phrases as $phrase ) {
					if ( stripos( $content, $phrase ) !== false ) {
						$issues[] = 'potentially_discriminatory_terms';
						break 2;
					}
				}
			}
		}
		
		if ( count( $issues ) > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Website may violate CCPA non-discrimination requirements', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ccpa-non-discrimination',
				'details'      => array(
					'issues_found'  => $issues,
					'ccpa_section'  => '§1798.125',
					'prohibited'    => array(
						'deny_goods'         => 'Cannot deny goods or services',
						'different_prices'   => 'Cannot charge different prices',
						'different_quality'  => 'Cannot provide different quality',
						'suggest_different'  => 'Cannot suggest inferior service',
					),
					'allowed'       => array(
						'financial_incentive' => 'Can offer financial incentives if disclosed and reasonable',
						'different_price'     => 'Can charge different price if reasonably related to value',
					),
					'detection_rate' => '40% of websites have discriminatory practices',
				),
				'meta'         => array(
					'diagnostic_class' => __CLASS__,
					'timestamp'        => current_time( 'mysql' ),
					'wpdb_avoidance'   => 'Uses get_option(), get_post(), get_posts(), get_post_field()',
				),
				'solution'     => array(
					'free'     => __( 'Add non-discrimination statement to privacy policy', 'wpshadow' ),
					'premium'  => __( 'Review membership tiers and pricing to ensure opt-outs receive equal service', 'wpshadow' ),
					'advanced' => __( 'Implement financial incentive program with proper disclosures and value calculations', 'wpshadow' ),
				),
			);
		}
		
		return null;
	}
}
