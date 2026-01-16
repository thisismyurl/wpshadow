<?php
/**
 * Achievement Badges System for WPS Suite operations.
 *
 * Awards badges for actions like "First Fix," "Security Hardened," "Performance Boosted."
 * Displays badges in the dashboard and sends email notifications when badges are earned.
 *
 * @package wpshadow_Support
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Achievement Badges Class
 *
 * Tracks user achievements and awards badges for significant actions.
 */
class WPSHADOW_Achievement_Badges {

	/**
	 * Badge types.
	 */
	public const BADGE_FIRST_FIX         = 'first_fix';
	public const BADGE_SECURITY_HARDENED = 'security_hardened';
	public const BADGE_PERFORMANCE_BOOST = 'performance_boost';
	public const BADGE_MODULE_MASTER     = 'module_master';
	public const BADGE_VAULT_GUARDIAN    = 'vault_guardian';
	public const BADGE_EARLY_ADOPTER     = 'early_adopter';

	/**
	 * User meta key for badges.
	 */
	private const USER_META_KEY = 'wpshadow_achievement_badges';

	/**
	 * Option key for badge notification settings.
	 */
	private const NOTIFICATION_OPTION = 'wpshadow_badge_notifications_enabled';

	/**
	 * Initialize the achievement badges system.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Hook into activity events to award badges.
		add_action( 'wpshadow_feature_enabled', array( __CLASS__, 'on_feature_enabled' ), 10, 2 );
		add_action( 'wpshadow_module_activated', array( __CLASS__, 'on_module_activated' ), 10, 1 );
		add_action( 'wpshadow_security_action', array( __CLASS__, 'on_security_action' ), 10, 1 );
		add_action( 'wpshadow_performance_action', array( __CLASS__, 'on_performance_action' ), 10, 1 );
		add_action( 'wpshadow_vault_file_added', array( __CLASS__, 'on_vault_file_added' ), 10, 1 );

		// Add dashboard widget.
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_dashboard_widget' ) );

		// Add admin menu for badges page.
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );

		// Enqueue styles for badges.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_badge_styles' ) );
	}

	/**
	 * Get all badge definitions with metadata.
	 *
	 * @return array<string, array{title: string, description: string, icon: string, color: string}> Badge definitions.
	 */
	public static function get_badge_definitions(): array {
		return array(
			self::BADGE_FIRST_FIX         => array(
				'title'       => __( 'First Fix', 'plugin-wpshadow' ),
				'description' => __( 'Activated your first feature or module', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-admin-tools',
				'color'       => '#4CAF50',
			),
			self::BADGE_SECURITY_HARDENED => array(
				'title'       => __( 'Security Hardened', 'plugin-wpshadow' ),
				'description' => __( 'Enabled security features to protect your site', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-lock',
				'color'       => '#F44336',
			),
			self::BADGE_PERFORMANCE_BOOST => array(
				'title'       => __( 'Performance Boosted', 'plugin-wpshadow' ),
				'description' => __( 'Optimized your site for better performance', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-performance',
				'color'       => '#2196F3',
			),
			self::BADGE_MODULE_MASTER     => array(
				'title'       => __( 'Module Master', 'plugin-wpshadow' ),
				'description' => __( 'Activated 5 or more modules', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-admin-plugins',
				'color'       => '#9C27B0',
			),
			self::BADGE_VAULT_GUARDIAN    => array(
				'title'       => __( 'Vault Guardian', 'plugin-wpshadow' ),
				'description' => __( 'Protected your first file in the vault', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-vault',
				'color'       => '#FF9800',
			),
			self::BADGE_EARLY_ADOPTER     => array(
				'title'       => __( 'Early Adopter', 'plugin-wpshadow' ),
				'description' => __( 'One of the first to use WPShadow', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-star-filled',
				'color'       => '#FFD700',
			),
		);
	}

	/**
	 * Award a badge to a user.
	 *
	 * @param int    $user_id   User ID.
	 * @param string $badge_id  Badge identifier.
	 * @return bool True if badge was newly awarded, false if already earned.
	 */
	public static function award_badge( int $user_id, string $badge_id ): bool {
		$badges = self::get_user_badges( $user_id );

		// Check if badge is already earned.
		if ( isset( $badges[ $badge_id ] ) ) {
			return false;
		}

		// Add badge with timestamp.
		$badges[ $badge_id ] = array(
			'earned_at' => current_time( 'mysql' ),
			'timestamp' => time(),
		);

		update_user_meta( $user_id, self::USER_META_KEY, $badges );

		// Send notification if enabled.
		if ( self::are_notifications_enabled() ) {
			self::send_badge_notification( $user_id, $badge_id );
		}

		// Log achievement.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Activity_Logger' ) ) {
			WPSHADOW_Activity_Logger::log_event(
				'badge_earned',
				sprintf(
					/* translators: %s: Badge title */
					__( 'Earned badge: %s', 'plugin-wpshadow' ),
					self::get_badge_definitions()[ $badge_id ]['title']
				),
				$user_id
			);
		}

		return true;
	}

	/**
	 * Get badges earned by a user.
	 *
	 * @param int $user_id User ID.
	 * @return array<string, array{earned_at: string, timestamp: int}> User badges.
	 */
	public static function get_user_badges( int $user_id ): array {
		$badges = get_user_meta( $user_id, self::USER_META_KEY, true );
		return is_array( $badges ) ? $badges : array();
	}

	/**
	 * Check if badge notifications are enabled.
	 *
	 * @return bool True if notifications enabled.
	 */
	private static function are_notifications_enabled(): bool {
		return (bool) get_option( self::NOTIFICATION_OPTION, true );
	}

	/**
	 * Send badge notification email.
	 *
	 * @param int    $user_id  User ID.
	 * @param string $badge_id Badge identifier.
	 * @return void
	 */
	private static function send_badge_notification( int $user_id, string $badge_id ): void {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		$definitions = self::get_badge_definitions();
		if ( ! isset( $definitions[ $badge_id ] ) ) {
			return;
		}

		$badge = $definitions[ $badge_id ];

		$subject = sprintf(
			/* translators: 1: Site name, 2: Badge title */
			__( '[%1$s] Achievement Unlocked: %2$s', 'plugin-wpshadow' ),
			get_bloginfo( 'name' ),
			$badge['title']
		);

		$message = sprintf(
			/* translators: 1: User display name, 2: Badge title, 3: Badge description, 4: Site URL */
			__(
				'Congratulations %1$s!

You have earned the "%2$s" badge!

%3$s

View your achievements: %4$s

Keep up the great work!

-- The WPShadow Team',
				'plugin-wpshadow'
			),
			$user->display_name,
			$badge['title'],
			$badge['description'],
			admin_url( 'admin.php?page=wps-achievements' )
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		wp_mail( $user->user_email, $subject, $message, $headers );
	}

	/**
	 * Handle feature enabled event.
	 *
	 * @param string $feature_id Feature identifier.
	 * @param int    $user_id    User ID who enabled the feature.
	 * @return void
	 */
	public static function on_feature_enabled( string $feature_id, int $user_id ): void {
		// Award First Fix badge for first feature enabled.
		$badges = self::get_user_badges( $user_id );
		if ( empty( $badges ) ) {
			self::award_badge( $user_id, self::BADGE_FIRST_FIX );
		}

		// Check for security features.
		if ( str_contains( $feature_id, 'security' ) || str_contains( $feature_id, 'hardening' ) ) {
			self::award_badge( $user_id, self::BADGE_SECURITY_HARDENED );
		}

		// Check for performance features.
		$performance_keywords = array( 'minification', 'lazy', 'cleanup', 'optimization', 'cache', 'deferral' );
		foreach ( $performance_keywords as $keyword ) {
			if ( str_contains( $feature_id, $keyword ) ) {
				self::award_badge( $user_id, self::BADGE_PERFORMANCE_BOOST );
				break;
			}
		}
	}

	/**
	 * Handle module activated event.
	 *
	 * @param int $user_id User ID who activated the module.
	 * @return void
	 */
	public static function on_module_activated( int $user_id ): void {
		// Count active modules.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Module_Registry' ) ) {
			$catalog        = WPSHADOW_Module_Registry::get_catalog_with_status();
			$active_modules = array_filter(
				$catalog,
				function ( $module ) {
					return ! empty( $module['enabled'] );
				}
			);

			if ( count( $active_modules ) >= 5 ) {
				self::award_badge( $user_id, self::BADGE_MODULE_MASTER );
			}
		}

		// Award First Fix if this is their first action.
		$badges = self::get_user_badges( $user_id );
		if ( empty( $badges ) ) {
			self::award_badge( $user_id, self::BADGE_FIRST_FIX );
		}
	}

	/**
	 * Handle security action event.
	 *
	 * @param int $user_id User ID who performed security action.
	 * @return void
	 */
	public static function on_security_action( int $user_id ): void {
		self::award_badge( $user_id, self::BADGE_SECURITY_HARDENED );
	}

	/**
	 * Handle performance action event.
	 *
	 * @param int $user_id User ID who performed performance action.
	 * @return void
	 */
	public static function on_performance_action( int $user_id ): void {
		self::award_badge( $user_id, self::BADGE_PERFORMANCE_BOOST );
	}

	/**
	 * Handle vault file added event.
	 *
	 * @param int $user_id User ID who added vault file.
	 * @return void
	 */
	public static function on_vault_file_added( int $user_id ): void {
		self::award_badge( $user_id, self::BADGE_VAULT_GUARDIAN );
	}

	/**
	 * Register dashboard widget.
	 *
	 * @return void
	 */
	public static function register_dashboard_widget(): void {
		wp_add_dashboard_widget(
			'wpshadow_achievement_badges',
			__( 'Achievement Badges', 'plugin-wpshadow' ),
			array( __CLASS__, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @return void
	 */
	public static function render_dashboard_widget(): void {
		$user_id     = get_current_user_id();
		$badges      = self::get_user_badges( $user_id );
		$definitions = self::get_badge_definitions();

		echo '<div class="wps-badges-widget">';

		if ( empty( $badges ) ) {
			echo '<p>' . esc_html__( 'Start using WPShadow features to earn achievement badges!', 'plugin-wpshadow' ) . '</p>';
		} else {
			echo '<div class="wps-badges-earned">';
			foreach ( $badges as $badge_id => $badge_data ) {
				if ( ! isset( $definitions[ $badge_id ] ) ) {
					continue;
				}
				$def = $definitions[ $badge_id ];
				printf(
					'<div class="wps-badge-item earned" title="%s">
						<span class="dashicons %s" style="color: %s;"></span>
						<div class="badge-info">
							<strong>%s</strong>
							<small>%s</small>
						</div>
					</div>',
					esc_attr( $def['description'] ),
					esc_attr( $def['icon'] ),
					esc_attr( $def['color'] ),
					esc_html( $def['title'] ),
					esc_html( human_time_diff( $badge_data['timestamp'], time() ) . ' ' . __( 'ago', 'plugin-wpshadow' ) )
				);
			}
			echo '</div>';
		}

		// Show locked badges.
		$locked_badges = array_diff_key( $definitions, $badges );
		if ( ! empty( $locked_badges ) ) {
			echo '<div class="wps-badges-locked">';
			echo '<h4>' . esc_html__( 'Locked Badges', 'plugin-wpshadow' ) . '</h4>';
			foreach ( $locked_badges as $badge_id => $def ) {
				printf(
					'<div class="wps-badge-item locked" title="%s">
						<span class="dashicons %s"></span>
						<div class="badge-info">
							<strong>%s</strong>
							<small>%s</small>
						</div>
					</div>',
					esc_attr( $def['description'] ),
					esc_attr( $def['icon'] ),
					esc_html( $def['title'] ),
					esc_html( $def['description'] )
				);
			}
			echo '</div>';
		}

		echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=wps-achievements' ) ) . '" class="button">' . esc_html__( 'View All Achievements', 'plugin-wpshadow' ) . '</a></p>';
		echo '</div>';
	}

	/**
	 * Register admin menu for achievements page.
	 *
	 * @return void
	 */
	public static function register_admin_menu(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Achievements', 'plugin-wpshadow' ),
			__( 'Achievements', 'plugin-wpshadow' ),
			'read',
			'wps-achievements',
			array( __CLASS__, 'render_achievements_page' )
		);
	}

	/**
	 * Render achievements page.
	 *
	 * @return void
	 */
	public static function render_achievements_page(): void {
		$user_id     = get_current_user_id();
		$badges      = self::get_user_badges( $user_id );
		$definitions = self::get_badge_definitions();

		echo '<div class="wrap wps-achievements-page">';
		echo '<h1>' . esc_html__( 'Your Achievements', 'plugin-wpshadow' ) . '</h1>';

		// Stats.
		$earned_count = count( $badges );
		$total_count  = count( $definitions );
		$percentage   = $total_count > 0 ? round( ( $earned_count / $total_count ) * 100 ) : 0;

		echo '<div class="wps-achievement-stats">';
		printf(
			'<p>%s</p>',
			sprintf(
				/* translators: 1: Earned count, 2: Total count, 3: Percentage */
				esc_html__( 'You have earned %1$d out of %2$d badges (%3$d%%)', 'plugin-wpshadow' ),
				(int) $earned_count,
				(int) $total_count,
				(int) $percentage
			)
		);
		echo '<div class="wps-progress-bar"><div class="wps-progress-fill" style="width: ' . esc_attr( $percentage ) . '%;"></div></div>';
		echo '</div>';

		// Earned badges.
		echo '<h2>' . esc_html__( 'Earned Badges', 'plugin-wpshadow' ) . '</h2>';
		echo '<div class="wps-badges-grid">';
		foreach ( $badges as $badge_id => $badge_data ) {
			if ( ! isset( $definitions[ $badge_id ] ) ) {
				continue;
			}
			$def = $definitions[ $badge_id ];
			printf(
				'<div class="wps-badge-card earned">
					<div class="badge-icon" style="background-color: %s;">
						<span class="dashicons %s"></span>
					</div>
					<h3>%s</h3>
					<p>%s</p>
					<small>%s</small>
				</div>',
				esc_attr( $def['color'] ),
				esc_attr( $def['icon'] ),
				esc_html( $def['title'] ),
				esc_html( $def['description'] ),
				esc_html(
					sprintf(
					/* translators: %s: Time ago */
						__( 'Earned %s', 'plugin-wpshadow' ),
						human_time_diff( $badge_data['timestamp'], time() ) . ' ' . __( 'ago', 'plugin-wpshadow' )
					)
				)
			);
		}
		echo '</div>';

		// Locked badges.
		$locked_badges = array_diff_key( $definitions, $badges );
		if ( ! empty( $locked_badges ) ) {
			echo '<h2>' . esc_html__( 'Locked Badges', 'plugin-wpshadow' ) . '</h2>';
			echo '<div class="wps-badges-grid">';
			foreach ( $locked_badges as $badge_id => $def ) {
				printf(
					'<div class="wps-badge-card locked">
						<div class="badge-icon">
							<span class="dashicons %s"></span>
						</div>
						<h3>%s</h3>
						<p>%s</p>
					</div>',
					esc_attr( $def['icon'] ),
					esc_html( $def['title'] ),
					esc_html( $def['description'] )
				);
			}
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Enqueue badge styles.
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public static function enqueue_badge_styles( string $hook ): void {
		// Only load on dashboard and achievements page.
		if ( 'index.php' !== $hook && false === strpos( $hook, 'wps-achievements' ) ) {
			return;
		}

		wp_add_inline_style(
			'wps-core-admin',
			'
			.wps-badges-widget { padding: 12px 0; }
			.wps-badge-item { display: flex; align-items: center; gap: 12px; padding: 8px 0; border-bottom: 1px solid #f0f0f1; }
			.wps-badge-item:last-child { border-bottom: none; }
			.wps-badge-item .dashicons { font-size: 32px; width: 32px; height: 32px; }
			.wps-badge-item.locked .dashicons { color: #c3c4c7; }
			.wps-badge-item .badge-info { flex: 1; }
			.wps-badge-item .badge-info strong { display: block; font-size: 14px; }
			.wps-badge-item .badge-info small { color: #646970; font-size: 12px; }
			.wps-badges-locked { margin-top: 20px; padding-top: 20px; border-top: 1px solid #f0f0f1; }
			.wps-badges-locked h4 { margin: 0 0 12px 0; color: #646970; font-size: 13px; text-transform: uppercase; }
			.wps-achievement-stats { background: #f6f7f7; padding: 16px; border-radius: 4px; margin: 20px 0; }
			.wps-progress-bar { background: #dcdcde; height: 20px; border-radius: 10px; overflow: hidden; margin-top: 10px; }
			.wps-progress-fill { background: linear-gradient(90deg, #2196F3, #4CAF50); height: 100%; transition: width 0.3s; }
			.wps-badges-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
			.wps-badge-card { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 20px; text-align: center; }
			.wps-badge-card.locked { opacity: 0.5; }
			.wps-badge-card .badge-icon { width: 80px; height: 80px; margin: 0 auto 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
			.wps-badge-card.earned .badge-icon { background-color: #f0f0f1; }
			.wps-badge-card.locked .badge-icon { background-color: #f6f7f7; }
			.wps-badge-card .badge-icon .dashicons { font-size: 48px; width: 48px; height: 48px; color: #fff; }
			.wps-badge-card.locked .badge-icon .dashicons { color: #c3c4c7; }
			.wps-badge-card h3 { margin: 0 0 8px 0; font-size: 16px; }
			.wps-badge-card p { margin: 0; color: #646970; font-size: 13px; }
			.wps-badge-card small { display: block; margin-top: 12px; color: #50575e; font-size: 12px; }
		'
		);
	}
}
