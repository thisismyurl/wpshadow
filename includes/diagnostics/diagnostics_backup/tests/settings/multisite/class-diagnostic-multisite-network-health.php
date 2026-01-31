<?php
/**
 * Multisite Network Site Health Diagnostic
 *
 * Monitors overall health of WordPress multisite network, detecting
 * issues with network configuration, site synchronization, and shared resources.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Multisite_Network_Health Class
 *
 * Detects multisite network health issues.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Multisite_Network_Health extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-network-health';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Network Health';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors multisite network configuration and health';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'multisite';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Only run on multisite
		if ( ! is_multisite() ) {
			return null;
		}

		$health_check = self::check_network_health();

		if ( empty( $health_check['issues'] ) ) {
			return null; // Network healthy
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of issues */
				__( '%d multisite network issues detected. Network problems cascade to all sub-sites, affecting thousands of users.', 'wpshadow' ),
				count( $health_check['issues'] )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-health',
			'family'       => self::$family,
			'meta'         => array(
				'total_sites'  => $health_check['total_sites'],
				'issues_found' => $health_check['issues'],
				'network_type' => $health_check['network_type'],
			),
			'details'      => array(
				'multisite_architecture'      => array(
					'Subdomain' => 'site1.example.com, site2.example.com (requires wildcard DNS)',
					'Subdirectory' => 'example.com/site1, example.com/site2 (simpler setup)',
					'Domain Mapping' => 'customdomain.com → example.com/site1 (requires plugin)',
				),
				'common_multisite_issues'     => array(
					'Broken Domain Mapping' => array(
						'Problem: Mapped domains not resolving',
						'Cause: DNS misconfiguration, plugin issues',
						'Fix: Check DNS A records, verify domain mapping plugin',
					),
					'Shared Theme Conflicts' => array(
						'Problem: Network theme breaks some sites',
						'Cause: Theme not multisite-compatible',
						'Fix: Test theme on staging site first',
					),
					'Network Plugin Crashes' => array(
						'Problem: Network-activated plugin breaks all sites',
						'Cause: Plugin incompatibility',
						'Fix: Use WP-CLI to deactivate: wp plugin deactivate --network',
					),
					'Upload Directory Issues' => array(
						'Problem: /wp-content/uploads/sites/X/ permission errors',
						'Cause: Incorrect file permissions',
						'Fix: chmod 755 on directories, 644 on files',
					),
				),
				'network_admin_best_practices' => array(
					__( 'Test plugins on single site before network activation' ),
					__( 'Use staging network for major changes' ),
					__( 'Monitor disk usage across all sites' ),
					__( 'Regular database cleanup (transients, revisions)' ),
					__( 'Centralized backup strategy for all sites' ),
				),
				'performance_considerations'  => array(
					'Shared Database' => array(
						'All sites share wp_users table',
						'Site-specific tables: wp_X_posts, wp_X_options',
						'Optimization: Regular database cleanup',
					),
					'Shared Uploads' => array(
						'Each site: /wp-content/uploads/sites/X/',
						'Can grow large quickly',
						'Solution: CDN for uploads, S3 offload',
					),
					'Caching Complexity' => array(
						'Must cache per-site, not globally',
						'Plugin: WP Rocket multisite mode',
						'Server: Varnish with site-specific rules',
					),
				),
				'troubleshooting_network_issues' => array(
					'Site Not Loading' => array(
						'Check: wp-config.php MULTISITE constants',
						'Verify: .htaccess rules for subdomains/subdirectories',
						'Test: wp-cli site list --url=example.com',
					),
					'Login Redirects Wrong Site' => array(
						'Check: COOKIE_DOMAIN in wp-config.php',
						'Should be: .example.com (leading dot for wildcard)',
					),
					'Theme/Plugin Not Appearing' => array(
						'Check: Network Admin → Sites → Edit → Themes',
						'Enable theme for specific site',
					),
				),
			),
		);
	}

	/**
	 * Check network health.
	 *
	 * @since  1.2601.2148
	 * @return array Network health status.
	 */
	private static function check_network_health() {
		if ( ! is_multisite() ) {
			return array(
				'issues' => array(),
			);
		}

		$issues = array();

		// Check network site count
		$total_sites = get_blog_count();

		// Check if subdomain or subdirectory
		$network_type = defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL ? 'subdomain' : 'subdirectory';

		// Check for common issues
		// 1. Check if sunrise.php exists (domain mapping)
		if ( defined( 'SUNRISE' ) && SUNRISE ) {
			if ( ! file_exists( WP_CONTENT_DIR . '/sunrise.php' ) ) {
				$issues[] = __( 'SUNRISE enabled but sunrise.php missing', 'wpshadow' );
			}
		}

		// 2. Check for large number of sites (performance concern)
		if ( $total_sites > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of sites */
				__( 'Network has %d sites - consider performance optimization', 'wpshadow' ),
				$total_sites
			);
		}

		return array(
			'total_sites'  => $total_sites,
			'network_type' => $network_type,
			'issues'       => $issues,
		);
	}
}
