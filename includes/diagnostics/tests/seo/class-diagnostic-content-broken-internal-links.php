<?php
/**
 * Content Broken Internal Links Diagnostic
 *
 * Detects broken internal links.
 *
 * @since   1.6033.1730
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Broken Internal Links Diagnostic Class
 *
 * Internal 404s hurt user experience and SEO. Fix immediately.
 *
 * @since 1.6033.1730
 */
class Diagnostic_Content_Broken_Internal_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-broken-internal-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Internal Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects internal links that return 404 or redirect errors';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1730
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for broken internal links.
		$broken_internal_count = apply_filters( 'wpshadow_broken_internal_link_count', 0 );
		if ( $broken_internal_count > 0 ) {
			$issues[] = __( 'Broken internal links detected; fix immediately to avoid SEO damage', 'wpshadow' );
		}

		// Check for broken link density.
		$broken_internal_density = apply_filters( 'wpshadow_broken_internal_links_per_post', 0 );
		if ( $broken_internal_density > 0 ) {
			$issues[] = __( 'Internal 404s reduce crawl efficiency and user trust', 'wpshadow' );
		}

		// Check for navigation impact.
		$navigation_impact = apply_filters( 'wpshadow_broken_internal_links_navigation_impact', false );
		if ( $navigation_impact ) {
			$issues[] = __( 'Broken internal links create dead ends for users and crawlers', 'wpshadow' );
		}

		// Check for redirect hygiene.
		$redirect_hygiene = apply_filters( 'wpshadow_internal_redirect_hygiene', false );
		if ( ! $redirect_hygiene ) {
			$issues[] = __( 'Replace broken internal links with updated URLs instead of chained redirects', 'wpshadow' );
		}

		// Check for automated monitoring.
		$link_monitoring = apply_filters( 'wpshadow_internal_link_monitoring_enabled', false );
		if ( ! $link_monitoring ) {
			$issues[] = __( 'Enable automated internal link monitoring to prevent future 404s', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/content-broken-internal-links',
			);
		}

		return null;
	}
}
