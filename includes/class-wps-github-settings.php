<?php
/**
 * GitHub Updates Settings Handler
 *
 * Manages GitHub personal access token configuration for private repository updates.
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
 * WPS_GitHub_Settings Class
 *
 * Manages GitHub personal access token configuration and settings UI.
 */
class WPS_GitHub_Settings {

	/**
	 * Initialize GitHub settings.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'handle_token_submission' ) );
	}

	/**
	 * Handle GitHub token settings submission
	 *
	 * @return void
	 */
	public static function handle_token_submission(): void {
	// Only process on settings page.
	if ( ! isset( $_GET['page'] ) || 'wp-support' !== $_GET['page'] ) {
		return;
	}

	if ( ! isset( $_GET['WPS_tab'] ) || 'github-updates' !== $_GET['WPS_tab'] ) {
		return;
	}

	// Check POST data for token submission.
	if ( ! isset( $_POST['WPS_github_action'] ) || 'save_token' !== $_POST['WPS_github_action'] ) {
		return;
	}

	// Verify nonce and capability.
	if ( ! isset( $_POST['WPS_github_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['WPS_github_nonce'] ) ), 'WPS_github_token_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'plugin-wp-support-thisismyurl' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to manage settings.', 'plugin-wp-support-thisismyurl' ) );
	}

	// Get token from POST.
	$token = \WPS\CoreSupport\wps_get_post_text( 'WPS_github_token' );

	if ( ! empty( $token ) ) {
		// Validate token format (GitHub tokens are typically 40+ chars).
		if ( strlen( $token ) < 20 ) {
			wp_safe_redirect( add_query_arg( 'WPS_github_error', 'invalid_token_format', wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			exit;
		}

		// Save token.
		update_option( 'wps_github_token', $token );

		// Clear update cache to force fresh check.
		WPS_GitHub_Updater::clear_cache();

		wp_safe_redirect( add_query_arg( 'WPS_github_saved', '1', wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		exit;
	} else {
		// Clear token if empty.
		delete_option( 'wps_github_token' );
		WPS_GitHub_Updater::clear_cache();

		wp_safe_redirect( add_query_arg( 'WPS_github_cleared', '1', wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		exit;
	}
}

	/**
	 * Render GitHub Updates settings panel
	 *
	 * @return void
	 */
	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
		}

	$token_set = ! empty( get_option( 'wps_github_token' ) );
	$saved     = isset( $_GET['WPS_github_saved'] );
	$cleared   = isset( $_GET['WPS_github_cleared'] );
	$error     = isset( $_GET['WPS_github_error'] ) ? sanitize_text_field( wp_unslash( $_GET['WPS_github_error'] ) ) : '';
	?>
	<div class="wrap wps-github-settings">
		<h1><?php esc_html_e( 'GitHub Updates', 'plugin-wp-support-thisismyurl' ); ?></h1>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'GitHub token saved successfully.', 'plugin-wp-support-thisismyurl' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( $cleared ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'GitHub token cleared.', 'plugin-wp-support-thisismyurl' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( 'invalid_token_format' === $error ) : ?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'Invalid token format. GitHub tokens must be at least 20 characters long.', 'plugin-wp-support-thisismyurl' ); ?></p>
			</div>
		<?php endif; ?>

		<div class="card" style="max-width: 800px; margin-top: 20px;">
			<h2 style="margin-top: 0;"><?php esc_html_e( 'Automatic Updates from GitHub', 'plugin-wp-support-thisismyurl' ); ?></h2>

			<p>
				<?php esc_html_e( 'WordPress Support checks for updates from the private GitHub repository every 12 hours. Configure a personal access token to improve update reliability and rate limits.', 'plugin-wp-support-thisismyurl' ); ?>
			</p>

			<div class="wps-info-box" style="background: #f0f6fc; border-left: 4px solid #0969da; padding: 12px; margin: 16px 0; border-radius: 4px;">
				<p style="margin: 0;">
					<strong><?php esc_html_e( 'Update Status:', 'plugin-wp-support-thisismyurl' ); ?></strong>
					<?php if ( $token_set ) : ?>
						<span style="color: #28a745;">✓ <?php esc_html_e( 'GitHub token configured', 'plugin-wp-support-thisismyurl' ); ?></span>
					<?php else : ?>
						<span style="color: #ffc107;">⚠ <?php esc_html_e( 'No token configured (updates work but with rate limits)', 'plugin-wp-support-thisismyurl' ); ?></span>
					<?php endif; ?>
				</p>
			</div>

			<h3><?php esc_html_e( 'GitHub Personal Access Token', 'plugin-wp-support-thisismyurl' ); ?></h3>

			<p>
				<?php esc_html_e( 'A GitHub personal access token allows WordPress to access the private repository reliably. This is optional but recommended.', 'plugin-wp-support-thisismyurl' ); ?>
			</p>

			<ol style="margin-left: 20px;">
				<li>
					<?php
					echo wp_kses_post(
						sprintf(
							__( 'Visit <a href="%s" target="_blank" rel="noopener noreferrer">GitHub Personal Access Tokens</a>', 'plugin-wp-support-thisismyurl' ),
							'https://github.com/settings/tokens'
						)
					);
					?>
				</li>
				<li><?php esc_html_e( 'Click "Generate new token" (classic token)', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><?php esc_html_e( 'Name: "WordPress Support Updates"', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><?php esc_html_e( 'Select only the "repo" scope (full control of private repositories)', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><?php esc_html_e( 'Generate and copy the token', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><?php esc_html_e( 'Paste the token below and save', 'plugin-wp-support-thisismyurl' ); ?></li>
			</ol>

			<form method="post" action="">
				<?php wp_nonce_field( 'WPS_github_token_nonce', 'WPS_github_nonce' ); ?>
				<input type="hidden" name="WPS_github_action" value="save_token" />

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="WPS_github_token"><?php esc_html_e( 'Personal Access Token', 'plugin-wp-support-thisismyurl' ); ?></label>
						</th>
						<td>
							<input
								type="password"
								name="WPS_github_token"
								id="WPS_github_token"
								class="large-text"
								placeholder="ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
								value="<?php echo $token_set ? '••••••••••••••••' : ''; ?>"
								<?php echo $token_set ? 'title="' . esc_attr__( 'Token is configured (shown as dots for security)', 'plugin-wp-support-thisismyurl' ) . '"' : ''; ?>
							/>
							<p class="description">
								<?php esc_html_e( 'Your token will be stored securely and never transmitted except to GitHub API.', 'plugin-wp-support-thisismyurl' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button( esc_html__( 'Save Token', 'plugin-wp-support-thisismyurl' ), 'primary', 'submit', true ); ?>
			</form>

			<?php if ( $token_set ) : ?>
				<form method="post" action="" style="margin-top: 20px;">
					<?php wp_nonce_field( 'WPS_github_token_nonce', 'WPS_github_nonce' ); ?>
					<input type="hidden" name="WPS_github_action" value="save_token" />
					<input type="hidden" name="WPS_github_token" value="" />
					<?php submit_button( esc_html__( 'Clear Token', 'plugin-wp-support-thisismyurl' ), 'secondary', 'submit', true ); ?>
				</form>
			<?php endif; ?>

			<hr style="margin: 24px 0;" />

			<h3><?php esc_html_e( 'How It Works', 'plugin-wp-support-thisismyurl' ); ?></h3>
			<ul style="margin-left: 20px;">
				<li><?php esc_html_e( 'WordPress checks for updates every 12 hours', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><?php esc_html_e( 'We fetch the latest release from GitHub API', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><?php esc_html_e( 'Updates appear in the Plugins dashboard automatically', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><?php esc_html_e( 'One-click updates work just like official WordPress.org plugins', 'plugin-wp-support-thisismyurl' ); ?></li>
			</ul>

			<p class="description">
				<?php esc_html_e( 'Note: Make sure your GitHub repository has official releases tagged. Updates are drawn from "Releases" on GitHub, not commits.', 'plugin-wp-support-thisismyurl' ); ?>
			</p>
		</div>
	</div>
	<?php
	}
}
