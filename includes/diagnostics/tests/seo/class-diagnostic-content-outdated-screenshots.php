<?php
/**
 * Content Outdated Screenshots Diagnostic
 *
 * Detects outdated UI screenshots in content.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Outdated Screenshots Diagnostic Class
 *
 * UI screenshots that are 2+ years old confuse users and reduce trust.
 * Updating top posts can boost conversions by ~30%.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Outdated_Screenshots extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-outdated-screenshots';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Outdated Screenshots';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects screenshots that are outdated or mismatched with current UI';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for outdated screenshot age.
		$outdated_count = apply_filters( 'wpshadow_outdated_screenshot_count', 0 );
		if ( $outdated_count > 0 ) {
			$issues[] = __( 'UI screenshots appear 2+ years old; update to match current interfaces', 'wpshadow' );
		}

		// Check for high-traffic posts with outdated screenshots.
		$top_posts_outdated = apply_filters( 'wpshadow_top_posts_have_outdated_screenshots', false );
		if ( $top_posts_outdated ) {
			$issues[] = __( 'Update screenshots in top 10 posts for up to 30% engagement boost', 'wpshadow' );
		}

		// Check for user confusion signals.
		$confusion_signals = apply_filters( 'wpshadow_outdated_screenshots_user_confusion', false );
		if ( $confusion_signals ) {
			$issues[] = __( 'Support tickets indicate confusion from outdated UI screenshots', 'wpshadow' );
		}

		// Check for branded UI mismatches.
		$branding_mismatch = apply_filters( 'wpshadow_screenshot_branding_mismatch', false );
		if ( $branding_mismatch ) {
			$issues[] = __( 'Screenshots show old branding; update for consistency and trust', 'wpshadow' );
		}

		// Check for dark mode/locale variance.
		$variant_mismatch = apply_filters( 'wpshadow_screenshot_variant_mismatch', false );
		if ( $variant_mismatch ) {
			$issues[] = __( 'Screenshots do not match current theme, locale, or dark mode', 'wpshadow' );
		}

		// Check for documentation update policy.
		$update_policy = apply_filters( 'wpshadow_screenshot_update_policy_defined', false );
		if ( ! $update_policy ) {
			$issues[] = __( 'Set a screenshot refresh policy (e.g., every major release)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-outdated-screenshots?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
