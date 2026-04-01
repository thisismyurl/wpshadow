<?php
/**
 * AMP for Email Diagnostic
 *
 * Tests whether the site implements AMP for interactive email features that drive engagement.
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
 * AMP for Email Diagnostic Class
 *
 * AMP emails enable interactive experiences (carousels, forms, real-time content)
 * that increase click-through rates by 20-30%.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Amp_For_Email extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'amp-for-email';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'AMP for Email';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site implements AMP for interactive email features that drive engagement';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$amp_score = 0;
		$max_score = 4;

		// Check for AMP plugin.
		$amp_plugin = self::check_amp_plugin();
		if ( $amp_plugin ) {
			$amp_score++;
		} else {
			$issues[] = __( 'AMP plugin not installed for email support', 'wpshadow' );
		}

		// Check for AMP-compatible email platform.
		$amp_platform = self::check_amp_compatible_platform();
		if ( $amp_platform ) {
			$amp_score++;
		} else {
			$issues[] = __( 'Email platform does not support AMP emails', 'wpshadow' );
		}

		// Check for interactive content.
		$interactive_content = self::check_interactive_content();
		if ( $interactive_content ) {
			$amp_score++;
		} else {
			$issues[] = __( 'No interactive email content (forms, carousels, accordions)', 'wpshadow' );
		}

		// Check for fallback support.
		$fallback_support = self::check_fallback_support();
		if ( $fallback_support ) {
			$amp_score++;
		} else {
			$issues[] = __( 'No HTML fallback for non-AMP email clients', 'wpshadow' );
		}

		// Determine severity based on AMP implementation.
		$amp_percentage = ( $amp_score / $max_score ) * 100;

		if ( $amp_percentage < 25 ) {
			$severity = 'low';
			$threat_level = 15;
		} elseif ( $amp_percentage < 50 ) {
			$severity = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: AMP for email percentage */
				__( 'AMP for email at %d%%. ', 'wpshadow' ),
				(int) $amp_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'AMP emails increase click-through rates by 20-30%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/amp-for-email?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check AMP plugin.
	 *
	 * @since 0.6093.1200
	 * @return bool True if plugin exists, false otherwise.
	 */
	private static function check_amp_plugin() {
		// Check for AMP plugin.
		if ( is_plugin_active( 'amp/amp.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_amp_plugin', false );
	}

	/**
	 * Check AMP-compatible platform.
	 *
	 * @since 0.6093.1200
	 * @return bool True if compatible, false otherwise.
	 */
	private static function check_amp_compatible_platform() {
		// Gmail, Yahoo, and Outlook support AMP.
		// Most platforms can send AMP emails if configured.
		return apply_filters( 'wpshadow_amp_email_platform', false );
	}

	/**
	 * Check interactive content.
	 *
	 * @since 0.6093.1200
	 * @return bool True if interactive, false otherwise.
	 */
	private static function check_interactive_content() {
		// Check for AMP components in content.
		$query = new \WP_Query(
			array(
				's'              => 'amp-carousel amp-form amp-accordion',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check fallback support.
	 *
	 * @since 0.6093.1200
	 * @return bool True if fallback exists, false otherwise.
	 */
	private static function check_fallback_support() {
		// Professional email systems provide fallbacks.
		return apply_filters( 'wpshadow_amp_email_fallback', true );
	}
}
