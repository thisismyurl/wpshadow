<?php
/**
 * Dark Mode Tool Page
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'read' ) ) {
	wp_die( 'Insufficient permissions.' );
}

$user_id = get_current_user_id();
$dark_mode_pref = get_user_meta( $user_id, 'wpshadow_dark_mode_preference', true ) ?: 'auto';
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Dark Mode', 'wpshadow' ); ?></h1>
	<p><?php esc_html_e( 'Enable dark mode for the WordPress admin interface.', 'wpshadow' ); ?></p>

	<div class="wpshadow-tool-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px;">
		<h2><?php esc_html_e( 'Dark Mode Settings', 'wpshadow' ); ?></h2>
		
		<form method="post" action="">
			<?php wp_nonce_field( 'wpshadow_dark_mode', 'wpshadow_dark_mode_nonce' ); ?>
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Mode Preference', 'wpshadow' ); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="radio" name="dark_mode_pref" value="auto" <?php checked( $dark_mode_pref, 'auto' ); ?>>
								<?php esc_html_e( 'Auto (follow system/WordPress theme)', 'wpshadow' ); ?>
							</label><br>
							<label>
								<input type="radio" name="dark_mode_pref" value="light" <?php checked( $dark_mode_pref, 'light' ); ?>>
								<?php esc_html_e( 'Light mode', 'wpshadow' ); ?>
							</label><br>
							<label>
								<input type="radio" name="dark_mode_pref" value="dark" <?php checked( $dark_mode_pref, 'dark' ); ?>>
								<?php esc_html_e( 'Dark mode', 'wpshadow' ); ?>
							</label>
						</fieldset>
						<p class="description">
							<?php esc_html_e( 'Dark mode reduces eye strain in low-light environments and saves battery on OLED screens.', 'wpshadow' ); ?>
						</p>
					</td>
				</tr>
			</table>
			
			<p class="submit">
				<button type="submit" name="save_dark_mode" class="button button-primary">
					<?php esc_html_e( 'Save Changes', 'wpshadow' ); ?>
				</button>
			</p>
		</form>

		<?php
		if ( isset( $_POST['save_dark_mode'] ) && wp_verify_nonce( $_POST['wpshadow_dark_mode_nonce'], 'wpshadow_dark_mode' ) ) {
			$new_pref = isset( $_POST['dark_mode_pref'] ) ? sanitize_key( $_POST['dark_mode_pref'] ) : 'auto';
			update_user_meta( $user_id, 'wpshadow_dark_mode_preference', $new_pref );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Dark mode preference saved!', 'wpshadow' ) . '</p></div>';
		}
		?>
	</div>

	<div class="wpshadow-tool-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px;">
		<h2><?php esc_html_e( 'About Dark Mode', 'wpshadow' ); ?></h2>
		<p><strong><?php esc_html_e( 'Full dark mode implementation coming soon!', 'wpshadow' ); ?></strong></p>
		<p><?php esc_html_e( 'This will apply dark styling to WPShadow admin pages. Currently, preferences are saved but the visual theme is not yet applied.', 'wpshadow' ); ?></p>
	</div>
</div>
