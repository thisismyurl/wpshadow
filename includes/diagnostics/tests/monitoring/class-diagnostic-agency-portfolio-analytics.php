<?php
/**
 * Advanced Data Analysis Diagnostic
 *
 * Provides advanced analytics on diagnostic findings across portfolio.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1415
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Agency Portfolio Analytics Diagnostic Class
 *
 * Analyzes patterns in diagnostic findings across the agency's
 * client portfolio to identify common issues and resource allocation.
 *
 * @since 1.6035.1415
 */
class Diagnostic_Agency_Portfolio_Analytics extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'agency-portfolio-analytics';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Agency Portfolio Analytics';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes common issues across client sites';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the agency portfolio analytics check.
	 *
	 * @since  1.6035.1415
	 * @return array|null Finding array if portfolio issues detected, null otherwise.
	 */
	public static function check() {
		$stats = array();
		$issues = array();

		// Get portfolio data if this is a network/portfolio environment.
		if ( ! is_multisite() ) {
			// Single site - return null for agency-specific diagnostic.
			return null;
		}

		// Analyze sites in portfolio.
		$blog_ids = get_sites( array(
			'fields' => 'ids',
			'number' => 100,
		) );

		$stats['total_sites'] = count( $blog_ids );

		if ( empty( $blog_ids ) ) {
			return null;
		}

		// Collect diagnostic patterns.
		$common_issues = array();
		$security_issues_count = 0;
		$performance_issues_count = 0;
		$outdated_sites = 0;
		$backup_failures = 0;

		foreach ( $blog_ids as $blog_id ) {
			// Check WordPress version.
			switch_to_blog( $blog_id );

			global $wp_version;
			$current_core_update = get_core_updates();

			if ( ! empty( $current_core_update ) ) {
				$outdated_sites++;
			}

			// Check for outdated plugins.
			$plugin_updates = get_plugin_updates();
			if ( ! empty( $plugin_updates ) ) {
				$key = 'plugins_needing_update';
				$common_issues[ $key ] = ( $common_issues[ $key ] ?? 0 ) + 1;
			}

			// Check backup status.
			$last_backup = get_option( 'wpshadow_last_backup_time' );
			if ( empty( $last_backup ) ) {
				$backup_failures++;
			} else {
				$backup_age_days = ( current_time( 'timestamp' ) - intval( $last_backup ) ) / ( 60 * 60 * 24 );
				if ( $backup_age_days > 7 ) {
					$backup_failures++;
				}
			}

			// Check SSL certificate.
			$ssl_valid = get_option( 'wpshadow_ssl_valid' );
			if ( ! $ssl_valid ) {
				$key = 'ssl_issues';
				$common_issues[ $key ] = ( $common_issues[ $key ] ?? 0 ) + 1;
				$security_issues_count++;
			}

			// Check PHP version.
			$recommended_php = version_compare( phpversion(), '8.1', '<' );
			if ( $recommended_php ) {
				$key = 'php_version_outdated';
				$common_issues[ $key ] = ( $common_issues[ $key ] ?? 0 ) + 1;
				$performance_issues_count++;
			}

			restore_current_blog();
		}

		$stats['sites_with_outdated_wp'] = $outdated_sites;
		$stats['sites_with_backup_issues'] = $backup_failures;
		$stats['security_issues_detected'] = $security_issues_count;
		$stats['performance_issues_detected'] = $performance_issues_count;
		$stats['common_issues_breakdown'] = $common_issues;

		// Generate recommendations based on portfolio patterns.
		if ( $outdated_sites > ( count( $blog_ids ) * 0.5 ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of sites */
				__( '%d+ sites have outdated WordPress - implement update policy', 'wpshadow' ),
				$outdated_sites
			);
		}

		if ( $backup_failures > ( count( $blog_ids ) * 0.3 ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of sites */
				__( '%d+ sites have backup failures - needs remediation', 'wpshadow' ),
				$backup_failures
			);
		}

		if ( $security_issues_count > count( $blog_ids ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of issues */
				__( '%d security issues across portfolio - recommend bulk audit', 'wpshadow' ),
				$security_issues_count
			);
		}

		// If issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Portfolio analytics: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/portfolio-management',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null; // Portfolio health optimal.
	}
}
