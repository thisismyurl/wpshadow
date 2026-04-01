<?php
/**
 * Form Optimization Diagnostic
 *
 * Tests if forms are optimized for conversion.
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
 * Form Optimization Diagnostic Class
 *
 * Evaluates whether forms are optimized for conversion and user experience.
 * Checks for form plugins, validation, analytics, A/B testing, and optimization features.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Form_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'optimizes_forms';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Form Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if forms are optimized for conversion';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$stats         = array();
		$issues        = array();
		$warnings      = array();
		$score         = 0;
		$total_points  = 0;
		$earned_points = 0;

		// Check for advanced form builder plugins.
		$form_plugins = array(
			'gravityforms/gravityforms.php'                 => 'Gravity Forms',
			'wpforms-lite/wpforms.php'                      => 'WPForms',
			'wpforms/wpforms.php'                           => 'WPForms Pro',
			'formidable/formidable.php'                     => 'Formidable Forms',
			'ninja-forms/ninja-forms.php'                   => 'Ninja Forms',
			'contact-form-7/wp-contact-form-7.php'          => 'Contact Form 7',
			'fluentform/fluentform.php'                     => 'Fluent Forms',
			'elementor-pro/elementor-pro.php'               => 'Elementor Pro Forms',
			'everest-forms/everest-forms.php'               => 'Everest Forms',
		);

		$active_form_plugins = array();
		foreach ( $form_plugins as $plugin => $name ) {
			$total_points += 10;
			if ( is_plugin_active( $plugin ) ) {
				$active_form_plugins[] = $name;
				$earned_points        += 10;
				break; // Only need one form plugin.
			}
		}

		$stats['form_plugins'] = array(
			'found' => count( $active_form_plugins ),
			'list'  => $active_form_plugins,
		);

		if ( empty( $active_form_plugins ) ) {
			$issues[] = __( 'No advanced form builder plugin detected', 'wpshadow' );
		}

		// Check for form analytics/tracking.
		$total_points += 15;
		$form_analytics_plugins = array(
			'gravityforms-google-analytics/gravityforms-google-analytics.php' => 'Gravity Forms Analytics',
			'wpforms-lite/wpforms.php' => 'WPForms (includes analytics)',
			'mc4wp-premium/mc4wp-premium.php' => 'MC4WP Premium',
		);

		$active_form_analytics = array();
		foreach ( $form_analytics_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_form_analytics[] = $name;
			}
		}

		// Check for Google Analytics event tracking.
		if ( wp_script_is( 'google-analytics', 'enqueued' ) ||
			 wp_script_is( 'gtag', 'enqueued' ) ||
			 ! empty( $active_form_analytics ) ) {
			$earned_points += 15;
			$stats['form_analytics'] = true;
		} else {
			$stats['form_analytics'] = false;
			$issues[] = __( 'No form analytics or tracking detected', 'wpshadow' );
		}

		// Check for form validation features.
		$total_points += 10;
		$has_validation = false;

		// Advanced form plugins typically have built-in validation.
		if ( ! empty( $active_form_plugins ) ) {
			$has_validation = true;
			$earned_points += 10;
		}

		$stats['validation_enabled'] = $has_validation;

		if ( ! $has_validation ) {
			$warnings[] = __( 'No advanced form validation detected', 'wpshadow' );
		}

		// Check for multi-step form capability.
		$total_points += 10;
		$multistep_capable = array(
			'gravityforms/gravityforms.php',
			'wpforms/wpforms.php',
			'formidable/formidable.php',
			'fluentform/fluentform.php',
		);

		$has_multistep = false;
		foreach ( $multistep_capable as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_multistep = true;
				$earned_points += 10;
				break;
			}
		}

		$stats['multistep_capable'] = $has_multistep;

		if ( ! $has_multistep ) {
			$warnings[] = __( 'No multi-step form capability detected', 'wpshadow' );
		}

		// Check for conditional logic support.
		$total_points += 10;
		$conditional_logic_plugins = array(
			'gravityforms/gravityforms.php',
			'wpforms/wpforms.php',
			'formidable/formidable.php',
			'ninja-forms/ninja-forms.php',
			'fluentform/fluentform.php',
		);

		$has_conditional_logic = false;
		foreach ( $conditional_logic_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_conditional_logic = true;
				$earned_points        += 10;
				break;
			}
		}

		$stats['conditional_logic'] = $has_conditional_logic;

		if ( ! $has_conditional_logic ) {
			$warnings[] = __( 'No conditional logic support detected', 'wpshadow' );
		}

		// Check for A/B testing capability.
		$total_points += 15;
		$ab_testing_tools = array(
			'google-optimize' => 'Google Optimize',
			'optimizely'      => 'Optimizely',
			'vwo'             => 'VWO',
		);

		$active_ab_tools = array();
		foreach ( $ab_testing_tools as $handle => $name ) {
			if ( wp_script_is( $handle, 'enqueued' ) || wp_script_is( $handle, 'registered' ) ) {
				$active_ab_tools[] = $name;
			}
		}

		if ( ! empty( $active_ab_tools ) ) {
			$earned_points += 15;
		}

		$stats['ab_testing'] = array(
			'found' => count( $active_ab_tools ),
			'list'  => $active_ab_tools,
		);

		if ( empty( $active_ab_tools ) ) {
			$warnings[] = __( 'No A/B testing tools detected for form optimization', 'wpshadow' );
		}

		// Check for spam protection.
		$total_points += 10;
		$spam_protection = array(
			'akismet/akismet.php'                 => 'Akismet',
			'recaptcha/recaptcha.php'             => 'reCAPTCHA',
			'google-captcha/google-captcha.php'   => 'Google reCAPTCHA',
			'hcaptcha-for-forms-and-more/hcaptcha.php' => 'hCaptcha',
		);

		$active_spam_protection = array();
		foreach ( $spam_protection as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_spam_protection[] = $name;
			}
		}

		if ( ! empty( $active_spam_protection ) ) {
			$earned_points += 10;
		}

		$stats['spam_protection'] = array(
			'found' => count( $active_spam_protection ),
			'list'  => $active_spam_protection,
		);

		// Check for email marketing integration.
		$total_points += 10;
		$email_integrations = array(
			'mailchimp-for-wp/mailchimp-for-wp.php' => 'Mailchimp',
			'constant-contact-forms/constant-contact-forms.php' => 'Constant Contact',
			'convertkit/convertkit.php' => 'ConvertKit',
			'activecampaign/activecampaign.php' => 'ActiveCampaign',
		);

		$active_email_integrations = array();
		foreach ( $email_integrations as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_email_integrations[] = $name;
			}
		}

		if ( ! empty( $active_email_integrations ) ) {
			$earned_points += 10;
		}

		$stats['email_integrations'] = array(
			'found' => count( $active_email_integrations ),
			'list'  => $active_email_integrations,
		);

		// Check for progressive profiling.
		$total_points += 10;
		$progressive_profiling_plugins = array(
			'gravityforms/gravityforms.php', // Has partial entries.
			'fluentform/fluentform.php',     // Has save & resume.
		);

		$has_progressive = false;
		foreach ( $progressive_profiling_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_progressive = true;
				$earned_points  += 10;
				break;
			}
		}

		$stats['progressive_profiling'] = $has_progressive;

		// Calculate final score.
		if ( $total_points > 0 ) {
			$score = round( ( $earned_points / $total_points ) * 100 );
		}

		$stats['score']         = $score;
		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;

		// Determine severity.
		$severity     = 'medium';
		$threat_level = 40;

		if ( $score < 30 ) {
			$severity     = 'high';
			$threat_level = 55;
		} elseif ( $score > 70 ) {
			$severity     = 'low';
			$threat_level = 25;
		}

		// Return finding if form optimization is insufficient.
		if ( $score < 50 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: optimization score percentage */
					__( 'Form optimization score: %d%%. Optimized forms can significantly improve conversion rates and user experience.', 'wpshadow' ),
					$score
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/form-optimization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
