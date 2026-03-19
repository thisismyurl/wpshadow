<?php
/**
 * Customer Success & Onboarding Program Diagnostic
 *
 * Checks if new customers receive onboarding to reduce refunds and churn.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Success & Onboarding Program Diagnostic Class
 *
 * Good onboarding reduces churn by 50%. Helps customers succeed early
 * so they stay and buy more.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Customer_Success_Onboarding extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'customer-success-onboarding';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Success & Onboarding Program';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customers receive onboarding to reduce refunds and early churn';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'retention-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if ecommerce is active.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );
		$has_edd         = is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' );

		if ( ! $has_woocommerce && ! $has_edd ) {
			return null; // Not applicable for non-ecommerce sites.
		}

		$issues           = array();
		$onboarding_score = 0;
		$max_score        = 5;

		// Check for onboarding email sequence.
		$has_sequence = self::check_onboarding_sequence();
		if ( $has_sequence ) {
			$onboarding_score++;
		} else {
			$issues[] = 'onboarding email sequence';
		}

		// Check for welcome guide or checklist.
		$has_guide = self::check_welcome_guide();
		if ( $has_guide ) {
			$onboarding_score++;
		} else {
			$issues[] = 'welcome guide or checklist';
		}

		// Check for success milestones.
		$has_milestones = self::check_success_milestones();
		if ( $has_milestones ) {
			$onboarding_score++;
		} else {
			$issues[] = 'success milestone tracking';
		}

		// Check for proactive support outreach.
		$has_support = self::check_proactive_support();
		if ( $has_support ) {
			$onboarding_score++;
		} else {
			$issues[] = 'proactive support outreach';
		}

		// Check for at-risk customer monitoring.
		$has_monitoring = self::check_at_risk_monitoring();
		if ( $has_monitoring ) {
			$onboarding_score++;
		} else {
			$issues[] = 'at-risk customer monitoring';
		}

		$completion_percentage = ( $onboarding_score / $max_score ) * 100;

		if ( $completion_percentage >= 80 ) {
			return null; // Good onboarding program in place.
		}

		$severity     = $completion_percentage < 40 ? 'high' : 'medium';
		$threat_level = $completion_percentage < 40 ? 70 : 50;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Customer onboarding at %1$d%%. Missing: %2$s. Good onboarding reduces churn by 50%% and helps customers extract value early.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/customer-success-onboarding',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check if onboarding email sequence exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if sequence exists.
	 */
	private static function check_onboarding_sequence(): bool {
		// Check for scheduled onboarding emails.
		$cron_hooks = array(
			'wpshadow_customer_onboarding',
			'woocommerce_onboarding_email',
			'edd_customer_onboarding',
		);

		foreach ( $cron_hooks as $hook ) {
			if ( wp_next_scheduled( $hook ) ) {
				return true;
			}
		}

		// Check for onboarding automation settings.
		$automations = get_option( 'wpshadow_email_automations', array() );
		if ( is_array( $automations ) ) {
			foreach ( $automations as $automation ) {
				$trigger = $automation['trigger'] ?? '';
				if ( false !== strpos( $trigger, 'onboarding' ) || false !== strpos( $trigger, 'first_purchase' ) ) {
					return true;
				}
			}
		}

		// Check for email sequence plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$email_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/plugin.php',
		);

		foreach ( $email_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				// Check if onboarding sequences exist.
				if ( 'mailpoet/mailpoet.php' === $plugin ) {
					global $wpdb;
					$sequences = $wpdb->get_var(
						"SELECT COUNT(*) FROM {$wpdb->prefix}mailpoet_newsletters 
						WHERE subject LIKE '%welcome%' OR subject LIKE '%onboarding%' 
						LIMIT 1"
					);
					if ( $sequences > 0 ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check if welcome guide or checklist exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if guide exists.
	 */
	private static function check_welcome_guide(): bool {
		// Check for welcome page.
		$args = array(
			'post_type'      => 'page',
			'posts_per_page' => 1,
			's'              => 'welcome guide getting started',
			'post_status'    => 'publish',
		);

		$welcome_pages = get_posts( $args );
		if ( ! empty( $welcome_pages ) ) {
			return true;
		}

		// Check for guide option.
		$welcome_guide = get_option( 'wpshadow_welcome_guide_enabled', false );
		if ( $welcome_guide ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if success milestones are defined.
	 *
	 * @since 1.6093.1200
	 * @return bool True if milestones exist.
	 */
	private static function check_success_milestones(): bool {
		// Check for milestone tracking.
		$milestones = get_option( 'wpshadow_customer_success_milestones', array() );
		if ( ! empty( $milestones ) ) {
			return true;
		}

		// Check for achievement/progress tracking.
		$progress_tracking = get_option( 'wpshadow_customer_progress_tracking', false );
		if ( $progress_tracking ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if proactive support outreach is configured.
	 *
	 * @since 1.6093.1200
	 * @return bool True if outreach exists.
	 */
	private static function check_proactive_support(): bool {
		// Check for support check-in emails.
		$cron_hooks = array(
			'wpshadow_customer_checkin',
			'wpshadow_support_outreach',
		);

		foreach ( $cron_hooks as $hook ) {
			if ( wp_next_scheduled( $hook ) ) {
				return true;
			}
		}

		// Check for proactive support settings.
		$proactive_support = get_option( 'wpshadow_proactive_support_enabled', false );
		if ( $proactive_support ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if at-risk customer monitoring exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if monitoring exists.
	 */
	private static function check_at_risk_monitoring(): bool {
		// Check for usage monitoring.
		$usage_monitoring = get_option( 'wpshadow_usage_monitoring_enabled', false );
		if ( $usage_monitoring ) {
			return true;
		}

		// Check for at-risk detection.
		$at_risk_detection = get_option( 'wpshadow_at_risk_detection_enabled', false );
		if ( $at_risk_detection ) {
			return true;
		}

		// Check for engagement tracking.
		$engagement_tracking = get_option( 'wpshadow_engagement_tracking', false );
		if ( $engagement_tracking ) {
			return true;
		}

		return false;
	}
}
