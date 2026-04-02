<?php
/**
 * Machine Learning Personalization Diagnostic
 *
 * Tests whether the site uses machine learning for personalized user experiences.
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
 * Machine Learning Personalization Diagnostic Class
 *
 * ML-powered personalization adapts content, products, and experiences to individual
 * users based on behavior patterns, improving engagement and conversions.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Machine_Learning_Personalization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'machine-learning-personalization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Machine Learning Personalization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses machine learning for personalized user experiences';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'personalization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$ml_score = 0;
		$max_score = 7;

		// Check for personalization plugins.
		$personalization_plugins = array(
			'if-so/if-so.php' => 'If-So Dynamic Content',
			'персонализация/personalization.php' => 'Personalization',
			'dynamic-content-for-elementor/dynamic-content-for-elementor.php' => 'Dynamic Content',
		);

		$has_personalization = false;
		foreach ( $personalization_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_personalization = true;
				$ml_score++;
				break;
			}
		}

		if ( ! $has_personalization ) {
			$issues[] = __( 'No personalization plugin detected', 'wpshadow' );
		}

		// Check for recommendation engines.
		$recommendation_engines = self::check_recommendation_engine();
		if ( $recommendation_engines ) {
			$ml_score++;
		} else {
			$issues[] = __( 'No product/content recommendation engine', 'wpshadow' );
		}

		// Check for user segmentation.
		$user_segmentation = self::check_user_segmentation();
		if ( $user_segmentation ) {
			$ml_score++;
		} else {
			$issues[] = __( 'No user segmentation or behavior-based grouping', 'wpshadow' );
		}

		// Check for dynamic content delivery.
		$dynamic_content = self::check_dynamic_content();
		if ( $dynamic_content ) {
			$ml_score++;
		} else {
			$issues[] = __( 'No dynamic content adaptation based on user behavior', 'wpshadow' );
		}

		// Check for personalized email campaigns.
		$personalized_email = self::check_personalized_email();
		if ( $personalized_email ) {
			$ml_score++;
		} else {
			$issues[] = __( 'Email campaigns not personalized based on behavior', 'wpshadow' );
		}

		// Check for predictive search.
		$predictive_search = self::check_predictive_search();
		if ( $predictive_search ) {
			$ml_score++;
		} else {
			$issues[] = __( 'No ML-powered predictive search functionality', 'wpshadow' );
		}

		// Check for behavioral targeting.
		$behavioral_targeting = self::check_behavioral_targeting();
		if ( $behavioral_targeting ) {
			$ml_score++;
		} else {
			$issues[] = __( 'No behavioral targeting for offers or CTAs', 'wpshadow' );
		}

		// Determine severity based on ML personalization implementation.
		$ml_percentage = ( $ml_score / $max_score ) * 100;

		if ( $ml_percentage < 30 ) {
			// Minimal or no ML personalization.
			$severity = 'low';
			$threat_level = 30;
		} elseif ( $ml_percentage < 60 ) {
			// Basic ML personalization.
			$severity = 'low';
			$threat_level = 20;
		} else {
			// Good ML personalization - no issue.
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: ML personalization percentage */
				__( 'ML personalization at %d%%. ', 'wpshadow' ),
				(int) $ml_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Personalization can increase conversions by 20-40%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/machine-learning-personalization',
			);
		}

		return null;
	}

	/**
	 * Check for recommendation engine.
	 *
	 * @since 1.6093.1200
	 * @return bool True if recommendation engine exists, false otherwise.
	 */
	private static function check_recommendation_engine() {
		// Check for WooCommerce recommendation plugins.
		$recommendation_plugins = array(
			'woocommerce-recommendation-engine/woocommerce-recommendation-engine.php',
			'beeketing-for-woocommerce/beeketing-for-woocommerce.php',
			'related-products-for-woocommerce/related-products-for-woocommerce.php',
		);

		foreach ( $recommendation_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for content recommendation plugins.
		$content_recommendation = array(
			'contextly-related-links/contextly-linker.php',
			'zemanta/zemanta.php',
		);

		foreach ( $content_recommendation as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_recommendation_engine', false );
	}

	/**
	 * Check for user segmentation.
	 *
	 * @since 1.6093.1200
	 * @return bool True if user segmentation exists, false otherwise.
	 */
	private static function check_user_segmentation() {
		// Check for segmentation in marketing automation.
		$segmentation_plugins = array(
			'mailchimp-for-woocommerce/mailchimp-woocommerce.php',
			'convertkit/convertkit.php',
			'activecampaign/activecampaign.php',
		);

		foreach ( $segmentation_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_user_segmentation', false );
	}

	/**
	 * Check for dynamic content delivery.
	 *
	 * @since 1.6093.1200
	 * @return bool True if dynamic content exists, false otherwise.
	 */
	private static function check_dynamic_content() {
		// Check for dynamic content plugins.
		$dynamic_plugins = array(
			'if-so/if-so.php',
			'dynamic-content-for-elementor/dynamic-content-for-elementor.php',
			'dynamic-widgets/dynamic-widgets.php',
		);

		foreach ( $dynamic_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_dynamic_content', false );
	}

	/**
	 * Check for personalized email campaigns.
	 *
	 * @since 1.6093.1200
	 * @return bool True if personalized email exists, false otherwise.
	 */
	private static function check_personalized_email() {
		// Check for advanced email marketing platforms.
		$email_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'newsletter/newsletter.php',
			'mailpoet/mailpoet.php',
		);

		foreach ( $email_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_personalized_email', false );
	}

	/**
	 * Check for predictive search.
	 *
	 * @since 1.6093.1200
	 * @return bool True if predictive search exists, false otherwise.
	 */
	private static function check_predictive_search() {
		// Check for advanced search plugins.
		$search_plugins = array(
			'relevanssi/relevanssi.php',
			'ajax-search-lite/ajax-search-lite.php',
			'searchwp/searchwp.php',
			'elasticpress/elasticpress.php',
		);

		foreach ( $search_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_predictive_search', false );
	}

	/**
	 * Check for behavioral targeting.
	 *
	 * @since 1.6093.1200
	 * @return bool True if behavioral targeting exists, false otherwise.
	 */
	private static function check_behavioral_targeting() {
		// Check for targeting/personalization plugins.
		$targeting_plugins = array(
			'if-so/if-so.php',
			'optinmonster/optin-monster-wp-api.php',
			'thrive-leads/thrive-leads.php',
		);

		foreach ( $targeting_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_behavioral_targeting', false );
	}
}
