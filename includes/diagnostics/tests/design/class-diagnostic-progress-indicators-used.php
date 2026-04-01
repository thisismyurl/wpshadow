<?php
/**
 * Progress Indicators Used Diagnostic
 *
 * Tests whether the site uses progress indicators in multi-step processes to improve completion rates.
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
 * Progress Indicators Used Diagnostic Class
 *
 * Progress bars increase completion rates by 28% by reducing uncertainty
 * and motivating users to finish what they started.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Progress_Indicators_Used extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'progress-indicators-used';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Progress Indicators Used';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses progress indicators in multi-step processes to improve completion rates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cro';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$progress_score = 0;
		$max_score = 5;

		// Check for multi-step forms.
		$multistep_forms = self::check_multistep_forms();
		if ( $multistep_forms ) {
			$progress_score++;
		} else {
			$issues[] = __( 'No multi-step forms with progress indicators', 'wpshadow' );
		}

		// Check checkout progress.
		$checkout_progress = self::check_checkout_progress();
		if ( $checkout_progress ) {
			$progress_score++;
		} else {
			$issues[] = __( 'Checkout process missing progress indicators', 'wpshadow' );
		}

		// Check onboarding progress.
		$onboarding_progress = self::check_onboarding_progress();
		if ( $onboarding_progress ) {
			$progress_score++;
		} else {
			$issues[] = __( 'No progress tracking in onboarding flows', 'wpshadow' );
		}

		// Check profile completion.
		$profile_completion = self::check_profile_completion();
		if ( $profile_completion ) {
			$progress_score++;
		} else {
			$issues[] = __( 'No profile completion indicators for users', 'wpshadow' );
		}

		// Check course/content progress.
		$content_progress = self::check_content_progress();
		if ( $content_progress ) {
			$progress_score++;
		} else {
			$issues[] = __( 'No progress tracking for courses or multi-part content', 'wpshadow' );
		}

		// Determine severity based on progress indicator implementation.
		$progress_percentage = ( $progress_score / $max_score ) * 100;

		if ( $progress_percentage < 30 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $progress_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Progress indicator implementation percentage */
				__( 'Progress indicator usage at %d%%. ', 'wpshadow' ),
				(int) $progress_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Progress bars increase completion rates by 28%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/progress-indicators-used?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check for multi-step forms.
	 *
	 * @since 0.6093.1200
	 * @return bool True if multi-step forms exist, false otherwise.
	 */
	private static function check_multistep_forms() {
		// Check for form plugins that support multi-step.
		if ( is_plugin_active( 'wpforms-lite/wpforms.php' ) ||
			 is_plugin_active( 'formidable/formidable.php' ) ||
			 is_plugin_active( 'gravity-forms/gravityforms.php' ) ) {
			return true;
		}

		// Check for step indicators in content.
		$query = new \WP_Query(
			array(
				's'              => 'step 1 of progress bar',
				'post_type'      => 'page',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check checkout progress.
	 *
	 * @since 0.6093.1200
	 * @return bool True if checkout progress exists, false otherwise.
	 */
	private static function check_checkout_progress() {
		// WooCommerce with multi-step checkout.
		if ( class_exists( 'WooCommerce' ) ) {
			// Check for multi-step checkout plugins.
			if ( is_plugin_active( 'woocommerce-multistep-checkout/woocommerce-multistep-checkout.php' ) ) {
				return true;
			}

			// Many themes include checkout progress.
			return true;
		}

		return apply_filters( 'wpshadow_has_checkout_progress', false );
	}

	/**
	 * Check onboarding progress.
	 *
	 * @since 0.6093.1200
	 * @return bool True if onboarding progress exists, false otherwise.
	 */
	private static function check_onboarding_progress() {
		// Check for onboarding content with progress.
		$query = new \WP_Query(
			array(
				's'              => 'onboarding progress complete profile',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		if ( $query->have_posts() ) {
			return true;
		}

		// GamiPress can track progress.
		if ( is_plugin_active( 'gamipress/gamipress.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_onboarding_progress', false );
	}

	/**
	 * Check profile completion.
	 *
	 * @since 0.6093.1200
	 * @return bool True if profile completion exists, false otherwise.
	 */
	private static function check_profile_completion() {
		// BuddyPress and similar have profile completion.
		if ( is_plugin_active( 'buddypress/bp-loader.php' ) ||
			 is_plugin_active( 'peepso-core/peepso.php' ) ) {
			return true;
		}

		// Check for profile completion content.
		$query = new \WP_Query(
			array(
				's'              => 'profile complete % completion',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check content progress.
	 *
	 * @since 0.6093.1200
	 * @return bool True if content progress exists, false otherwise.
	 */
	private static function check_content_progress() {
		// LMS plugins track course progress.
		$lms_plugins = array(
			'learndash/learndash.php',
			'sensei-lms/sensei-lms.php',
			'lifterlms/lifterlms.php',
		);

		foreach ( $lms_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_content_progress', false );
	}
}
