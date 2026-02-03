<?php
/**
 * Churn Prediction Model Diagnostic
 *
 * Tests whether the site uses data to predict and prevent member churn.
 *
 * @since   1.26034.0230
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Churn Prediction Model Diagnostic Class
 *
 * Predictive churn models allow proactive intervention, potentially reducing
 * churn by 20-30% through early identification of at-risk members.
 *
 * @since 1.26034.0230
 */
class Diagnostic_Churn_Prediction_Model extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'churn-prediction-model';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Churn Prediction Model';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses data to predict and prevent member churn';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'membership';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for membership sites.
		if ( ! self::is_membership_site() ) {
			return null;
		}

		$issues = array();
		$prediction_score = 0;
		$max_score = 7;

		// Check for analytics platform.
		$analytics = self::check_analytics_platform();
		if ( $analytics ) {
			$prediction_score++;
		} else {
			$issues[] = __( 'No advanced analytics platform for data collection', 'wpshadow' );
		}

		// Check for behavioral tracking.
		$behavioral_tracking = self::check_behavioral_tracking();
		if ( $behavioral_tracking ) {
			$prediction_score++;
		} else {
			$issues[] = __( 'No tracking of member behavior patterns', 'wpshadow' );
		}

		// Check for churn indicators.
		$churn_indicators = self::check_churn_indicators();
		if ( $churn_indicators ) {
			$prediction_score++;
		} else {
			$issues[] = __( 'No defined churn risk indicators or signals', 'wpshadow' );
		}

		// Check for risk scoring.
		$risk_scoring = self::check_risk_scoring();
		if ( $risk_scoring ) {
			$prediction_score++;
		} else {
			$issues[] = __( 'No churn risk scoring system', 'wpshadow' );
		}

		// Check for predictive tools.
		$predictive_tools = self::check_predictive_tools();
		if ( $predictive_tools ) {
			$prediction_score++;
		} else {
			$issues[] = __( 'No predictive analytics or machine learning tools', 'wpshadow' );
		}

		// Check for intervention workflows.
		$intervention = self::check_intervention_workflows();
		if ( $intervention ) {
			$prediction_score++;
		} else {
			$issues[] = __( 'No automated workflows to intervene with at-risk members', 'wpshadow' );
		}

		// Check for model performance tracking.
		$performance_tracking = self::check_performance_tracking();
		if ( $performance_tracking ) {
			$prediction_score++;
		} else {
			$issues[] = __( 'No tracking of churn prediction accuracy', 'wpshadow' );
		}

		// Determine severity based on churn prediction implementation.
		$prediction_percentage = ( $prediction_score / $max_score ) * 100;

		if ( $prediction_percentage < 30 ) {
			$severity = 'medium';
			$threat_level = 55;
		} elseif ( $prediction_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 35;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Churn prediction percentage */
				__( 'Churn prediction capabilities at %d%%. ', 'wpshadow' ),
				(int) $prediction_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Predictive models can reduce churn by 20-30%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/churn-prediction-model',
			);
		}

		return null;
	}

	/**
	 * Check if this is a membership site.
	 *
	 * @since  1.26034.0230
	 * @return bool True if membership features detected, false otherwise.
	 */
	private static function is_membership_site() {
		$membership_plugins = array(
			'paid-memberships-pro/paid-memberships-pro.php',
			'restrict-content-pro/restrict-content-pro.php',
			'memberpress/memberpress.php',
			'woocommerce-memberships/woocommerce-memberships.php',
		);

		foreach ( $membership_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for analytics platform.
	 *
	 * @since  1.26034.0230
	 * @return bool True if analytics platform exists, false otherwise.
	 */
	private static function check_analytics_platform() {
		$analytics_plugins = array(
			'google-site-kit/google-site-kit.php',
			'matomo/matomo.php',
			'jetpack/jetpack.php',
			'google-analytics-for-wordpress/googleanalytics.php',
		);

		foreach ( $analytics_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_analytics_platform', false );
	}

	/**
	 * Check for behavioral tracking.
	 *
	 * @since  1.26034.0230
	 * @return bool True if behavioral tracking exists, false otherwise.
	 */
	private static function check_behavioral_tracking() {
		// Check for activity tracking plugins.
		$tracking_plugins = array(
			'stream/stream.php',
			'simple-history/index.php',
			'aryo-activity-log/aryo-activity-log.php',
		);

		foreach ( $tracking_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for event tracking.
		if ( is_plugin_active( 'google-site-kit/google-site-kit.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_behavioral_tracking', false );
	}

	/**
	 * Check for churn indicators.
	 *
	 * @since  1.26034.0230
	 * @return bool True if indicators exist, false otherwise.
	 */
	private static function check_churn_indicators() {
		// Check for content about churn signals.
		$keywords = array( 'churn indicators', 'at-risk members', 'churn signals', 'risk factors' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'any',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_churn_indicators', false );
	}

	/**
	 * Check for risk scoring.
	 *
	 * @since  1.26034.0230
	 * @return bool True if risk scoring exists, false otherwise.
	 */
	private static function check_risk_scoring() {
		// Check for gamification with scoring.
		$scoring_plugins = array(
			'gamipress/gamipress.php',
			'mycred/mycred.php',
		);

		foreach ( $scoring_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for risk scoring content.
		$query = new \WP_Query(
			array(
				's'              => 'risk score churn risk health score',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for predictive tools.
	 *
	 * @since  1.26034.0230
	 * @return bool True if predictive tools exist, false otherwise.
	 */
	private static function check_predictive_tools() {
		// Check for ML/AI plugins.
		$ml_plugins = array(
			'ai-engine/ai-engine.php',
			'jetpack/jetpack.php', // Has predictive features.
		);

		foreach ( $ml_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for predictive analytics content.
		$query = new \WP_Query(
			array(
				's'              => 'predictive analytics machine learning forecast',
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for intervention workflows.
	 *
	 * @since  1.26034.0230
	 * @return bool True if workflows exist, false otherwise.
	 */
	private static function check_intervention_workflows() {
		// Check for automation plugins.
		$automation_plugins = array(
			'mailpoet/mailpoet.php',
			'automated-emails/automated-emails.php',
			'fluentcrm/fluentcrm.php',
		);

		foreach ( $automation_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for intervention-related content.
		$query = new \WP_Query(
			array(
				's'              => 'intervention workflow automated outreach',
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for performance tracking.
	 *
	 * @since  1.26034.0230
	 * @return bool True if tracking exists, false otherwise.
	 */
	private static function check_performance_tracking() {
		// Check for analytics platforms.
		if ( is_plugin_active( 'google-site-kit/google-site-kit.php' ) ||
			 is_plugin_active( 'matomo/matomo.php' ) ) {
			return true;
		}

		// WooCommerce has reporting.
		if ( class_exists( 'WooCommerce' ) ) {
			return true;
		}

		// Check for model performance content.
		$query = new \WP_Query(
			array(
				's'              => 'model accuracy prediction performance metrics',
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}
}
