<?php
/**
 * No Mobile App Strategy Diagnostic
 *
 * Checks if mobile app strategy or presence exists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile App Strategy Diagnostic
 *
 * Mobile app users engage 3x more than web users and have 2x higher
 * customer lifetime value. Apps create sticky engagement.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Mobile_App_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-mobile-app-strategy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile App Strategy or Presence';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile app strategy or presence exists';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_mobile_app_strategy() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No mobile app strategy detected. Mobile app users engage 3x more than web users and have 2x higher lifetime value. Apps enable: push notifications (10x open rate vs email), offline access, device features (camera, GPS), faster performance, habit formation (icon on home screen). Decide: 1) Native apps (iOS/Android - best UX, costly), 2) Progressive Web App (PWA - cheaper, nearly native), 3) Hybrid (React Native/Flutter - middle ground). Not all businesses need apps, but evaluate opportunity cost.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/mobile-app-strategy',
				'details'     => array(
					'issue'               => __( 'No mobile app strategy or presence detected', 'wpshadow' ),
					'recommendation'      => __( 'Evaluate mobile app opportunity and decide on implementation approach', 'wpshadow' ),
					'business_impact'     => __( 'Potentially missing 3x engagement and 2x lifetime value from app users', 'wpshadow' ),
					'app_approaches'      => self::get_app_approaches(),
					'decision_criteria'   => self::get_decision_criteria(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if mobile app strategy exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if strategy detected, false otherwise.
	 */
	private static function has_mobile_app_strategy() {
		// Check for mobile app content
		$app_posts = self::count_posts_by_keywords(
			array(
				'mobile app',
				'ios app',
				'android app',
				'download our app',
				'app store',
				'google play',
				'progressive web app',
				'pwa',
			)
		);

		if ( $app_posts > 0 ) {
			return true;
		}

		// Check for PWA plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$app_keywords = array(
			'progressive web',
			'pwa',
			'mobile app',
			'app builder',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $app_keywords as $keyword ) {
				if ( false !== strpos( $plugin_name, $keyword ) ) {
					if ( is_plugin_active( $plugin_file ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since  1.6035.0000
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get mobile app approaches.
	 *
	 * @since  1.6035.0000
	 * @return array App approaches with pros/cons.
	 */
	private static function get_app_approaches() {
		return array(
			'native_ios_android' => array(
				'description' => __( 'Separate native apps for iOS and Android', 'wpshadow' ),
				'pros'        => __( 'Best UX, full device access, best performance', 'wpshadow' ),
				'cons'        => __( 'Expensive ($50k-200k+), maintain two codebases', 'wpshadow' ),
				'best_for'    => __( 'High-value apps with complex features', 'wpshadow' ),
			),
			'pwa'                => array(
				'description' => __( 'Progressive Web App (web tech, app-like)', 'wpshadow' ),
				'pros'        => __( 'Low cost, one codebase, no app store approval', 'wpshadow' ),
				'cons'        => __( 'Limited device features, iOS restrictions', 'wpshadow' ),
				'best_for'    => __( 'Content-focused apps, budget constraints', 'wpshadow' ),
			),
			'hybrid'             => array(
				'description' => __( 'React Native or Flutter (cross-platform)', 'wpshadow' ),
				'pros'        => __( 'One codebase, near-native UX, moderate cost', 'wpshadow' ),
				'cons'        => __( 'Slightly slower than native, learning curve', 'wpshadow' ),
				'best_for'    => __( 'Most businesses (sweet spot)', 'wpshadow' ),
			),
			'no_code'            => array(
				'description' => __( 'No-code app builders (Adalo, Glide, Bubble)', 'wpshadow' ),
				'pros'        => __( 'Very low cost, fast to build, easy changes', 'wpshadow' ),
				'cons'        => __( 'Limited customization, platform lock-in', 'wpshadow' ),
				'best_for'    => __( 'MVPs, simple apps, rapid prototyping', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get decision criteria for mobile apps.
	 *
	 * @since  1.6035.0000
	 * @return array Decision criteria questions.
	 */
	private static function get_decision_criteria() {
		return array(
			'frequency'     => __( 'Do users need your product daily/multiple times per day?', 'wpshadow' ),
			'offline'       => __( 'Do users need offline access or sync?', 'wpshadow' ),
			'notifications' => __( 'Would push notifications significantly increase engagement?', 'wpshadow' ),
			'device_access' => __( 'Do you need camera, GPS, or other device features?', 'wpshadow' ),
			'performance'   => __( 'Is web performance limiting the user experience?', 'wpshadow' ),
			'stickiness'    => __( 'Would home screen icon improve habit formation?', 'wpshadow' ),
			'competitors'   => __( 'Do competitors have apps that create advantage?', 'wpshadow' ),
			'roi'           => __( 'Does 2x LTV from app users justify development cost?', 'wpshadow' ),
		);
	}
}
