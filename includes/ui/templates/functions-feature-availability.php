<?php
/**
 * Feature Availability Template Helper
 *
 * Provides template functions to conditionally display cards and links
 * based on feature availability (checks @since tags).
 *
 * @package    WPShadow
 * @subpackage UI\Templates
 * @since 0.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Version_Checker;

/**
 * Render a feature card only if it's live
 *
 * Usage:
 * ```php
 * wpshadow_render_feature_card_if_live(
 *     'WPShadow\Diagnostics\Diagnostic_Example',
 *     'Card Title',
 *     'Card description text',
 *     'dashicons-admin-tools',
 *     'https://example.com/link'
 * );
 * ```
 *
 * @since 0.6093.1200
 * @param  string   $class_name       Full class name to check.
 * @param  string   $title            Card title.
 * @param  string   $description      Card description.
 * @param  string   $icon             Icon class (e.g., 'dashicons-admin-tools').
 * @param  string   $button_url       URL for button link.
 * @param  string   $button_text      Button text (optional).
 * @param  callable $content_callback Callback to render card body (optional).
 * @return void
 */
function wpshadow_render_feature_card_if_live(
	string $class_name,
	string $title,
	string $description,
	string $icon,
	string $button_url = '',
	string $button_text = '',
	callable $content_callback = null
): void {
	// Initialize version checker on first use
	static $version_checker_init = false;
	if ( ! $version_checker_init ) {
		Version_Checker::init();
		$version_checker_init = true;
	}

	// Check if feature is live
	if ( ! Version_Checker::is_feature_live( $class_name ) ) {
		return;
	}

	// Render the card
	?>
	<div class="wps-card">
		<div class="wps-card-header wps-pb-3 wps-border-bottom">
			<div class="wps-flex wps-gap-3 wps-items-start">
				<span class="<?php echo esc_attr( $icon ); ?> wps-text-3xl wps-text-primary"></span>
				<div>
					<h3 class="wps-card-title wps-m-0">
						<?php
						if ( ! empty( $button_url ) ) {
							?>
							<a href="<?php echo esc_url( $button_url ); ?>" style="color: inherit; text-decoration: none;">
								<?php echo esc_html( $title ); ?>
							</a>
							<?php
						} else {
							echo esc_html( $title );
						}
						?>
					</h3>
					<p class="wps-card-description wps-m-0">
						<?php echo esc_html( $description ); ?>
					</p>
				</div>
			</div>
		</div>
		<div class="wps-card-body">
			<?php
			if ( is_callable( $content_callback ) ) {
				call_user_func( $content_callback );
			} elseif ( ! empty( $button_url ) ) {
				?>
				<a href="<?php echo esc_url( $button_url ); ?>" class="wps-btn wps-btn--secondary">
					<span class="dashicons dashicons-external"></span>
					<?php echo esc_html( $button_text ?: __( 'Learn More', 'wpshadow' ) ); ?>
				</a>
				<?php
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Render a "coming soon" placeholder for future features
 *
 * @since 0.6093.1200
 * @param  string $title      Card title.
 * @param  string $class_name Class name (to show @since version).
 * @return void
 */
function wpshadow_render_coming_soon_card( string $title, string $class_name = '' ): void {
	$coming_in_version = '';

	if ( ! empty( $class_name ) ) {
		$since = Version_Checker::get_feature_since( $class_name );
		if ( ! empty( $since ) ) {
			$coming_in_version = sprintf(
				/* translators: %s: version number */
				esc_html__( 'Coming in v%s', 'wpshadow' ),
				esc_html( $since )
			);
		}
	}
	?>
	<div class="wps-card wps-opacity-50">
		<div class="wps-card-header wps-pb-3 wps-border-bottom">
			<div class="wps-flex wps-gap-3 wps-items-start">
				<span class="dashicons dashicons-clock wps-text-3xl" style="color: #999;"></span>
				<div>
					<h3 class="wps-card-title wps-m-0" style="color: #999;">
						<?php echo esc_html( $title ); ?>
					</h3>
					<p class="wps-card-description wps-m-0">
						<?php echo esc_html__( 'This feature is not yet available in your version.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
		</div>
		<div class="wps-card-body">
			<?php if ( ! empty( $coming_in_version ) ) : ?>
				<p style="color: #999; font-size: 0.9em; margin: 0;">
					<?php echo esc_html( $coming_in_version ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Conditionally render a menu link only if feature is live
 *
 * Usage:
 * ```php
 * wpshadow_render_menu_link_if_live(
 *     'WPShadow\Admin\Example_Page',
 *     'Example Page',
 *     'admin.php?page=wpshadow-example'
 * );
 * ```
 *
 * @since 0.6093.1200
 * @param  string $class_name   Full class name to check.
 * @param  string $label        Menu label.
 * @param  string $url          Menu URL.
 * @param  string $icon         Icon HTML (optional).
 * @param  bool   $show_coming_soon Show "coming soon" text if not live (default false).
 * @return void
 */
function wpshadow_render_menu_link_if_live(
	string $class_name,
	string $label,
	string $url = '',
	string $icon = '',
	bool $show_coming_soon = false
): void {
	// Initialize version checker
	static $version_checker_init = false;
	if ( ! $version_checker_init ) {
		Version_Checker::init();
		$version_checker_init = true;
	}

	// Check if feature is live
	if ( ! Version_Checker::is_feature_live( $class_name ) ) {
		if ( ! $show_coming_soon ) {
			return; // Don't render at all
		}

		// Render disabled/grayed out version
		$since = Version_Checker::get_feature_since( $class_name );
		$coming_text = ! empty( $since ) ? sprintf( ' (%s)', esc_html( $since ) ) : '';
		?>
		<li class="wps-menu-item wps-menu-item--disabled">
			<?php echo wp_kses_post( $icon ); ?>
			<span style="color: #999; text-decoration: line-through;">
				<?php echo esc_html( $label . $coming_text ); ?>
			</span>
		</li>
		<?php
		return;
	}

	// Render active link
	?>
	<li class="wps-menu-item">
		<?php echo wp_kses_post( $icon ); ?>
		<a href="<?php echo esc_url( $url ); ?>" class="wps-menu-link">
			<?php echo esc_html( $label ); ?>
		</a>
	</li>
	<?php
}

/**
 * Check if feature is live (for use in templates)
 *
 * @since 0.6093.1200
 * @param  string $class_name Full class name.
 * @return bool True if live.
 */
function wpshadow_is_feature_live( string $class_name ): bool {
	static $version_checker_init = false;
	if ( ! $version_checker_init ) {
		Version_Checker::init();
		$version_checker_init = true;
	}

	return Version_Checker::is_feature_live( $class_name );
}
