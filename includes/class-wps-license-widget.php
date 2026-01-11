<?php
/**
 * License Status Widget
 *
 * Persistent widget displayed on all WP Support dashboard pages.
 * Cannot be dismissed, moved, or reordered until plugin is licensed.
 *
 * @package    WP_Support
 * @subpackage Core
 * @since      1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * License Widget Class
 *
 * Displays persistent license status widget on all TIMU plugin dashboards.
 */
class WPS_License_Widget {

	/**
	 * Widget ID
	 *
	 * @var string
	 */
	private const WIDGET_ID = 'wps_license_widget';

	/**
	 * Initialize the license widget
	 */
	public static function init(): void {
		// Add widget to all WP Support pages (use admin_head for custom pages).
		add_action( 'admin_head', array( __CLASS__, 'maybe_add_to_dashboard' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_widget_scripts' ) );
		add_action( 'admin_footer', array( __CLASS__, 'force_widget_position' ) );
		add_action( 'admin_init', array( __CLASS__, 'clear_screen_layout_cache' ) );

		// Prevent dismissal if unlicensed.
		add_filter( 'user_can_dismiss_widget', array( __CLASS__, 'prevent_dismissal' ), 10, 3 );
	}

	/**
	 * Check if current page is a TIMU plugin page
	 *
	 * @return bool
	 */
	private static function is_timu_page(): bool {
		if ( ! isset( $_GET['page'] ) ) {
			return false;
		}

		$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );

		// Core and hub pages.
		$timu_pages = array(
			'wp-support',
			'image-hub',
			'video-hub',
		);

		// Check for spoke pages (format-specific plugins).
		$spoke_patterns = array( '-spoke', '-support-thisismyurl' );

		foreach ( $timu_pages as $timu_page ) {
			if ( $page === $timu_page ) {
				return true;
			}
		}

		foreach ( $spoke_patterns as $pattern ) {
			if ( false !== strpos( $page, $pattern ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add widget to dashboard (only on TIMU pages)
	 */
	public static function maybe_add_to_dashboard(): void {
		if ( ! self::is_timu_page() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		// Add widget with core priority to appear first.
		add_meta_box(
			self::WIDGET_ID,
			__( 'License Status', 'plugin-wp-support-thisismyurl' ),
			array( __CLASS__, 'render_widget' ),
			$screen->id,
			'side',
			'core' // Use 'core' instead of 'high' for top positioning
		);
	}

	/**
	 * Render the license widget
	 */
	public static function render_widget(): void {
		$license_key    = get_option( 'wps_license_key', '' );
		$has_license    = ! empty( $license_key );
		$update_data    = get_transient( 'wps_update_data' );
		$license_valid  = false;
		$license_expire = '';

		if ( $update_data && is_array( $update_data ) ) {
			$license_valid  = $update_data['license_valid'] ?? false;
			$license_expire = $update_data['license_expires'] ?? '';
		}

		// Determine status.
		if ( $has_license && $license_valid ) {
			$status_class = 'licensed';
			$status_icon  = 'yes-alt';
			$status_color = '#46b450';
			$status_text  = __( 'Licensed & Active', 'plugin-wp-support-thisismyurl' );
		} elseif ( $has_license && ! $license_valid ) {
			$status_class = 'invalid';
			$status_icon  = 'warning';
			$status_color = '#f0b849';
			$status_text  = __( 'License Invalid', 'plugin-wp-support-thisismyurl' );
		} else {
			$status_class = 'unlicensed';
			$status_icon  = 'lock';
			$status_color = '#dc3232';
			$status_text  = __( 'Unlicensed', 'plugin-wp-support-thisismyurl' );
		}

		?>
		<div class="wps-license-widget wps-license-<?php echo esc_attr( $status_class ); ?>">
			<div class="wps-license-status" style="text-align: center; padding: 15px 0;">
				<span class="dashicons dashicons-<?php echo esc_attr( $status_icon ); ?>" 
					  style="font-size: 48px; width: 48px; height: 48px; color: <?php echo esc_attr( $status_color ); ?>;"></span>
				<h3 style="margin: 10px 0 5px; font-size: 16px;">
					<?php echo esc_html( $status_text ); ?>
				</h3>
			</div>

			<?php if ( $has_license && $license_valid && $license_expire ) : ?>
				<div class="wps-license-details" style="text-align: center; padding: 0 15px 10px; font-size: 13px; color: #666;">
					<?php
					$expire_date = strtotime( $license_expire );
					$days_left   = (int) ( ( $expire_date - time() ) / DAY_IN_SECONDS );
					
					if ( $days_left <= 30 ) {
						echo '<span style="color: #f0b849; font-weight: 600;">';
						printf(
							/* translators: %d: days until expiration */
							esc_html( _n( 'Expires in %d day', 'Expires in %d days', $days_left, 'plugin-wp-support-thisismyurl' ) ),
							$days_left
						);
						echo '</span>';
					} else {
						printf(
							/* translators: %s: expiration date */
							esc_html__( 'Expires: %s', 'plugin-wp-support-thisismyurl' ),
							esc_html( date_i18n( get_option( 'date_format' ), $expire_date ) )
						);
					}
					?>
				</div>
			<?php endif; ?>

			<?php if ( ! $has_license || ! $license_valid ) : ?>
				<div class="wps-license-message" style="padding: 10px 15px; background: #f8f9fa; border-top: 1px solid #ddd;">
					<p style="margin: 0 0 10px; font-size: 13px;">
						<?php if ( ! $has_license ) : ?>
							<?php esc_html_e( 'Enter your license key to receive automatic updates and unlock premium features.', 'plugin-wp-support-thisismyurl' ); ?>
						<?php else : ?>
							<?php esc_html_e( 'Your license key is invalid or expired. Please check your key or contact support.', 'plugin-wp-support-thisismyurl' ); ?>
						<?php endif; ?>
					</p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-support&tab=updates' ) ); ?>" 
					   class="button button-primary button-large" 
					   style="width: 100%; text-align: center; margin-bottom: 5px;">
						<?php esc_html_e( 'Enter License Key', 'plugin-wp-support-thisismyurl' ); ?>
					</a>
					<a href="https://thisismyurl.com/wp-support/#pricing" 
					   target="_blank" 
					   class="button button-secondary button-large" 
					   style="width: 100%; text-align: center;">
						<?php esc_html_e( 'Purchase License', 'plugin-wp-support-thisismyurl' ); ?>
					</a>
				</div>
			<?php else : ?>
				<div class="wps-license-actions" style="padding: 10px 15px; background: #f8f9fa; border-top: 1px solid #ddd; text-align: center;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-support&tab=updates' ) ); ?>" 
					   class="button button-secondary" 
					   style="width: 100%;">
						<?php esc_html_e( 'Manage License', 'plugin-wp-support-thisismyurl' ); ?>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( ! $has_license || ! $license_valid ) : ?>
				<style>
					/* Make widget non-dismissible when unlicensed */
					#<?php echo esc_attr( self::WIDGET_ID ); ?> .handle-actions,
					#<?php echo esc_attr( self::WIDGET_ID ); ?> .handlediv {
						display: none !important;
					}
					#<?php echo esc_attr( self::WIDGET_ID ); ?> {
						cursor: default !important;
					}
				</style>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Enqueue widget scripts to force positioning
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue_widget_scripts( string $hook ): void {
		if ( ! self::is_timu_page() ) {
			return;
		}

		wp_add_inline_style( 'wp-admin', self::get_widget_css() );
	}

	/**
	 * Get widget CSS
	 *
	 * @return string CSS rules.
	 */
	private static function get_widget_css(): string {
		return '
			/* License widget styling */
			.wps-license-widget {
				background: #fff;
			}
			
			.wps-license-unlicensed .wps-license-status,
			.wps-license-invalid .wps-license-status {
				background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
				border-bottom: 3px solid #dc3232;
			}
			
			.wps-license-licensed .wps-license-status {
				background: linear-gradient(135deg, #fff 0%, #f0fff4 100%);
				border-bottom: 3px solid #46b450;
			}
			
			/* Force widget to stay at top of side column */
			#' . self::WIDGET_ID . ' {
				order: -999 !important;
			}
			
			/* Override any saved positions */
			.postbox-container .meta-box-sortables {
				display: flex;
				flex-direction: column;
			}
			
			#' . self::WIDGET_ID . ' {
				order: -999 !important;
			}
			
			/* Highlight widget when unlicensed */
			.wps-license-unlicensed #' . self::WIDGET_ID . ',
			.wps-license-invalid #' . self::WIDGET_ID . ' {
				border-left: 4px solid #dc3232;
				box-shadow: 0 1px 3px rgba(220, 50, 50, 0.1);
			}
			
			.wps-license-licensed #' . self::WIDGET_ID . ' {
				border-left: 4px solid #46b450;
			}
		';
	}

	/**
	 * Force widget position at top of side column
	 */
	public static function force_widget_position(): void {
		if ( ! self::is_timu_page() ) {
			return;
		}

		$license_key   = get_option( 'wps_license_key', '' );
		$has_license   = ! empty( $license_key );
		$update_data   = get_transient( 'wps_update_data' );
		$license_valid = ( $update_data && is_array( $update_data ) ) ? ( $update_data['license_valid'] ?? false ) : false;
		$is_licensed   = $has_license && $license_valid;

		?>
		<script>
		(function($) {
			'use strict';
			
			// Run immediately and on page load
			function positionLicenseWidget() {
				var widgetId = '<?php echo esc_js( self::WIDGET_ID ); ?>';
				var $widget = $('#' + widgetId);
				var isLicensed = <?php echo $is_licensed ? 'true' : 'false'; ?>;
				
				if ($widget.length === 0) {
					return;
				}
				
				// Find all possible side column selectors
				var $sideColumn = $('#postbox-container-1 .meta-box-sortables, ' +
									'#side-sortables, ' +
									'.postbox-container.side .meta-box-sortables');
				
				if ($sideColumn.length > 0) {
					// Force to very first position
					$sideColumn.first().prepend($widget);
					$widget.show();
				}
				
				// Prevent dragging if unlicensed.
				if (!isLicensed) {
					$widget.removeClass('postbox closed');
					$widget.addClass('postbox');
					$widget.find('.hndle').css('cursor', 'default');
					
					// Disable sortable on this specific widget.
					$widget.off('mousedown');
					
					// Re-position if user tries to move it.
					var repositionTimer;
					$('.meta-box-sortables').on('sortstop sortupdate', function() {
						clearTimeout(repositionTimer);
						repositionTimer = setTimeout(function() {
							if ($widget.index() !== 0) {
								$sideColumn.first().prepend($widget);
							}
						}, 10);
					});
					
					// Force open if closed
					$widget.removeClass('closed');
					$widget.find('.handlediv').remove();
					$widget.find('.handle-actions').remove();
				}
				
				// Add body class for styling.
				$('body').addClass(isLicensed ? 'wps-license-licensed' : 'wps-license-unlicensed');
			}
			
			// Run immediately
			positionLicenseWidget();
			
			// Run on DOM ready
			$(document).ready(function() {
				positionLicenseWidget();
			});
			
			// Run after postboxes initialize
			$(window).on('load', function() {
				setTimeout(positionLicenseWidget, 100);
				setTimeout(positionLicenseWidget, 500);
			});
			
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * Prevent widget dismissal when unlicensed
	 *
	 * @param bool   $can_dismiss Whether user can dismiss.
	 * @param string $widget_id   Widget ID.
	 * @param object $widget      Widget object.
	 * @return bool
	 */
	public static function prevent_dismissal( bool $can_dismiss, string $widget_id, $widget ): bool {
		if ( $widget_id !== self::WIDGET_ID ) {
			return $can_dismiss;
		}

		$license_key   = get_option( 'wps_license_key', '' );
		$has_license   = ! empty( $license_key );
		$update_data   = get_transient( 'wps_update_data' );
		$license_valid = ( $update_data && is_array( $update_data ) ) ? ( $update_data['license_valid'] ?? false ) : false;

		// Only allow dismissal if licensed.
		return $has_license && $license_valid;
	}

	/**
	 * Check if plugin is licensed
	 *
	 * @return bool
	 */
	public static function is_licensed(): bool {
		$license_key   = get_option( 'wps_license_key', '' );
		$has_license   = ! empty( $license_key );
		$update_data   = get_transient( 'wps_update_data' );
		$license_valid = ( $update_data && is_array( $update_data ) ) ? ( $update_data['license_valid'] ?? false ) : false;

		return $has_license && $license_valid;
	}

	/**
	 * Clear screen layout cache to force widget reordering
	 */
	public static function clear_screen_layout_cache(): void {
		if ( ! self::is_timu_page() ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		// Clear screen layout options that cache widget positions.
		$screen = get_current_screen();
		if ( $screen ) {
			delete_user_meta( $user_id, 'meta-box-order_' . $screen->id );
			delete_user_meta( $user_id, 'screen_layout_' . $screen->id );
			delete_user_meta( $user_id, 'closedpostboxes_' . $screen->id );
			delete_user_meta( $user_id, 'metaboxhidden_' . $screen->id );
		}

		// Also clear for common TIMU screen IDs.
		$timu_screens = array(
			'toplevel_page_wp-support',
			'wp-support_page_image-hub',
			'wp-support_page_video-hub',
		);

		foreach ( $timu_screens as $screen_id ) {
			delete_user_meta( $user_id, 'meta-box-order_' . $screen_id );
			delete_user_meta( $user_id, 'screen_layout_' . $screen_id );
			delete_user_meta( $user_id, 'closedpostboxes_' . $screen_id );
			delete_user_meta( $user_id, 'metaboxhidden_' . $screen_id );
		}
	}
}
