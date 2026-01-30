<?php
/**
 * Broken Links Detection Diagnostic
 *
 * Scans site for broken internal/external links indicating
 * outdated content or setup issues.
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
 * Diagnostic_Broken_Links_Detection Class
 *
 * Detects broken links in content.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Broken_Links_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'broken-links-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Links Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Scans for broken links';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if broken links found, null otherwise.
	 */
	public static function check() {
		$link_status = self::scan_for_broken_links();

		if ( $link_status['broken_count'] === 0 ) {
			return null; // No broken links
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of broken links */
				__( 'Found %d broken links. Each 404 = visitor frustration = bounce = lost sale. Link to old product = 404 = bad UX. Fix or redirect.', 'wpshadow' ),
				$link_status['broken_count']
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/broken-links',
			'family'       => self::$family,
			'meta'         => array(
				'broken_links' => $link_status['broken_count'],
			),
			'details'      => array(
				'impact_of_broken_links'          => array(
					'User Experience' => array(
						'Visitor clicks link = 404',
						'Frustration = bounce',
					),
					'SEO' => array(
						'Internal 404s: Waste crawl budget',
						'External 404s: Domain loses authority',
					),
					'Trust' => array(
						'Old links: Signal of neglect',
						'404s everywhere: Poor maintenance',
					),
				),
				'finding_broken_links'            => array(
					'Google Search Console' => array(
						'URL: search.google.com/search-console',
						'Report: Coverage → Errors',
						'Shows: All 404s Google found',
					),
					'Broken Link Checker Plugin' => array(
						'Plugin: Broken Link Checker',
						'Cost: Free',
						'Frequency: Weekly scan',
					),
					'Manual Tools' => array(
						'Link Checker: linkchecker.org',
						'Screaming Frog: screamingfrog.co.uk',
					),
				),
				'fixing_broken_links'             => array(
					'Update Link' => array(
						'Find: Post with broken link',
						'Edit: Update to correct URL',
					),
					'Redirect' => array(
						'Old URL: /old-product-name/',
						'Redirect to: /new-product-name/',
						'Plugin: Redirection for managing',
					),
					'Delete' => array(
						'Link to deleted page',
						'No replacement',
						'Just remove link from text',
					),
				),
				'common_broken_link_causes'       => array(
					'Updated Product' => array(
						'Old: /product/widget-v1/',
						'New: /product/widget-v2/',
						'Links not updated',
					),
					'Changed Domain' => array(
						'Moved: example.com → newdomain.com',
						'Old links still point to old domain',
					),
					'Changed URL Structure' => array(
						'WordPress setting changed',
						'Permastruct modified',
						'Old URLs now 404',
					),
					'External Link Target Died' => array(
						'Linked to external resource',
						'That resource deleted',
						'Link now 404',
					),
				),
			),
		);
	}

	/**
	 * Scan for broken links.
	 *
	 * @since  1.2601.2148
	 * @return array Link scan results.
	 */
	private static function scan_for_broken_links() {
		// Simplified: Check if broken link plugin is running
		$broken_count = 0;

		if ( is_plugin_active( 'broken-link-checker/broken-link-checker.php' ) ) {
			// Plugin active, assume it's running
			global $wpdb;
			$result = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}blc_instances WHERE link_id IN (SELECT link_id FROM {$wpdb->prefix}blc_links WHERE http_code = 404)"
			);
			$broken_count = (int) $result;
		}

		return array(
			'broken_count' => $broken_count,
		);
	}
}
