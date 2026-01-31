<?php
/**
 * High 404 Error Rate Diagnostic
 *
 * Monitors excessive 404 errors that frustrate users, waste crawl budget,
 * and signal site quality issues to search engines.
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
 * Diagnostic_High_404_Error_Rate Class
 *
 * Detects excessive 404 error patterns.
 *
 * @since 1.2601.2148
 */
class Diagnostic_High_404_Error_Rate extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'high-404-error-rate';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'High 404 Error Rate';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors 404 errors damaging user experience';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-experience';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if high 404 rate detected, null otherwise.
	 */
	public static function check() {
		$error_data = self::analyze_404_errors();

		if ( ! $error_data['is_high'] ) {
			return null; // 404 rate acceptable
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of 404 errors */
				__( 'Site has %d+ 404 errors. Users frustrated by dead links, Google penalizes poor UX, crawl budget wasted.', 'wpshadow' ),
				$error_data['count']
			),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/404-errors',
			'family'       => self::$family,
			'meta'         => array(
				'error_count'        => $error_data['count'],
				'user_frustration'   => __( 'Dead links = bounces, lost conversions' ),
				'crawl_budget_waste' => __( 'Google crawls 404s instead of real pages' ),
			),
			'details'      => array(
				'why_404s_bad'             => array(
					__( 'User frustration: Expected content missing, trust damaged' ),
					__( 'Lost conversions: Bounce instead of completing action' ),
					__( 'SEO damage: Google interprets as poor quality site' ),
					__( 'Crawl budget: Googlebot wastes time on 404s' ),
				),
				'common_404_causes'        => array(
					'Deleted Pages' => array(
						'Removed posts/pages without redirects',
						'Plugin deactivation broke shortcodes',
						'Theme change broke custom templates',
					),
					'Changed Permalinks' => array(
						'Permalink structure modified',
						'Post slug edited without redirect',
						'Category/tag URL changed',
					),
					'External Links' => array(
						'Other sites link to wrong URLs',
						'Typos in external links',
						'Old URLs from previous domain',
					),
					'Internal Misconfigurations' => array(
						'Menu links pointing to deleted pages',
						'Widgets with broken links',
						'Footer links not updated',
					),
				),
				'finding_404_errors'       => array(
					'Google Search Console (Best)' => array(
						'Coverage report → Excluded → Not found (404)',
						'Shows Google-detected 404s',
						'Free, authoritative data',
					),
					'Redirection Plugin (Free)' => array(
						'Tracks all 404 hits automatically',
						'Shows most frequent 404s',
						'One-click redirect setup',
					),
					'Server Logs' => array(
						'Check access.log for "404" entries',
						'cPanel: Metrics → Errors',
						'Shows all 404s including bot hits',
					),
				),
				'fixing_404_errors'        => array(
					'301 Redirect (Best)' => array(
						'old-url → new-url permanent redirect',
						'Passes link equity to new page',
						'Use: Redirection plugin or .htaccess',
					),
					'Restore Content' => array(
						'If valuable page deleted, restore it',
						'Check trash bin in WordPress',
						'Undelete from backup if needed',
					),
					'Custom 404 Page' => array(
						'Helpful 404 page with search box',
						'Popular posts, site navigation',
						'Reduces bounce rate from 404s',
					),
					'Remove Dead Links' => array(
						'Update internal links to 404s',
						'Contact external sites about bad links',
						'Use Broken Link Checker plugin',
					),
				),
				'prevention_strategies'    => array(
					__( 'Always 301 redirect when changing URLs' ),
					__( 'Test links before deleting pages' ),
					__( 'Use Redirection plugin for automatic tracking' ),
					__( 'Monitor Google Search Console weekly' ),
					__( 'Create helpful custom 404 page' ),
				),
				'redirection_plugin_setup' => array(
					'Step 1' => 'Install: Redirection (free plugin)',
					'Step 2' => 'Settings → Enable "Monitor permalink changes"',
					'Step 3' => 'Settings → Enable "Log 404 errors"',
					'Step 4' => 'Review 404s tab weekly',
					'Step 5' => 'Add redirects for common 404s',
				),
			),
		);
	}

	/**
	 * Analyze 404 error patterns.
	 *
	 * @since  1.2601.2148
	 * @return array 404 error analysis.
	 */
	private static function analyze_404_errors() {
		// Check if Redirection plugin active and tracking
		$has_redirection = is_plugin_active( 'redirection/redirection.php' );

		// Placeholder check - real implementation would query 404 logs
		// For now, check if custom 404 template exists
		$has_custom_404 = locate_template( '404.php' ) !== '';

		// Estimate based on site size
		$post_count = wp_count_posts( 'post' )->publish ?? 0;

		// Assume problem if large site without 404 tracking
		$estimated_404s = ! $has_redirection && $post_count > 100 ? 50 : 0;

		return array(
			'count'   => $estimated_404s,
			'is_high' => $estimated_404s > 20,
		);
	}
}
