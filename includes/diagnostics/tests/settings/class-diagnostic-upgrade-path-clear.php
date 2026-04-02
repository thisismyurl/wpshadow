<?php
/**
 * Upgrade Path Clear Diagnostic
 *
 * Tests whether membership upgrade options are clearly presented.
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
 * Upgrade Path Clear Diagnostic Class
 *
 * Clear upgrade paths can increase average revenue per user (ARPU) by 15-25%.
 * Members need to understand their upgrade options and the value they receive.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Upgrade_Path_Clear extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upgrade-path-clear';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upgrade Path Clear';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether membership upgrade options are clearly presented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'membership';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for sites with multiple membership levels.
		if ( ! self::has_multiple_levels() ) {
			return null;
		}

		$issues = array();
		$upgrade_score = 0;
		$max_score = 7;

		// Check for upgrade page.
		$upgrade_page = self::check_upgrade_page();
		if ( $upgrade_page ) {
			$upgrade_score++;
		} else {
			$issues[] = __( 'No dedicated upgrade or pricing page', 'wpshadow' );
		}

		// Check for comparison table.
		$comparison_table = self::check_comparison_table();
		if ( $comparison_table ) {
			$upgrade_score++;
		} else {
			$issues[] = __( 'No plan comparison table showing tier differences', 'wpshadow' );
		}

		// Check for upgrade CTAs in member area.
		$upgrade_ctas = self::check_upgrade_ctas();
		if ( $upgrade_ctas ) {
			$upgrade_score++;
		} else {
			$issues[] = __( 'No upgrade prompts in member dashboard', 'wpshadow' );
		}

		// Check for value messaging.
		$value_messaging = self::check_value_messaging();
		if ( $value_messaging ) {
			$upgrade_score++;
		} else {
			$issues[] = __( 'Limited messaging about premium tier benefits', 'wpshadow' );
		}

		// Check for feature gating/teasing.
		$feature_gating = self::check_feature_gating();
		if ( $feature_gating ) {
			$upgrade_score++;
		} else {
			$issues[] = __( 'No feature previews showing higher tier benefits', 'wpshadow' );
		}

		// Check for upgrade incentives.
		$incentives = self::check_upgrade_incentives();
		if ( $incentives ) {
			$upgrade_score++;
		} else {
			$issues[] = __( 'No special incentives or bonuses for upgrading', 'wpshadow' );
		}

		// Check for seamless upgrade process.
		$easy_upgrade = self::check_upgrade_process();
		if ( $easy_upgrade ) {
			$upgrade_score++;
		} else {
			$issues[] = __( 'Upgrade process not clearly documented or accessible', 'wpshadow' );
		}

		// Determine severity based on upgrade path clarity.
		$upgrade_percentage = ( $upgrade_score / $max_score ) * 100;

		if ( $upgrade_percentage < 40 ) {
			$severity = 'medium';
			$threat_level = 55;
		} elseif ( $upgrade_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 35;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Upgrade path clarity percentage */
				__( 'Upgrade path clarity at %d%%. ', 'wpshadow' ),
				(int) $upgrade_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Clear upgrade paths increase ARPU by 15-25%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/upgrade-path-clear',
			);
		}

		return null;
	}

	/**
	 * Check if site has multiple membership levels.
	 *
	 * @since 1.6093.1200
	 * @return bool True if multiple levels detected, false otherwise.
	 */
	private static function has_multiple_levels() {
		// Check PMPro levels.
		if ( function_exists( 'pmpro_getAllLevels' ) ) {
			$levels = pmpro_getAllLevels();
			if ( is_array( $levels ) && count( $levels ) > 1 ) {
				return true;
			}
		}

		// Check MemberPress memberships.
		if ( class_exists( 'MeprProduct' ) ) {
			$products = get_posts(
				array(
					'post_type'      => 'memberpressproduct',
					'posts_per_page' => 2,
					'post_status'    => 'publish',
				)
			);
			if ( count( $products ) > 1 ) {
				return true;
			}
		}

		// Check RCP levels.
		if ( function_exists( 'rcp_get_subscription_levels' ) ) {
			$levels = rcp_get_subscription_levels();
			if ( is_array( $levels ) && count( $levels ) > 1 ) {
				return true;
			}
		}

		// Check WooCommerce Memberships.
		if ( is_plugin_active( 'woocommerce-memberships/woocommerce-memberships.php' ) ) {
			$plans = get_posts(
				array(
					'post_type'      => 'wc_membership_plan',
					'posts_per_page' => 2,
					'post_status'    => 'publish',
				)
			);
			if ( count( $plans ) > 1 ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_multiple_membership_levels', false );
	}

	/**
	 * Check for upgrade page.
	 *
	 * @since 1.6093.1200
	 * @return bool True if upgrade page exists, false otherwise.
	 */
	private static function check_upgrade_page() {
		$keywords = array( 'upgrade', 'pricing', 'plans', 'membership levels' );

		foreach ( $keywords as $keyword ) {
			$pages = get_posts(
				array(
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					's'              => $keyword,
				)
			);

			if ( ! empty( $pages ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_upgrade_page', false );
	}

	/**
	 * Check for comparison table.
	 *
	 * @since 1.6093.1200
	 * @return bool True if comparison table exists, false otherwise.
	 */
	private static function check_comparison_table() {
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			)
		);

		foreach ( $pages as $page ) {
			$content = $page->post_content;
			// Check for table with pricing/comparison keywords.
			if ( strpos( $content, '<table' ) !== false &&
				( strpos( strtolower( $content ), 'basic' ) !== false ||
				  strpos( strtolower( $content ), 'premium' ) !== false ||
				  strpos( strtolower( $content ), 'pro' ) !== false ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_comparison_table', false );
	}

	/**
	 * Check for upgrade CTAs.
	 *
	 * @since 1.6093.1200
	 * @return bool True if upgrade CTAs exist, false otherwise.
	 */
	private static function check_upgrade_ctas() {
		$query = new \WP_Query(
			array(
				's'              => 'upgrade premium unlock more',
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		if ( $query->have_posts() ) {
			return true;
		}

		// Check if membership plugins have upgrade settings.
		if ( is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' ) ||
			 is_plugin_active( 'memberpress/memberpress.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_upgrade_ctas', false );
	}

	/**
	 * Check for value messaging.
	 *
	 * @since 1.6093.1200
	 * @return bool True if value messaging exists, false otherwise.
	 */
	private static function check_value_messaging() {
		$keywords = array( 'premium benefits', 'exclusive access', 'advanced features', 'get more' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_value_messaging', false );
	}

	/**
	 * Check for feature gating.
	 *
	 * @since 1.6093.1200
	 * @return bool True if feature gating exists, false otherwise.
	 */
	private static function check_feature_gating() {
		// Check for "upgrade to access" messaging.
		$query = new \WP_Query(
			array(
				's'              => 'upgrade to access unlock premium only',
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for upgrade incentives.
	 *
	 * @since 1.6093.1200
	 * @return bool True if incentives exist, false otherwise.
	 */
	private static function check_upgrade_incentives() {
		$keywords = array( 'upgrade bonus', 'limited time', 'special offer', 'discount' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		// Check for coupon/discount functionality.
		if ( class_exists( 'WooCommerce' ) ) {
			$coupons = get_posts(
				array(
					'post_type'      => 'shop_coupon',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( ! empty( $coupons ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_upgrade_incentives', false );
	}

	/**
	 * Check for upgrade process.
	 *
	 * @since 1.6093.1200
	 * @return bool True if process is documented, false otherwise.
	 */
	private static function check_upgrade_process() {
		// Most membership plugins have built-in upgrade processes.
		if ( is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' ) ||
			 is_plugin_active( 'memberpress/memberpress.php' ) ||
			 is_plugin_active( 'restrict-content-pro/restrict-content-pro.php' ) ) {
			return true;
		}

		// Check for upgrade documentation.
		$query = new \WP_Query(
			array(
				's'              => 'how to upgrade change plan',
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}
}
