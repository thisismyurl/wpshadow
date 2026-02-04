<?php
/**
 * Content Multiple H1 Tags Diagnostic
 *
 * Detects multiple H1 tags on a page.
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
 * Content Multiple H1 Tags Diagnostic Class
 *
 * Multiple H1 tags confuse search engines about page topic and reduce
 * clarity for assistive technologies.
 *
 * @since 1.6033.1730
 */
class Diagnostic_Content_Multiple_H1 extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-multiple-h1';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multiple H1 Tags';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects pages with more than one H1 tag';

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

		// Check for multiple H1 tags.
		$h1_count = apply_filters( 'wpshadow_h1_tag_count', 0 );
		if ( $h1_count > 1 ) {
			$issues[] = __( 'Multiple H1 tags detected; use exactly one H1 per page', 'wpshadow' );
		}

		// Check for accessibility impact.
		$accessibility_impact = apply_filters( 'wpshadow_multiple_h1_accessibility_impact', false );
		if ( $accessibility_impact ) {
			$issues[] = __( 'Multiple H1 tags reduce heading hierarchy clarity for screen readers', 'wpshadow' );
		}

		// Check for template consistency.
		$template_consistency = apply_filters( 'wpshadow_h1_template_consistency', false );
		if ( ! $template_consistency ) {
			$issues[] = __( 'Ensure templates output a single H1 across content types', 'wpshadow' );
		}

		// Check for branding/logo H1 misuse.
		$logo_h1 = apply_filters( 'wpshadow_logo_uses_h1', false );
		if ( $logo_h1 ) {
			$issues[] = __( 'Logo should not use H1; reserve H1 for the page title', 'wpshadow' );
		}

		// Check for SEO impact.
		$seo_impact = apply_filters( 'wpshadow_multiple_h1_seo_impact', false );
		if ( $seo_impact ) {
			$issues[] = __( 'Multiple H1 tags dilute topic focus and reduce search clarity', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-multiple-h1',
			);
		}

		return null;
	}
}
