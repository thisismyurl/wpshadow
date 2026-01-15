<?php
/**
 * Features Discovery Dashboard Widget
 *
 * Shows active and ghost features from all modules in the WPShadow ecosystem.
 *
 * @package    WP_Support
 * @subpackage Core
 * @since      1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Features_Discovery_Widget Class
 *
 * Dashboard widget for feature discovery.
 */
class WPSHADOW_Features_Discovery_Widget {

	/**
	 * Initialize the widget.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_widget' ) );
	}

	/**
	 * Register the dashboard widget.
	 *
	 * @return void
	 */
	public static function register_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'wpshadow_features_discovery',
			__( '🚀 WPShadow Features - Discover What\'s Available', 'plugin-wpshadow' ),
			array( __CLASS__, 'render_widget' ),
			null,
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Render the dashboard widget.
	 *
	 * @return void
	 */
	public static function render_widget(): void {
		// Initialize ghost features if not already loaded.
		$all_features = WPSHADOW_Ghost_Features::get_all_features( true );
		
		if ( empty( $all_features ) ) {
			// Trigger registration if cache is empty.
			do_action( 'wpshadow_register_ghost_features' );
			$all_features = WPSHADOW_Ghost_Features::get_all_features( true );
		}

		$active_count = 0;
		$ghost_count  = 0;

		// Count active vs ghost features.
		foreach ( $all_features as $module_features ) {
			foreach ( $module_features as $feature ) {
				if ( $feature['is_available'] ) {
					$active_count++;
				} else {
					$ghost_count++;
				}
			}
		}

		?>
		<style>
			.wps-features-widget {
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			}
			.wps-features-stats {
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 15px;
				margin-bottom: 20px;
			}
			.wps-stat-box {
				padding: 15px;
				border-radius: 6px;
				text-align: center;
			}
			.wps-stat-number {
				font-size: 36px;
				font-weight: 700;
				line-height: 1;
				margin-bottom: 5px;
			}
			.wps-stat-label {
				font-size: 13px;
				color: #646970;
				text-transform: uppercase;
				letter-spacing: 0.5px;
				font-weight: 600;
			}
			.wps-features-tabs {
				display: flex;
				gap: 10px;
				margin-bottom: 20px;
				border-bottom: 2px solid #dcdcde;
			}
			.wps-tab-button {
				padding: 10px 15px;
				background: none;
				border: none;
				border-bottom: 3px solid transparent;
				cursor: pointer;
				font-weight: 600;
				color: #646970;
				transition: all 0.2s;
			}
			.wps-tab-button:hover {
				color: #2271b1;
			}
			.wps-tab-button.active {
				color: #2271b1;
				border-bottom-color: #2271b1;
			}
			.wps-tab-content {
				display: none;
			}
			.wps-tab-content.active {
				display: block;
			}
			.wps-mini-feature-card {
				padding: 12px;
				margin-bottom: 10px;
				border-left: 4px solid #2271b1;
				background: #f6f7f7;
				border-radius: 4px;
				transition: all 0.2s;
			}
			.wps-mini-feature-card:hover {
				background: #fff;
				box-shadow: 0 2px 8px rgba(0,0,0,0.1);
			}
			.wps-mini-feature-card.ghost {
				border-left-color: #dba617;
				opacity: 0.8;
			}
			.wps-feature-title {
				font-weight: 600;
				color: #1d2327;
				margin-bottom: 5px;
				display: flex;
				align-items: center;
				gap: 8px;
			}
			.wps-feature-desc {
				font-size: 13px;
				color: #50575e;
				margin-bottom: 8px;
				line-height: 1.5;
			}
			.wps-feature-module {
				font-size: 11px;
				color: #787c82;
			}
			.wps-install-link {
				display: inline-block;
				margin-top: 8px;
				font-size: 12px;
				color: #2271b1;
				text-decoration: none;
				font-weight: 600;
			}
			.wps-install-link:hover {
				color: #135e96;
			}
			.wps-cta-box {
				background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
				color: white;
				padding: 20px;
				border-radius: 8px;
				text-align: center;
				margin-top: 20px;
			}
			.wps-cta-box h3 {
				margin: 0 0 10px;
				color: white;
			}
			.wps-cta-button {
				display: inline-block;
				padding: 10px 20px;
				background: white;
				color: #2271b1;
				text-decoration: none;
				border-radius: 4px;
				font-weight: 600;
				margin-top: 10px;
				transition: all 0.2s;
			}
			.wps-cta-button:hover {
				background: #f0f0f1;
				transform: translateY(-2px);
				box-shadow: 0 4px 12px rgba(0,0,0,0.2);
			}
		</style>

		<div class="wps-features-widget">
			<!-- Stats Section -->
			<div class="wps-features-stats">
				<div class="wps-stat-box" style="background: #f0f9f1; border: 1px solid #46b450;">
					<div class="wps-stat-number" style="color: #46b450;"><?php echo absint( $active_count ); ?></div>
					<div class="wps-stat-label"><?php esc_html_e( 'Active Features', 'plugin-wpshadow' ); ?></div>
				</div>
				<div class="wps-stat-box" style="background: #fef8e7; border: 1px solid #dba617;">
					<div class="wps-stat-number" style="color: #dba617;"><?php echo absint( $ghost_count ); ?></div>
					<div class="wps-stat-label"><?php esc_html_e( 'Available to Install', 'plugin-wpshadow' ); ?></div>
				</div>
			</div>

			<!-- Tabs Section -->
			<div class="wps-features-tabs">
				<button class="wps-tab-button active" data-tab="all">
					<?php esc_html_e( 'All Features', 'plugin-wpshadow' ); ?>
				</button>
				<button class="wps-tab-button" data-tab="backup">
					<?php esc_html_e( 'Backup', 'plugin-wpshadow' ); ?>
				</button>
				<button class="wps-tab-button" data-tab="media">
					<?php esc_html_e( 'Media', 'plugin-wpshadow' ); ?>
				</button>
				<button class="wps-tab-button" data-tab="performance">
					<?php esc_html_e( 'Performance', 'plugin-wpshadow' ); ?>
				</button>
				<button class="wps-tab-button" data-tab="security">
					<?php esc_html_e( 'Security', 'plugin-wpshadow' ); ?>
				</button>
			</div>

			<!-- Tab Content -->
			<div class="wps-tab-content active" data-tab-content="all">
				<?php self::render_features_list( $all_features, 5 ); ?>
			</div>

			<div class="wps-tab-content" data-tab-content="backup">
				<?php
				$backup_features = WPSHADOW_Ghost_Features::get_features_by_category( 'backup', true );
				self::render_features_list( $backup_features, 5 );
				?>
			</div>

			<div class="wps-tab-content" data-tab-content="media">
				<?php
				$media_features = WPSHADOW_Ghost_Features::get_features_by_category( 'media', true );
				self::render_features_list( $media_features, 5 );
				?>
			</div>

			<div class="wps-tab-content" data-tab-content="performance">
				<?php
				$performance_features = WPSHADOW_Ghost_Features::get_features_by_category( 'performance', true );
				self::render_features_list( $performance_features, 5 );
				?>
			</div>

			<div class="wps-tab-content" data-tab-content="security">
				<?php
				$security_features = WPSHADOW_Ghost_Features::get_features_by_category( 'security', true );
				self::render_features_list( $security_features, 5 );
				?>
			</div>

			<!-- CTA Section -->
			<?php if ( $ghost_count > 0 ) : ?>
				<div class="wps-cta-box">
					<h3><?php esc_html_e( '🎁 Free Features Waiting!', 'plugin-wpshadow' ); ?></h3>
					<p style="margin: 0 0 15px; opacity: 0.9;">
						<?php
						/* translators: %d: Number of ghost features */
						printf( esc_html__( 'Install free modules to unlock %d additional features. No credit card required.', 'plugin-wpshadow' ), absint( $ghost_count ) );
						?>
					</p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-support&tab=modules' ) ); ?>" class="wps-cta-button">
						<?php esc_html_e( 'Browse All Modules', 'plugin-wpshadow' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<script>
		(function($) {
			'use strict';
			
			$(document).ready(function() {
				// Tab switching
				$('.wps-tab-button').on('click', function() {
					const tab = $(this).data('tab');
					
					// Update button states
					$('.wps-tab-button').removeClass('active');
					$(this).addClass('active');
					
					// Update content visibility
					$('.wps-tab-content').removeClass('active');
					$('[data-tab-content="' + tab + '"]').addClass('active');
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * Render features list.
	 *
	 * @param array $features Features grouped by module.
	 * @param int   $limit    Maximum features to display.
	 * @return void
	 */
	private static function render_features_list( array $features, int $limit = 5 ): void {
		if ( empty( $features ) ) {
			echo '<p style="color: #646970; text-align: center; padding: 20px;">';
			esc_html_e( 'No features found in this category.', 'plugin-wpshadow' );
			echo '</p>';
			return;
		}

		$count = 0;

		foreach ( $features as $module_slug => $module_features ) {
			foreach ( $module_features as $feature ) {
				if ( $count >= $limit ) {
					break 2;
				}

				$is_available = $feature['is_available'];
				$card_class   = $is_available ? '' : 'ghost';
				?>
				<div class="wps-mini-feature-card <?php echo esc_attr( $card_class ); ?>">
					<div class="wps-feature-title">
						<span class="dashicons <?php echo esc_attr( $feature['icon'] ); ?>"></span>
						<?php echo esc_html( $feature['title'] ); ?>
						<?php if ( $is_available ) : ?>
							<span style="display: inline-block; padding: 2px 6px; background: #46b450; color: white; font-size: 10px; border-radius: 3px; font-weight: 700;">
								<?php esc_html_e( 'ACTIVE', 'plugin-wpshadow' ); ?>
							</span>
						<?php else : ?>
							<span style="display: inline-block; padding: 2px 6px; background: #dba617; color: white; font-size: 10px; border-radius: 3px; font-weight: 700;">
								<?php esc_html_e( 'INSTALL', 'plugin-wpshadow' ); ?>
							</span>
						<?php endif; ?>
					</div>
					<div class="wps-feature-desc">
						<?php echo esc_html( $feature['description'] ); ?>
					</div>
					<div class="wps-feature-module">
						<?php
						/* translators: %s: Module name */
						printf( esc_html__( 'from %s module', 'plugin-wpshadow' ), '<strong>' . esc_html( $feature['module_name'] ) . '</strong>' );
						?>
					</div>
					<?php if ( ! $is_available ) : ?>
						<a href="<?php echo esc_url( $feature['install_url'] ); ?>" class="wps-install-link">
							<span class="dashicons dashicons-download" style="font-size: 12px; vertical-align: middle;"></span>
							<?php
							/* translators: %s: Module name */
							printf( esc_html__( 'Install %s →', 'plugin-wpshadow' ), esc_html( $feature['module_name'] ) );
							?>
						</a>
					<?php endif; ?>
				</div>
				<?php
				$count++;
			}
		}

		// Show "view all" link if there are more features.
		$total_count = 0;
		foreach ( $features as $module_features ) {
			$total_count += count( $module_features );
		}

		if ( $total_count > $limit ) {
			?>
			<p style="text-align: center; margin-top: 15px;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-support&tab=modules' ) ); ?>" style="color: #2271b1; text-decoration: none; font-weight: 600;">
					<?php
					/* translators: %d: Number of additional features */
					printf( esc_html__( 'View %d more features →', 'plugin-wpshadow' ), absint( $total_count - $limit ) );
					?>
				</a>
			</p>
			<?php
		}
	}
}
