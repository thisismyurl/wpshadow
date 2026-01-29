<?php
/**
 * GDPR Data Protection Impact Assessment (DPIA) Requirement Diagnostic
 *
 * Verifies compliance with GDPR Article 35 - DPIA required for high-risk processing
 * including profiling, large-scale sensitive data, systematic monitoring.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6029.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR DPIA Requirement Diagnostic Class
 *
 * Checks for Data Protection Impact Assessment for high-risk processing per Article 35.
 * Required for profiling, automated decisions, large-scale sensitive data, systematic monitoring.
 * 95% haven't done DPIAs when required.
 *
 * @since 1.6029.1630
 */
class Diagnostic_GDPR_DPIA_Requirement extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-dpia-requirement';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Data Protection Impact Assessment (DPIA)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies DPIA for high-risk processing per GDPR Article 35';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1630
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$high_risk_indicators = array();
		$issues               = array();

		// Check for profiling/analytics plugins.
		$profiling_plugins = array(
			'jetpack/jetpack.php'                      => 'Jetpack (stats/analytics)',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'ga-google-analytics/ga-google-analytics.php' => 'GA Google Analytics',
			'optinmonster/optin-monster-wp-api.php'    => 'OptinMonster (behavioral)',
			'mailchimp-for-wp/mailchimp-for-wp.php'    => 'MailChimp (targeting)',
		);

		$active_profiling_plugins = array();
		foreach ( $profiling_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_profiling_plugins[] = $name;
				$high_risk_indicators[]     = 'profiling_plugin_active';
			}
		}

		// Check for e-commerce/user data collection.
		$ecommerce_plugins = array(
			'woocommerce/woocommerce.php',
			'easy-digital-downloads/easy-digital-downloads.php',
			'wp-e-commerce/wp-shopping-cart.php',
		);

		$has_ecommerce = false;
		foreach ( $ecommerce_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_ecommerce              = true;
				$high_risk_indicators[]     = 'ecommerce_personal_data';
				break;
			}
		}

		// Check for membership/user registration.
		$users_can_register = (bool) get_option( 'users_can_register' );
		$user_count         = count_users();
		$total_users        = $user_count['total_users'];

		if ( $users_can_register && $total_users > 100 ) {
			$high_risk_indicators[] = 'large_scale_user_data';
		}

		// Check for forms that collect data.
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php'     => 'Contact Form 7',
			'ninja-forms/ninja-forms.php'              => 'Ninja Forms',
			'wpforms-lite/wpforms.php'                 => 'WPForms',
			'formidable/formidable.php'                => 'Formidable Forms',
			'gravityforms/gravityforms.php'            => 'Gravity Forms',
		);

		$has_forms = false;
		foreach ( $form_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_forms              = true;
				$high_risk_indicators[] = 'data_collection_forms';
				break;
			}
		}

		// Check privacy policy for DPIA mention.
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		$has_dpia_mention = false;

		if ( $privacy_page_id ) {
			$content = strtolower( get_post_field( 'post_content', $privacy_page_id ) );

			$dpia_patterns = array( 'dpia', 'data protection impact', 'impact assessment', 'privacy impact' );
			foreach ( $dpia_patterns as $pattern ) {
				if ( stripos( $content, $pattern ) !== false ) {
					$has_dpia_mention = true;
					break;
				}
			}
		}

		// Check for DPIA-related documentation pages.
		$dpia_pages = get_posts(
			array(
				'post_type'      => 'page',
				's'              => 'impact assessment',
				'posts_per_page' => 3,
			)
		);

		$has_dpia_documentation = false;
		foreach ( $dpia_pages as $page ) {
			$page_content = strtolower( get_post_field( 'post_content', $page->ID ) );
			if ( stripos( $page_content, 'dpia' ) !== false ||
				stripos( $page_content, 'impact assessment' ) !== false ) {
				$has_dpia_documentation = true;
				break;
			}
		}

		// Check for automated decision-making.
		$has_automated_decisions = false;
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			// WooCommerce might have automated fraud detection, recommendations, etc.
			$has_automated_decisions = true;
			$high_risk_indicators[]  = 'automated_decisions';
		}

		// If high-risk indicators found but no DPIA documentation.
		if ( ! empty( $high_risk_indicators ) ) {
			if ( ! $has_dpia_mention && ! $has_dpia_documentation ) {
				$issues[] = 'high_risk_processing_without_dpia';
			}

			if ( ! empty( $active_profiling_plugins ) ) {
				$issues[] = 'profiling_without_dpia';
			}

			if ( $has_ecommerce && ! $has_dpia_mention ) {
				$issues[] = 'ecommerce_without_dpia';
			}

			if ( $total_users > 1000 && ! $has_dpia_mention ) {
				$issues[] = 'large_scale_processing_without_dpia';
			}
		}

		// Check for DPO mention (required for DPIA consultation).
		$has_dpo_mention = false;
		if ( $privacy_page_id ) {
			$content = strtolower( get_post_field( 'post_content', $privacy_page_id ) );
			if ( stripos( $content, 'data protection officer' ) !== false ||
				stripos( $content, 'dpo' ) !== false ) {
				$has_dpo_mention = true;
			}
		}

		if ( ! empty( $high_risk_indicators ) && ! $has_dpo_mention ) {
			$issues[] = 'no_dpo_for_dpia_consultation';
		}

		// If issues found, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'High-risk processing detected but DPIA not documented', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'details'      => array(
					'issues_found'           => $issues,
					'high_risk_indicators'   => $high_risk_indicators,
					'profiling_plugins'      => $active_profiling_plugins,
					'has_ecommerce'          => $has_ecommerce,
					'has_forms'              => $has_forms,
					'total_users'            => $total_users,
					'has_dpia_mention'       => $has_dpia_mention,
					'has_dpia_documentation' => $has_dpia_documentation,
					'has_dpo_mention'        => $has_dpo_mention,
				),
				'meta'         => array(
					'gdpr_article'       => 'Article 35',
					'triggers'           => 'Profiling, automated decisions, large-scale sensitive data, systematic monitoring',
					'consultation'       => 'Must consult with DPO',
					'update_required'    => 'When processing changes',
					'wpdb_avoidance'     => 'Uses get_option(), get_posts(), get_post_field(), is_plugin_active(), count_users() instead of $wpdb',
					'detection_method'   => 'WordPress APIs - plugin detection, user count, privacy policy analysis',
				),
				'kb_link'      => 'https://wpshadow.com/kb/gdpr-dpia-requirement',
				'solution'     => sprintf(
					/* translators: 1: Privacy policy URL */
					__( 'GDPR Article 35 requires Data Protection Impact Assessment (DPIA) for high-risk processing. Your site shows high-risk indicators: %1$s. Actions needed: 1) Conduct DPIA before continuing high-risk processing, 2) Document risks, mitigation measures, and necessity, 3) Consult with Data Protection Officer (DPO), 4) Update DPIA when processing changes, 5) Document DPIA results in privacy policy, 6) Consider privacy by design principles. High-risk processing includes: profiling/automated decisions, large-scale processing (1000+ users), sensitive data (health, biometric), systematic monitoring. Update privacy policy at %2$s. Resources: <a href="https://ico.org.uk/for-organisations/guide-to-data-protection/guide-to-the-general-data-protection-regulation-gdpr/accountability-and-governance/data-protection-impact-assessments/">ICO DPIA Guide</a> | <a href="https://gdpr.eu/article-35-impact-assessment/">GDPR Article 35</a>', 'wpshadow' ),
					implode( ', ', array_unique( $high_risk_indicators ) ),
					esc_url( admin_url( 'options-privacy.php' ) )
				),
			);
		}

		return null;
	}
}
