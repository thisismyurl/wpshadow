<?php
/**
 * Category Metadata and Configuration
 *
 * Defines all health gauge categories with colors, labels, and display properties.
 * Used throughout dashboard for gauge rendering and category organization.
 *
 * @package WPShadow
 * @subpackage Core
 */

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get category metadata for all health gauges
 *
 * Returns configuration for all 10 health categories:
 * - 8 standard WPShadow categories
 * - 1 WordPress native Site Health
 * - 1 Overall site health (calculated from all)
 *
 * Issue #563: Each category has a distinct, attractive color
 *
 * @return array<string, array{label: string, icon: string, color: string, background: string, description: string}>
 */
function wpshadow_get_category_metadata(): array {
	return array(
		// Standard WPShadow Categories (8)
		'security'         => array(
			'label'       => __( 'Security', 'wpshadow' ),
			'icon'        => 'dashicons-shield',
			'color'       => '#dc2626', // Red
			'background'  => 'rgba(220, 38, 38, 0.1)',
			'description' => __( 'Site security, vulnerabilities, and protection measures', 'wpshadow' ),
		),
		'performance'      => array(
			'label'       => __( 'Performance', 'wpshadow' ),
			'icon'        => 'dashicons-performance',
			'color'       => '#0891b2', // Cyan
			'background'  => 'rgba(8, 145, 178, 0.1)',
			'description' => __( 'Site speed, caching, and optimization', 'wpshadow' ),
		),
		'code-quality'     => array(
			'label'       => __( 'Code Quality', 'wpshadow' ),
			'icon'        => 'dashicons-editor-code',
			'color'       => '#7c3aed', // Purple
			'background'  => 'rgba(124, 58, 237, 0.1)',
			'description' => __( 'Code standards, best practices, and technical debt', 'wpshadow' ),
		),
		'seo'              => array(
			'label'       => __( 'SEO', 'wpshadow' ),
			'icon'        => 'dashicons-search',
			'color'       => '#2563eb', // Blue
			'background'  => 'rgba(37, 99, 235, 0.1)',
			'description' => __( 'Search engine optimization and discoverability', 'wpshadow' ),
		),
		'design'           => array(
			'label'       => __( 'Design', 'wpshadow' ),
			'icon'        => 'dashicons-admin-appearance',
			'color'       => '#8e44ad', // Purple-Pink
			'background'  => 'rgba(142, 68, 173, 0.1)',
			'description' => __( 'Visual design, UX, and accessibility', 'wpshadow' ),
		),
		'settings'         => array(
			'label'       => __( 'Settings', 'wpshadow' ),
			'icon'        => 'dashicons-admin-settings',
			'color'       => '#4b5563', // Gray
			'background'  => 'rgba(75, 85, 99, 0.1)',
			'description' => __( 'WordPress configuration and settings', 'wpshadow' ),
		),
		'monitoring'       => array(
			'label'       => __( 'Monitoring', 'wpshadow' ),
			'icon'        => 'dashicons-visibility',
			'color'       => '#059669', // Green
			'background'  => 'rgba(5, 150, 105, 0.1)',
			'description' => __( 'Site monitoring, uptime, and alerts', 'wpshadow' ),
		),
		'workflows'        => array(
			'label'       => __( 'Workflows', 'wpshadow' ),
			'icon'        => 'dashicons-update',
			'color'       => '#ea580c', // Orange
			'background'  => 'rgba(234, 88, 12, 0.1)',
			'description' => __( 'Automation, scheduled tasks, and workflows', 'wpshadow' ),
		),

		// WordPress Native Site Health (9th gauge)
		'wordpress-health' => array(
			'label'       => __( 'WordPress Health', 'wpshadow' ),
			'icon'        => 'dashicons-wordpress',
			'color'       => '#21759b', // WordPress Blue
			'background'  => 'rgba(33, 117, 155, 0.1)',
			'description' => __( 'WordPress native site health checks', 'wpshadow' ),
		),

		// Overall Site Health (10th gauge - calculated)
		'overall'          => array(
			'label'       => __( 'Overall Health', 'wpshadow' ),
			'icon'        => 'dashicons-heart',
			'color'       => '#1976d2', // Deep Blue
			'background'  => 'rgba(25, 118, 210, 0.1)',
			'description' => __( 'Combined health score across all categories', 'wpshadow' ),
		),
	);
}

/**
 * Calculate overall health from all category gauges
 *
 * @param array $findings_by_category Findings grouped by category.
 * @param array $category_meta Category metadata array.
 * @return array Health status with score, status, color, message.
 */
function wpshadow_calculate_overall_health( array $findings_by_category, array $category_meta ): array {
	$total_findings = 0;
	$critical_count = 0;
	$high_count     = 0;
	$medium_count   = 0;

	// Exclude 'overall' and 'wordpress-health' from calculation to avoid circular reference
	foreach ( $findings_by_category as $cat_key => $findings ) {
		if ( in_array( $cat_key, array( 'overall', 'wordpress-health' ), true ) ) {
			continue;
		}

		foreach ( $findings as $finding ) {
			++$total_findings;
			$threat = isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;

			if ( $threat >= 75 ) {
				++$critical_count;
			} elseif ( $threat >= 50 ) {
				++$high_count;
			} else {
				++$medium_count;
			}
		}
	}

	if ( $total_findings === 0 ) {
		return array(
			'score'   => 100,
			'status'  => __( 'Excellent', 'wpshadow' ),
			'color'   => '#2e7d32',
			'message' => __( 'No issues detected. Your site is in excellent health!', 'wpshadow' ),
		);
	}

	// Weighted scoring: Critical = 30pts, High = 15pts, Medium = 5pts
	$weighted_score = 100 - ( ( $critical_count * 30 + $high_count * 15 + $medium_count * 5 ) / $total_findings );
	$weighted_score = max( 0, min( 100, $weighted_score ) );

	if ( $weighted_score >= 80 ) {
		return array(
			'score'   => (int) $weighted_score,
			'status'  => __( 'Good', 'wpshadow' ),
			'color'   => '#2e7d32',
			'message' => __( 'Your site is healthy with only minor issues to address.', 'wpshadow' ),
		);
	} elseif ( $weighted_score >= 60 ) {
		return array(
			'score'   => (int) $weighted_score,
			'status'  => __( 'Fair', 'wpshadow' ),
			'color'   => '#f57c00',
			'message' => __( 'Your site needs some attention. Review the issues below.', 'wpshadow' ),
		);
	} else {
		return array(
			'score'   => (int) $weighted_score,
			'status'  => __( 'Poor', 'wpshadow' ),
			'color'   => '#c62828',
			'message' => __( 'Your site needs immediate attention. Address critical issues now.', 'wpshadow' ),
		);
	}
}

/**
 * Get WordPress native Site Health status
 *
 * Fetches WordPress core Site Health data (excluding WPShadow's own checks)
 *
 * @return array Status with score, status, color, message, findings_count
 */
function wpshadow_get_wordpress_health_status(): array {
	// Ensure Site Health is available
	if ( ! class_exists( 'WP_Site_Health' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-site-health.php';
	}

	$site_health = \WP_Site_Health::get_instance();
	$tests       = \WP_Site_Health::get_tests();

	// Run tests
	$test_results = array();
	$passed       = 0;
	$recommended  = 0;
	$critical     = 0;

	// Direct tests
	if ( isset( $tests['direct'] ) ) {
		foreach ( $tests['direct'] as $test ) {
			if ( ! isset( $test['test'] ) ) {
				continue;
			}

			// Skip WPShadow's own tests
			if ( strpos( $test['test'], 'wpshadow' ) !== false ) {
				continue;
			}

			$result = call_user_func( $test['test'] );

			if ( isset( $result['status'] ) ) {
				if ( $result['status'] === 'good' ) {
					++$passed;
				} elseif ( $result['status'] === 'recommended' ) {
					++$recommended;
				} else {
					++$critical;
				}
			}
		}
	}

	$total = $passed + $recommended + $critical;

	if ( $total === 0 ) {
		return array(
			'score'          => 100,
			'status'         => __( 'Excellent', 'wpshadow' ),
			'color'          => '#2e7d32',
			'message'        => __( 'All WordPress health checks passed!', 'wpshadow' ),
			'findings_count' => 0,
		);
	}

	$score = (int) ( ( $passed / $total ) * 100 );

	if ( $critical > 0 ) {
		$color  = '#c62828';
		$status = __( 'Critical', 'wpshadow' );
	} elseif ( $recommended > 0 ) {
		$color  = '#f57c00';
		$status = __( 'Recommended', 'wpshadow' );
	} else {
		$color  = '#2e7d32';
		$status = __( 'Good', 'wpshadow' );
	}

	return array(
		'score'          => $score,
		'status'         => $status,
		'color'          => $color,
		'message'        => sprintf(
			// translators: %1$d = passed tests, %2$d = total tests.
			__( '%1$d of %2$d WordPress health checks passed', 'wpshadow' ),
			$passed,
			$total
		),
		'findings_count' => $critical + $recommended,
	);
}
