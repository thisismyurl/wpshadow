<?php
/**
 * Accessibility Auto-Fix Suggestions Diagnostic
 *
 * Identifies common accessibility issues with suggested fixes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26030.2000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Auto-Fix Suggestions Diagnostic
 *
 * Detects auto-fixable accessibility patterns and suggests remedies.
 *
 * @since 1.26030.2000
 */
class Diagnostic_A11y_Auto_Fix_Suggestions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'a11y-auto-fix-suggestions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Auto-Fix Opportunities';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies accessibility issues that can be auto-fixed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26030.2000
	 * @return array|null Finding array if fixable issues detected, null otherwise.
	 */
	public static function check() {
		$fixable_issues = array();

		// Check for pages missing alt text on images
		$pages_with_images = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 100,
				'post_status'    => 'publish',
			)
		);

		$pages_missing_alt = 0;
		foreach ( $pages_with_images as $page ) {
			if ( preg_match_all( '/<img[^>]*>/i', $page->post_content, $matches ) ) {
				foreach ( $matches[0] as $img_tag ) {
					if ( ! preg_match( '/alt\s*=\s*["\'][^"\']*["\']/i', $img_tag ) ) {
						++$pages_missing_alt;
					}
				}
			}
		}

		if ( $pages_missing_alt > 0 ) {
			$fixable_issues[] = sprintf(
				/* translators: %d: number of images missing alt text */
				__( '%d images missing alt text (auto-fixable with AI suggestions)', 'wpshadow' ),
				$pages_missing_alt
			);
		}

		// Check for buttons without accessible labels
		$custom_option = get_option( 'wpshadow_a11y_buttons_missing_labels', 0 );
		if ( $custom_option > 0 ) {
			$fixable_issues[] = sprintf(
				/* translators: %d: number of buttons */
				__( '%d buttons without aria-label (auto-fixable)', 'wpshadow' ),
				$custom_option
			);
		}

		if ( ! empty( $fixable_issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'We found several accessibility issues that can be auto-fixed. Use the Auto-Fix tool to apply suggested corrections for WCAG 2.1 Level AA compliance.', 'wpshadow' ),
				'details'     => implode( '; ', $fixable_issues ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/a11y-auto-fix',
			);
		}

		return null;
	}
}
