<?php
/**
 * Conversion Rate Optimization Diagnostic
 *
 * Tests for active CRO program and continuous improvements
 * to conversion funnels and user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1510
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Conversion Rate Optimization Diagnostic Class
 *
 * Evaluates whether the site has an active CRO program
 * with testing, tracking, and optimization infrastructure.
 *
 * @since 1.6035.1510
 */
class Diagnostic_Conversion_Rate_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'optimizes-conversion-rates';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Conversion Rate Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for active CRO program and improvements';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the conversion rate optimization diagnostic check.
	 *
	 * @since  1.6035.1510
	 * @return array|null Finding array if CRO issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for conversion tracking infrastructure.
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		$has_google_analytics = false;
		$has_google_tag_manager = false;
		$has_facebook_pixel = false;
		$has_google_ads_tracking = false;

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			// Google Analytics.
			if ( preg_match( '/gtag\(|ga\(|google-analytics\.com\/analytics\.js/i', $html ) ) {
				$has_google_analytics = true;
			}

			// Google Tag Manager.
			if ( preg_match( '/googletagmanager\.com\/gtm\.js|GTM-[A-Z0-9]+/i', $html ) ) {
				$has_google_tag_manager = true;
			}

			// Facebook Pixel.
			if ( preg_match( '/fbq\(|facebook\.com\/tr/i', $html ) ) {
				$has_facebook_pixel = true;
			}

			// Google Ads conversion tracking.
			if ( preg_match( '/googleadservices\.com|google\.com\/ads/i', $html ) ) {
				$has_google_ads_tracking = true;
			}
		}

		$stats['has_google_analytics'] = $has_google_analytics;
		$stats['has_google_tag_manager'] = $has_google_tag_manager;
		$stats['has_facebook_pixel'] = $has_facebook_pixel;
		$stats['has_google_ads_tracking'] = $has_google_ads_tracking;

		// Check for A/B testing tools.
		$has_ab_testing = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			$ab_testing_services = array( 'optimize\.google\.com', 'optimizely\.com', 'visualwebsiteoptimizer\.com', 'abtasty\.com' );
			foreach ( $ab_testing_services as $service ) {
				if ( preg_match( '/' . $service . '/i', $html ) ) {
					$has_ab_testing = true;
					break;
				}
			}
		}

		$stats['has_ab_testing'] = $has_ab_testing;

		// Check for heatmap/behavior tracking.
		$has_heatmap = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			$heatmap_services = array( 'hotjar\.com', 'crazyegg\.com', 'mouseflow\.com', 'luckyorange\.com', 'inspectlet\.com' );
			foreach ( $heatmap_services as $service ) {
				if ( preg_match( '/' . $service . '/i', $html ) ) {
					$has_heatmap = true;
					break;
				}
			}
		}

		$stats['has_heatmap'] = $has_heatmap;

		// Check for form plugins (conversion points).
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php'         => 'Contact Form 7',
			'wpforms-lite/wpforms.php'                     => 'WPForms',
			'ninja-forms/ninja-forms.php'                  => 'Ninja Forms',
			'gravityforms/gravityforms.php'                => 'Gravity Forms',
		);

		$active_form_plugins = array();
		foreach ( $form_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_form_plugins[] = $name;
			}
		}

		$stats['active_form_plugins'] = $active_form_plugins;
		$has_forms = ! empty( $active_form_plugins );

		// Check for CTA (Call-to-Action) plugins.
		$cta_plugins = array(
			'wordpress-calls-to-action/cta.php',
		);

		$has_cta_plugin = false;
		foreach ( $cta_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cta_plugin = true;
				break;
			}
		}

		$stats['has_cta_plugin'] = $has_cta_plugin;

		// Check for live chat (improves conversions).
		$chat_plugins = array(
			'wp-live-chat-support/wp-live-chat-support.php',
			'livechat-wc/livechat-wc.php',
		);

		$has_live_chat = false;
		foreach ( $chat_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_live_chat = true;
				break;
			}
		}

		// Also check for external chat services.
		if ( ! $has_live_chat && ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			$chat_services = array( 'intercom\.io', 'drift\.com', 'tawk\.to', 'crisp\.chat', 'zendesk\.com' );
			foreach ( $chat_services as $service ) {
				if ( preg_match( '/' . $service . '/i', $html ) ) {
					$has_live_chat = true;
					break;
				}
			}
		}

		$stats['has_live_chat'] = $has_live_chat;

		// Check for popup/exit-intent plugins.
		$popup_plugins = array(
			'popup-maker/popup-maker.php',
			'elementor/elementor.php', // Has popup capability.
		);

		$has_popup = false;
		foreach ( $popup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_popup = true;
				break;
			}
		}

		$stats['has_popup'] = $has_popup;

		// Check for social proof plugins.
		$social_proof_plugins = array(
			'wp-social-proof/wp-social-proof.php',
		);

		$has_social_proof = false;
		foreach ( $social_proof_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_social_proof = true;
				break;
			}
		}

		$stats['has_social_proof'] = $has_social_proof;

		// Check for testimonial/review plugins.
		$testimonial_plugins = array(
			'strong-testimonials/strong-testimonials.php',
		);

		$has_testimonials = false;
		foreach ( $testimonial_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_testimonials = true;
				break;
			}
		}

		// Also check for testimonial content.
		if ( ! $has_testimonials ) {
			$testimonial_posts = get_posts( array(
				'post_type'   => 'any',
				'post_status' => 'publish',
				'numberposts' => 1,
				's'           => 'testimonial review',
			) );
			$has_testimonials = ! empty( $testimonial_posts );
		}

		$stats['has_testimonials'] = $has_testimonials;

		// Check for performance optimization (affects conversions).
		$performance_plugins = array(
			'wp-rocket/wp-rocket.php',
			'w3-total-cache/w3-total-cache.php',
			'autoptimize/autoptimize.php',
		);

		$has_performance_optimization = false;
		foreach ( $performance_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_performance_optimization = true;
				break;
			}
		}

		$stats['has_performance_optimization'] = $has_performance_optimization;

		// Check for mobile optimization (critical for conversions).
		$has_mobile_optimization = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			if ( preg_match( '/<meta[^>]*name=["\']viewport["\'][^>]*>/i', $html ) ) {
				$has_mobile_optimization = true;
			}
		}

		$stats['has_mobile_optimization'] = $has_mobile_optimization;

		// Calculate CRO infrastructure score.
		$cro_features = 0;
		$total_features = 12;

		if ( $has_google_analytics ) { $cro_features++; }
		if ( $has_google_tag_manager ) { $cro_features++; }
		if ( $has_ab_testing ) { $cro_features++; }
		if ( $has_heatmap ) { $cro_features++; }
		if ( $has_forms ) { $cro_features++; }
		if ( $has_live_chat ) { $cro_features++; }
		if ( $has_popup ) { $cro_features++; }
		if ( $has_testimonials ) { $cro_features++; }
		if ( $has_performance_optimization ) { $cro_features++; }
		if ( $has_mobile_optimization ) { $cro_features++; }
		if ( $has_facebook_pixel || $has_google_ads_tracking ) { $cro_features++; }
		if ( $has_social_proof ) { $cro_features++; }

		$stats['cro_infrastructure_score'] = round( ( $cro_features / $total_features ) * 100, 1 );

		// Evaluate issues.
		if ( ! $has_google_analytics ) {
			$issues[] = __( 'Google Analytics not detected - critical for tracking conversions', 'wpshadow' );
		}

		if ( ! $has_ab_testing ) {
			$warnings[] = __( 'No A/B testing infrastructure - implement Google Optimize or similar', 'wpshadow' );
		}

		if ( ! $has_heatmap ) {
			$warnings[] = __( 'No heatmap/behavior tracking - understand user behavior to improve conversions', 'wpshadow' );
		}

		if ( ! $has_forms ) {
			$warnings[] = __( 'No form plugin detected - forms are key conversion points', 'wpshadow' );
		}

		if ( ! $has_live_chat ) {
			$warnings[] = __( 'No live chat - can significantly improve conversion rates', 'wpshadow' );
		}

		if ( ! $has_popup ) {
			$warnings[] = __( 'No popup/exit-intent - consider strategic popups for lead capture', 'wpshadow' );
		}

		if ( ! $has_testimonials ) {
			$warnings[] = __( 'No testimonials/reviews - social proof boosts conversions', 'wpshadow' );
		}

		if ( ! $has_performance_optimization ) {
			$warnings[] = __( 'No performance optimization - slow sites have lower conversion rates', 'wpshadow' );
		}

		if ( ! $has_mobile_optimization ) {
			$issues[] = __( 'No mobile optimization - mobile traffic often has lower conversion rates', 'wpshadow' );
		}

		if ( ! $has_google_tag_manager ) {
			$warnings[] = __( 'No Google Tag Manager - simplifies conversion tracking implementation', 'wpshadow' );
		}

		if ( $stats['cro_infrastructure_score'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: score percentage */
				__( 'CRO infrastructure score is low (%s%%) - build out conversion optimization tools', 'wpshadow' ),
				$stats['cro_infrastructure_score']
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Conversion rate optimization has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/conversion-optimization',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Conversion rate optimization has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/conversion-optimization',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // CRO program is active.
	}
}
