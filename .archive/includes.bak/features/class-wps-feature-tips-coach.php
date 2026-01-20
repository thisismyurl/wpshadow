<?php declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Tips_Coach extends WPSHADOW_Abstract_Feature {

	private const TYPE_BLOG        = 'blog';
	private const TYPE_WOOCOMMERCE = 'woocommerce';
	private const TYPE_LMS         = 'lms';
	private const TYPE_GENERIC     = 'generic';

	public function __construct() {
		parent::__construct( array(
			'id'          => 'tips-coach',
			'name'        => __( 'Smart Tips Helper', 'wpshadow' ),
			'description' => __( 'Get helpful suggestions customized for your type of website (blog, online store, or course site), plus troubleshooting and video walkthroughs.', 'wpshadow' ),
			'aliases'     => array( 'tips', 'suggestions', 'best practices', 'recommendations', 'coach', 'help', 'guidance', 'next steps', 'optimization tips', 'site improvement', 'dashboard tips', 'contextual help', 'troubleshooting', 'problem solver', 'video walkthrough', 'tutorials', 'step-by-step' ),
			'sub_features' => array(
				'enable_tips'        => array(
					'name'               => __( 'Enable Tips Display', 'wpshadow' ),
					'description_short'  => __( 'Show helpful tips and suggestions', 'wpshadow' ),
					'description_long'   => __( 'Enables the display of helpful tips and suggestions in the WordPress dashboard. These are contextual recommendations based on your site configuration and content. Helps site owners discover features and best practices they might not know about. Disabled by default - enable if you want ongoing guidance.', 'wpshadow' ),
					'description_wizard' => __( 'Show helpful tips in the dashboard to guide you on site improvements and best practices.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'show_site_specific' => array(
					'name'               => __( 'Site-Specific Tips', 'wpshadow' ),
					'description_short'  => __( 'Customize tips based on site type', 'wpshadow' ),
					'description_long'   => __( 'Customizes tips based on your site type (blog, WooCommerce store, learning management system, or generic site). Blog tips focus on content strategy and SEO, WooCommerce tips on sales and product management, LMS tips on student engagement. Provides relevant suggestions instead of generic advice.', 'wpshadow' ),
					'description_wizard' => __( 'Get tips tailored to your specific site type instead of generic advice. Much more useful than one-size-fits-all suggestions.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'auto_dismiss'       => array(
					'name'               => __( 'Auto-Dismiss Completed Tips', 'wpshadow' ),
					'description_short'  => __( 'Hide tips after completing actions', 'wpshadow' ),
					'description_long'   => __( 'Automatically hides tips after you complete the recommended action. For example, if a tip suggests enabling SSL and you enable it, the tip disappears. Keeps dashboard clean by only showing relevant, uncompleted tips.', 'wpshadow' ),
					'description_wizard' => __( 'Automatically hide tips you\'ve completed. Keeps your dashboard showing only relevant, pending items.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'show_priorities'    => array(
					'name'               => __( 'Show Priority Levels', 'wpshadow' ),
					'description_short'  => __( 'Indicate which tips are most important', 'wpshadow' ),
					'description_long'   => __( 'Shows priority labels (Critical, Important, Nice-to-have) indicating which tips matter most. Helps you focus on high-impact improvements first instead of tackling everything. Critical items like SSL and security get top priority, nice-to-have optimization tips get lower priority.', 'wpshadow' ),
					'description_wizard' => __( 'Show which tips are critical vs optional. Helps you prioritize improvements based on impact.', 'wpshadow' ),
					'default_enabled'    => false,
				),
				'troubleshooting' => array(
					'name'               => __( 'Troubleshooting Wizard', 'wpshadow' ),
					'description_short'  => __( 'Intelligent problem solver for common issues', 'wpshadow' ),
					'description_long'   => __( 'Provides smart troubleshooting guidance for common WordPress problems. Analyzes error logs, detects issues automatically, and guides you through step-by-step solutions with one-click fixes when available. Covers performance problems, plugin conflicts, security issues, and more.', 'wpshadow' ),
					'description_wizard' => __( 'Get guided help fixing common WordPress issues. Smart detection with step-by-step solutions.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'video_walkthroughs' => array(
					'name'               => __( 'Video Walkthroughs', 'wpshadow' ),
					'description_short'  => __( 'Learn through auto-generated video tutorials', 'wpshadow' ),
					'description_long'   => __( 'Video library with walkthroughs of WPShadow features and common WordPress tasks. Auto-generated screen recordings show you exactly how to perform actions. Download or embed videos directly in your documentation. Perfect for training clients or team members.', 'wpshadow' ),
					'description_wizard' => __( 'Auto-generated video walkthroughs for features and tasks. Great for training and documentation.', 'wpshadow' ),
					'default_enabled'    => false,
				),
			),
		) );

		$this->register_default_settings( array(
			'enable_tips'        => true,
			'show_site_specific' => true,
			'auto_dismiss'       => true,
			'show_priorities'    => false,
			'troubleshooting'    => true,
			'video_walkthroughs' => false,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( $this->is_sub_feature_enabled( 'enable_tips', true ) ) {
			add_action( 'wp_ajax_wpshadow_dismiss_tip', array( $this, 'ajax_dismiss_tip' ) );
			add_action( 'wp_ajax_wpshadow_apply_tip_action', array( $this, 'ajax_apply_tip_action' ) );
		}

		if ( $this->is_sub_feature_enabled( 'troubleshooting', true ) ) {
			add_action( 'wp_ajax_wpshadow_detect_issues', array( $this, 'ajax_detect_issues' ) );
			add_action( 'wp_ajax_wpshadow_apply_troubleshooting_fix', array( $this, 'ajax_apply_troubleshooting_fix' ) );
		}

		if ( $this->is_sub_feature_enabled( 'video_walkthroughs', true ) ) {
			add_action( 'wp_ajax_wpshadow_get_video_library', array( $this, 'ajax_get_video_library' ) );
			add_action( 'wp_ajax_wpshadow_get_video_walkthrough', array( $this, 'ajax_get_video_walkthrough' ) );
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	private function detect_site_type(): string {
		if ( class_exists( '\WooCommerce' ) || $this->is_plugin_active_safe( 'woocommerce/woocommerce.php' ) ) {
			return self::TYPE_WOOCOMMERCE;
		}

		$lms_plugins = array(
			'sfwd-lms/sfwd_lms.php',
			'lifterlms/lifterlms.php',
			'tutor/tutor.php',
			'learnpress/learnpress.php',
		);

		foreach ( $lms_plugins as $plugin ) {
			if ( $this->is_plugin_active_safe( $plugin ) ) {
				return self::TYPE_LMS;
			}
		}

		$post_count = wp_count_posts( 'post' );
		if ( isset( $post_count->publish ) && $post_count->publish > 5 ) {
			return self::TYPE_BLOG;
		}

		return self::TYPE_GENERIC;
	}

	private function get_tips(): array {
		$site_type = $this->detect_site_type();
		$tips = $this->get_common_tips();

		if ( $this->is_sub_feature_enabled( 'show_site_specific', true ) ) {
			switch ( $site_type ) {
				case self::TYPE_BLOG:
					$tips = array_merge( $tips, $this->get_blog_tips() );
					break;
				case self::TYPE_WOOCOMMERCE:
					$tips = array_merge( $tips, $this->get_woocommerce_tips() );
					break;
				case self::TYPE_LMS:
					$tips = array_merge( $tips, $this->get_lms_tips() );
					break;
			}
		}

		$dismissed = get_user_meta( get_current_user_id(), 'wpshadow_dismissed_tips', true );
		if ( is_array( $dismissed ) ) {
			$tips = array_filter(
				$tips,
				function ( $tip ) use ( $dismissed ) {
					return ! in_array( $tip['id'], $dismissed, true );
				}
			);
		}

		return array_slice( $tips, 0, 3 );
	}

	private function get_common_tips(): array {
		$tips = array();

		if ( ! is_ssl() ) {
			$tips[] = array(
				'id'     => 'enable_ssl',
				'title'  => __( 'Enable HTTPS', 'wpshadow' ),
				'desc'   => __( 'Secure your site with HTTPS for better security and SEO.', 'wpshadow' ),
				'action' => 'open_ssl_info',
				'label'  => __( 'Learn More', 'wpshadow' ),
				'icon'   => 'dashicons-lock',
				'prio'   => 90,
			);
		}

		if ( ! $this->has_backup_plugin() ) {
			$tips[] = array(
				'id'     => 'setup_backups',
				'title'  => __( 'Set Up Backups', 'wpshadow' ),
				'desc'   => __( 'Protect your site with automated backups.', 'wpshadow' ),
				'action' => 'open_backup_info',
				'label'  => __( 'Learn More', 'wpshadow' ),
				'icon'   => 'dashicons-backup',
				'prio'   => 85,
			);
		}

		return $tips;
	}

	private function get_blog_tips(): array {
		$tips = array();

		if ( ! $this->has_seo_plugin() ) {
			$tips[] = array(
				'id'     => 'install_seo_plugin',
				'title'  => __( 'Optimize for Search Engines', 'wpshadow' ),
				'desc'   => __( 'Install an SEO plugin to improve search rankings.', 'wpshadow' ),
				'action' => 'open_seo_plugins',
				'label'  => __( 'Browse Plugins', 'wpshadow' ),
				'icon'   => 'dashicons-chart-line',
				'prio'   => 80,
			);
		}

		if ( ! $this->has_caching_plugin() ) {
			$tips[] = array(
				'id'     => 'enable_caching',
				'title'  => __( 'Speed Up Your Site', 'wpshadow' ),
				'desc'   => __( 'Enable caching to improve page load times.', 'wpshadow' ),
				'action' => 'open_caching_plugins',
				'label'  => __( 'Browse Plugins', 'wpshadow' ),
				'icon'   => 'dashicons-performance',
				'prio'   => 75,
			);
		}

		return $tips;
	}

	private function get_woocommerce_tips(): array {
		$tips = array();

		if ( function_exists( 'WC' ) ) {
			$wc = WC();

			if ( method_exists( $wc->payment_gateways ?? null, 'get_available_payment_gateways' ) ) {
				$gateways = $wc->payment_gateways->get_available_payment_gateways();
				if ( empty( $gateways ) ) {
					$tips[] = array(
						'id'     => 'setup_payment_gateway',
						'title'  => __( 'Configure Payment Gateway', 'wpshadow' ),
						'desc'   => __( 'Set up a payment method to start accepting orders.', 'wpshadow' ),
						'action' => 'open_woo_payments',
						'label'  => __( 'Configure Now', 'wpshadow' ),
						'icon'   => 'dashicons-money-alt',
						'prio'   => 95,
					);
				}
			}
		}

		return $tips;
	}

	private function get_lms_tips(): array {
		return array(
			array(
				'id'     => 'create_first_course',
				'title'  => __( 'Create Your First Course', 'wpshadow' ),
				'desc'   => __( 'Start building content for your students.', 'wpshadow' ),
				'action' => 'open_course_creation',
				'label'  => __( 'Create Course', 'wpshadow' ),
				'icon'   => 'dashicons-welcome-learn-more',
				'prio'   => 95,
			),
		);
	}

	private function is_plugin_active_safe( string $plugin ): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( $plugin );
	}

	private function has_seo_plugin(): bool {
		$plugins = array(
			'wordpress-seo/wp-seo.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'seo-by-rank-math/rank-math.php',
			'autodescription/autodescription.php',
		);

		foreach ( $plugins as $plugin ) {
			if ( $this->is_plugin_active_safe( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	private function has_caching_plugin(): bool {
		$plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'litespeed-cache/litespeed-cache.php',
		);

		foreach ( $plugins as $plugin ) {
			if ( $this->is_plugin_active_safe( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	private function has_backup_plugin(): bool {
		$plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'duplicator/duplicator.php',
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
		);

		foreach ( $plugins as $plugin ) {
			if ( $this->is_plugin_active_safe( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	public function ajax_dismiss_tip(): void {
		check_ajax_referer( 'wpshadow_tips_coach' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => __( 'Permission denied', 'wpshadow' ) ) );
		}

		$tip_id = sanitize_text_field( $_POST['tip_id'] ?? '' );
		if ( empty( $tip_id ) ) {
			wp_send_json_error( array( 'msg' => __( 'Invalid tip ID', 'wpshadow' ) ) );
		}

		$dismissed = get_user_meta( get_current_user_id(), 'wpshadow_dismissed_tips', true );
		if ( ! is_array( $dismissed ) ) {
			$dismissed = array();
		}

		if ( ! in_array( $tip_id, $dismissed, true ) ) {
			$dismissed[] = $tip_id;
			update_user_meta( get_current_user_id(), 'wpshadow_dismissed_tips', $dismissed );
		}

		wp_send_json_success( array( 'msg' => __( 'Tip dismissed', 'wpshadow' ) ) );
	}

	public function ajax_apply_tip_action(): void {
		check_ajax_referer( 'wpshadow_tips_coach' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => __( 'Permission denied', 'wpshadow' ) ) );
		}

		$action = sanitize_text_field( $_POST['action_type'] ?? '' );
		$tip_id = sanitize_text_field( $_POST['tip_id'] ?? '' );

		if ( empty( $action ) || empty( $tip_id ) ) {
			wp_send_json_error( array( 'msg' => __( 'Invalid parameters', 'wpshadow' ) ) );
		}

		$redirect = $this->get_action_redirect( $action );
		if ( $redirect ) {
			wp_send_json_success( array( 'redirect' => $redirect ) );
		} else {
			wp_send_json_error( array( 'msg' => __( 'Unknown action', 'wpshadow' ) ) );
		}
	}

	private function get_action_redirect( string $action ): ?string {
		switch ( $action ) {
			case 'open_ssl_info':
			case 'open_general_settings':
				return admin_url( 'options-general.php' );
			case 'open_backup_info':
				return admin_url( 'plugin-install.php?s=backup&tab=search' );
			case 'open_seo_plugins':
				return admin_url( 'plugin-install.php?s=seo&tab=search' );
			case 'open_caching_plugins':
				return admin_url( 'plugin-install.php?s=cache&tab=search' );
			case 'open_woo_payments':
				return admin_url( 'admin.php?page=wc-settings&tab=checkout' );
			case 'open_course_creation':
				if ( $this->is_plugin_active_safe( 'sfwd-lms/sfwd_lms.php' ) ) {
					return admin_url( 'post-new.php?post_type=sfwd-courses' );
				}
				return admin_url( 'edit.php?post_type=courses' );
			default:
				return null;
		}
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['tips_coach'] = array(
			'label'  => __( 'Tips Coach', 'wpshadow' ),
			'test'   => array( $this, 'test_tips_coach' ),
		);

		return $tests;
	}

	public function test_tips_coach(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Tips Coach', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable Tips Coach for personalized recommendations.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'tips_coach',
			);
		}

		$tips = $this->get_tips();
		$site_type = $this->detect_site_type();

		return array(
			'label'       => __( 'Tips Coach', 'wpshadow' ),
			'status'      => ! empty( $tips ) ? 'good' : 'recommended',
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d tips available for your %s', 'wpshadow' ),
				count( $tips ),
				$site_type
			),
			'actions'     => '',
			'test'        => 'tips_coach',
		);
	}

	public function ajax_detect_issues(): void {
		check_ajax_referer( 'wpshadow_tips_coach' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => __( 'Permission denied', 'wpshadow' ) ) );
		}

		$issues = array();

		$error_log = ini_get( 'error_log' );
		if ( $error_log && file_exists( $error_log ) ) {
			$recent_errors = $this->get_recent_errors( $error_log );
			if ( ! empty( $recent_errors ) ) {
				$issues[] = array(
					'id'          => 'php_errors',
					'title'       => __( 'PHP Errors Detected', 'wpshadow' ),
					'description' => __( 'Your site has recent PHP errors in the error log.', 'wpshadow' ),
					'severity'    => 'high',
					'fix_type'    => 'guided',
				);
			}
		}

		if ( $this->detect_plugin_conflicts() ) {
			$issues[] = array(
				'id'          => 'plugin_conflicts',
				'title'       => __( 'Potential Plugin Conflicts', 'wpshadow' ),
				'description' => __( 'Some plugins may be conflicting with each other.', 'wpshadow' ),
				'severity'    => 'medium',
				'fix_type'    => 'guided',
			);
		}

		if ( ! $this->has_backup_plugin() ) {
			$issues[] = array(
				'id'          => 'no_backups',
				'title'       => __( 'No Backup Plugin', 'wpshadow' ),
				'description' => __( 'Your site doesn\'t have an automated backup solution.', 'wpshadow' ),
				'severity'    => 'high',
				'fix_type'    => 'guided',
			);
		}

		if ( ! is_ssl() ) {
			$issues[] = array(
				'id'          => 'no_ssl',
				'title'       => __( 'HTTPS Not Enabled', 'wpshadow' ),
				'description' => __( 'Your site should use HTTPS for security.', 'wpshadow' ),
				'severity'    => 'high',
				'fix_type'    => 'guided',
			);
		}

		wp_send_json_success( array( 'issues' => $issues ) );
	}

	private function get_recent_errors( string $error_log ): array {
		$errors = array();
		$handle = fopen( $error_log, 'r' );
		if ( $handle ) {
			fseek( $handle, -4096, SEEK_END );
			$content = fread( $handle, 4096 );
			fclose( $handle );

			preg_match_all( '/\[(\d{2}-\w+-\d{4} \d{2}:\d{2}:\d{2})\].*?(Error|Warning|Notice):(.*)/i', $content, $matches );
			foreach ( $matches[0] as $error ) {
				$errors[] = $error;
			}
		}
		return array_slice( $errors, -5 );
	}

	private function detect_plugin_conflicts(): bool {

		$cache_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'litespeed-cache/litespeed-cache.php',
		);

		$active_cache = 0;
		foreach ( $cache_plugins as $plugin ) {
			if ( $this->is_plugin_active_safe( $plugin ) ) {
				$active_cache++;
			}
		}

		return $active_cache > 1;
	}

	public function ajax_apply_troubleshooting_fix(): void {
		check_ajax_referer( 'wpshadow_tips_coach' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => __( 'Permission denied', 'wpshadow' ) ) );
		}

		$issue_id = sanitize_text_field( $_POST['issue_id'] ?? '' );
		$fix_type = sanitize_text_field( $_POST['fix_type'] ?? '' );

		if ( empty( $issue_id ) || empty( $fix_type ) ) {
			wp_send_json_error( array( 'msg' => __( 'Invalid parameters', 'wpshadow' ) ) );
		}

		$result = false;
		switch ( $issue_id ) {
			case 'no_ssl':
				wp_send_json_success( array(
					'message' => __( 'Visit your hosting control panel or contact support to enable HTTPS.', 'wpshadow' ),
					'action'  => 'open_page',
					'page'    => admin_url( 'options-general.php' ),
				) );
				break;
			case 'no_backups':
				wp_send_json_success( array(
					'message' => __( 'Consider installing UpdraftPlus, BackWPup, or Duplicator for automated backups.', 'wpshadow' ),
					'action'  => 'open_page',
					'page'    => admin_url( 'plugin-install.php?s=backup&tab=search' ),
				) );
				break;
			default:
				wp_send_json_error( array( 'msg' => __( 'Unknown issue type', 'wpshadow' ) ) );
		}
	}

	public function ajax_get_video_library(): void {
		check_ajax_referer( 'wpshadow_tips_coach' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => __( 'Permission denied', 'wpshadow' ) ) );
		}

		$library = get_option( 'wpshadow_video_library', array() );
		if ( empty( $library ) ) {
			$library = $this->get_default_video_library();
		}

		wp_send_json_success( array( 'videos' => $library ) );
	}

	private function get_default_video_library(): array {
		return array(
			array(
				'id'       => 'getting_started',
				'title'    => __( 'Getting Started with WPShadow', 'wpshadow' ),
				'duration' => '5:30',
				'category' => 'tutorial',
				'url'      => '#',
				'thumbnail' => 'dashicons-media-video',
			),
			array(
				'id'       => 'enable_features',
				'title'    => __( 'Enabling and Configuring Features', 'wpshadow' ),
				'duration' => '8:15',
				'category' => 'tutorial',
				'url'      => '#',
				'thumbnail' => 'dashicons-media-video',
			),
			array(
				'id'       => 'site_health',
				'title'    => __( 'Understanding Site Health Scores', 'wpshadow' ),
				'duration' => '4:45',
				'category' => 'tutorial',
				'url'      => '#',
				'thumbnail' => 'dashicons-media-video',
			),
			array(
				'id'       => 'security_setup',
				'title'    => __( 'Hardening Security', 'wpshadow' ),
				'duration' => '10:20',
				'category' => 'tutorial',
				'url'      => '#',
				'thumbnail' => 'dashicons-media-video',
			),
			array(
				'id'       => 'performance',
				'title'    => __( 'Optimizing Performance', 'wpshadow' ),
				'duration' => '12:00',
				'category' => 'tutorial',
				'url'      => '#',
				'thumbnail' => 'dashicons-media-video',
			),
			array(
				'id'       => 'troubleshooting',
				'title'    => __( 'Troubleshooting Common Issues', 'wpshadow' ),
				'duration' => '15:30',
				'category' => 'guide',
				'url'      => '#',
				'thumbnail' => 'dashicons-media-video',
			),
		);
	}

	public function ajax_get_video_walkthrough(): void {
		check_ajax_referer( 'wpshadow_tips_coach' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => __( 'Permission denied', 'wpshadow' ) ) );
		}

		$video_id = sanitize_text_field( $_POST['video_id'] ?? '' );
		if ( empty( $video_id ) ) {
			wp_send_json_error( array( 'msg' => __( 'Invalid video ID', 'wpshadow' ) ) );
		}

		$library = $this->get_default_video_library();
		$video   = null;

		foreach ( $library as $item ) {
			if ( $item['id'] === $video_id ) {
				$video = $item;
				break;
			}
		}

		if ( ! $video ) {
			wp_send_json_error( array( 'msg' => __( 'Video not found', 'wpshadow' ) ) );
		}

		wp_send_json_success( array( 'video' => $video ) );
	}
}
