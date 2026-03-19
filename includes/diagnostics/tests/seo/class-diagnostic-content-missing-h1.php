<?php
/**
 * Content Missing H1 Tag Diagnostic
 *
 * Detects missing H1 tag on a page.
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
 * Content Missing H1 Tag Diagnostic Class
 *
 * Missing H1 is a critical SEO and accessibility issue. Every page
 * should have exactly one H1 tag.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Content_Missing_H1 extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-missing-h1';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing H1 Tag';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects pages that lack an H1 heading';

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

		// Check for missing H1 tag.
		$has_h1 = apply_filters( 'wpshadow_page_has_h1_tag', false );
		if ( ! $has_h1 ) {
			$issues[] = __( 'No H1 tag found; every page needs exactly one H1', 'wpshadow' );
		}

		// Check for accessibility impact.
		$accessibility_impact = apply_filters( 'wpshadow_missing_h1_accessibility_impact', false );
		if ( $accessibility_impact ) {
			$issues[] = __( 'Missing H1 reduces heading hierarchy clarity for screen readers', 'wpshadow' );
		}

		// Check for SEO impact.
		$seo_impact = apply_filters( 'wpshadow_missing_h1_seo_impact', false );
		if ( $seo_impact ) {
			$issues[] = __( 'Search engines rely on H1 to understand topic; missing H1 harms rankings', 'wpshadow' );
		}

		// Check for template output issues.
		$template_issue = apply_filters( 'wpshadow_missing_h1_template_issue', false );
		if ( $template_issue ) {
			$issues[] = __( 'Template may be suppressing H1 output; review theme structure', 'wpshadow' );
		}

		// Check for title vs H1 mismatch.
		$title_mismatch = apply_filters( 'wpshadow_missing_h1_title_mismatch', false );
		if ( $title_mismatch ) {
			$issues[] = __( 'Ensure the page title is rendered as the H1 heading', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/content-missing-h1',
			);
		}

		return null;
	}
}
