<?php
/**
 * Transactional Email Optimization Diagnostic
 *
 * Checks if transactional emails are optimized for engagement and revenue.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transactional Email Optimization Diagnostic Class
 *
 * Transactional emails get 80% open rates vs 20% for marketing emails.
 * You're sending them anyway—optimize to increase revenue.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Transactional_Email_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'transactional-email-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Transactional Email Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if transactional emails are optimized for engagement';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'e-commerce-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues             = array();
		$optimization_score = 0;
		$max_score          = 5;

		// Check if emails are branded.
		$has_branding = self::check_email_branding();
		if ( $has_branding ) {
			++$optimization_score;
		} else {
			$issues[] = 'branded email templates';
		}

		// Check for product recommendations.
		$has_recommendations = self::check_product_recommendations();
		if ( $has_recommendations ) {
			++$optimization_score;
		} else {
			$issues[] = 'related product recommendations';
		}

		// Check for clear next steps.
		$has_next_steps = self::check_clear_next_steps();
		if ( $has_next_steps ) {
			++$optimization_score;
		} else {
			$issues[] = 'clear next steps/CTAs';
		}

		// Check for open/click tracking.
		$has_tracking = self::check_email_tracking();
		if ( $has_tracking ) {
			++$optimization_score;
		} else {
			$issues[] = 'open and click tracking';
		}

		// Check for mobile optimization.
		$has_mobile = self::check_mobile_optimization();
		if ( $has_mobile ) {
			++$optimization_score;
		} else {
			$issues[] = 'mobile-optimized templates';
		}

		$completion_percentage = ( $optimization_score / $max_score ) * 100;

		if ( $completion_percentage >= 60 ) {
			return null; // Emails properly optimized.
		}

		$severity     = $completion_percentage < 40 ? 'medium' : 'low';
		$threat_level = $completion_percentage < 40 ? 50 : 30;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Email optimization at %1$d%%. Missing: %2$s. Like giving receipt with coupon vs blank receipt—same effort, more return.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/transactional-email-optimization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check if emails have branding.
	 *
	 * @since 0.6093.1200
	 * @return bool True if branded.
	 */
	private static function check_email_branding(): bool {
		// Check for WooCommerce email customizer.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$email_customizers = array(
			'woocommerce-email-customizer/woocommerce-email-customizer.php',
			'kadence-woocommerce-email-designer/kadence-woocommerce-email-designer.php',
			'woo-advanced-email-customizer/woo-advanced-email-customizer.php',
		);

		foreach ( $email_customizers as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check if email templates are customized.
		$template_path = get_stylesheet_directory() . '/woocommerce/emails/';
		if ( file_exists( $template_path ) && is_dir( $template_path ) ) {
			$files = glob( $template_path . '*.php' );
			if ( ! empty( $files ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for product recommendations.
	 *
	 * @since 0.6093.1200
	 * @return bool True if recommendations exist.
	 */
	private static function check_product_recommendations(): bool {
		// Check for recommendation plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$recommendation_plugins = array(
			'woocommerce-product-recommendations/woocommerce-product-recommendations.php',
			'woocommerce-mailchimp/woocommerce-mailchimp.php',
		);

		foreach ( $recommendation_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for clear next steps.
	 *
	 * @since 0.6093.1200
	 * @return bool True if next steps exist.
	 */
	private static function check_clear_next_steps(): bool {
		// If email customizer is active, assume CTAs are added.
		return self::check_email_branding();
	}

	/**
	 * Check for email tracking.
	 *
	 * @since 0.6093.1200
	 * @return bool True if tracking exists.
	 */
	private static function check_email_tracking(): bool {
		// Check for email tracking plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$tracking_plugins = array(
			'mailchimp-for-woocommerce/mailchimp-woocommerce.php',
			'wp-mail-smtp/wp_mail_smtp.php',
		);

		foreach ( $tracking_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for mobile optimization.
	 *
	 * @since 0.6093.1200
	 * @return bool True if mobile optimized.
	 */
	private static function check_mobile_optimization(): bool {
		// Email customizer plugins typically include responsive templates.
		return self::check_email_branding();
	}
}
