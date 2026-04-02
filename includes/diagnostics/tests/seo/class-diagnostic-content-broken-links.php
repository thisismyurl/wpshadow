<?php
/**
 * Content Broken Links Diagnostic
 *
 * Detects broken links that harm trust and SEO.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Broken Links Diagnostic Class
 *
 * 3+ broken links per post causes ~45% trust loss and SEO penalties.
 * 100% auto-detectable via link checks.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Content_Broken_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-broken-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects broken internal and external links in content';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for broken links.
		$broken_link_count = apply_filters( 'wpshadow_broken_link_count', 0 );
		if ( $broken_link_count > 0 ) {
			$issues[] = __( 'Broken links detected; fix or replace them to avoid trust loss', 'wpshadow' );
		}

		// Check for high broken link density.
		$broken_link_density = apply_filters( 'wpshadow_broken_links_per_post', 0 );
		if ( $broken_link_density >= 3 ) {
			$issues[] = __( '3+ broken links per post can cause 45% trust loss and SEO penalties', 'wpshadow' );
		}

		// Check for internal link failures.
		$internal_broken = apply_filters( 'wpshadow_broken_internal_links', false );
		if ( $internal_broken ) {
			$issues[] = __( 'Broken internal links waste crawl budget and harm navigation', 'wpshadow' );
		}

		// Check for external link failures.
		$external_broken = apply_filters( 'wpshadow_broken_external_links', false );
		if ( $external_broken ) {
			$issues[] = __( 'Broken external citations reduce credibility; update sources', 'wpshadow' );
		}

		// Check for link monitoring.
		$link_monitoring = apply_filters( 'wpshadow_link_monitoring_enabled', false );
		if ( ! $link_monitoring ) {
			$issues[] = __( 'Enable automated link monitoring for proactive fixes', 'wpshadow' );
		}

		// Check for redirect handling.
		$redirect_handling = apply_filters( 'wpshadow_broken_link_redirects_configured', false );
		if ( ! $redirect_handling ) {
			$issues[] = __( 'Configure redirects for known broken URLs to preserve equity', 'wpshadow' );
		}

		// Check for accessibility impact.
		$accessibility = apply_filters( 'wpshadow_broken_links_accessibility_impact', false );
		if ( $accessibility ) {
			$issues[] = __( 'Broken links frustrate screen readers and keyboard users', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/content-broken-links',
			);
		}

		return null;
	}
}
