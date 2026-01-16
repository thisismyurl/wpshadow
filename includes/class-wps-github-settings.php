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

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_GitHub_Settings Class
 *
 * Manages GitHub personal access token configuration and settings UI.
 */
class WPSHADOW_GitHub_Settings {

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
	if ( ! isset( $_GET['page'] ) || 'wpshadow' !== $_GET['page'] ) {
		return;
	}

	if ( ! isset( $_GET['wpshadow_tab'] ) || 'github-updates' !== $_GET['wpshadow_tab'] ) {
		return;
	}

	// Check POST data for token submission.
	if ( ! isset( $_POST['wpshadow_github_action'] ) || 'save_token' !== $_POST['wpshadow_github_action'] ) {
		return;
	}

	// Verify nonce and capability.
	if ( ! isset( $_POST['wpshadow_github_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpshadow_github_nonce'] ) ), 'wpshadow_github_token_nonce' ) ) {
		wp_die( esc_html__( 'Your session expired. Please refresh and try again.', 'plugin-wpshadow' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to manage settings.', 'plugin-wpshadow' ) );
	}

	// Get token from POST.
	$token = \WPShadow\WPSHADOW_get_post_text( 'wpshadow_github_token' );

	if ( ! empty( $token ) ) {
		// Validate token format (GitHub tokens are typically 40+ chars).
		if ( strlen( $token ) < 20 ) {
			wp_safe_redirect( add_query_arg( 'wpshadow_github_error', 'invalid_token_format', wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			exit;
		}

		// Save token.
		update_option( 'wpshadow_github_token', $token );

		// Clear update cache to force fresh check.
		WPSHADOW_GitHub_Updater::clear_cache();

		wp_safe_redirect( add_query_arg( 'wpshadow_github_saved', '1', wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		exit;
	} else {
		// Clear token if empty.
		delete_option( 'wpshadow_github_token' );
		WPSHADOW_GitHub_Updater::clear_cache();

		wp_safe_redirect( add_query_arg( 'wpshadow_github_cleared', '1', wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
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
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
		}

	$token_set = ! empty( get_option( 'wpshadow_github_token' ) );
	$saved     = isset( $_GET['wpshadow_github_saved'] );
	$cleared   = isset( $_GET['wpshadow_github_cleared'] );
	$error     = isset( $_GET['wpshadow_github_error'] ) ? sanitize_text_field( wp_unslash( $_GET['wpshadow_github_error'] ) ) : '';
	?>
	<div class="wrap wps-github-settings">
		<h1><?php esc_html_e( 'GitHub Updates', 'plugin-wpshadow' ); ?></h1>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'GitHub token saved successfully.', 'plugin-wpshadow' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( $cleared ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'GitHub token cleared.', 'plugin-wpshadow' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( 'invalid_token_format' === $error ) : ?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'Invalid token format. GitHub tokens must be at least 20 characters long.', 'plugin-wpshadow' ); ?></p>
			</div>
		<?php endif; ?>

		<div class="card" style="max-width: 800px; margin-top: 20px;">
			<h2 style="margin-top: 0;"><?php esc_html_e( 'Automatic Updates from GitHub', 'plugin-wpshadow' ); ?></h2>

			<p>
				<?php esc_html_e( 'WPShadow checks for updates from the private GitHub repository every 12 hours. Configure a personal access token to improve update reliability and rate limits.', 'plugin-wpshadow' ); ?>
			</p>

			<div class="wps-info-box" style="background: #f0f6fc; border-left: 4px solid #0969da; padding: 12px; margin: 16px 0; border-radius: 4px;">
				<p style="margin: 0;">
					<strong><?php esc_html_e( 'Update Status:', 'plugin-wpshadow' ); ?></strong>
					<?php if ( $token_set ) : ?>
						<span style="color: #28a745;">✓ <?php esc_html_e( 'GitHub token configured', 'plugin-wpshadow' ); ?></span>
					<?php else : ?>
						<span style="color: #ffc107;">⚠ <?php esc_html_e( 'No token configured (updates work but with rate limits)', 'plugin-wpshadow' ); ?></span>
					<?php endif; ?>
				</p>
			</div>

			<h3><?php esc_html_e( 'GitHub Personal Access Token', 'plugin-wpshadow' ); ?></h3>

			<p>
				<?php esc_html_e( 'A GitHub personal access token allows WordPress to access the private repository reliably. This is optional but recommended.', 'plugin-wpshadow' ); ?>
			</p>

			<ol style="margin-left: 20px;">
				<li>
					<?php
					echo wp_kses_post(
						sprintf(
							__( 'Visit <a href="%s" target="_blank" rel="noopener noreferrer">GitHub Personal Access Tokens</a>', 'plugin-wpshadow' ),
							'https://github.com/settings/tokens'
						)
					);
					?>
				</li>
				<li><?php esc_html_e( 'Click "Generate new token" (classic token)', 'plugin-wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Name: "WPShadow Updates"', 'plugin-wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Select only the "repo" scope (full control of private repositories)', 'plugin-wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Generate and copy the token', 'plugin-wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Paste the token below and save', 'plugin-wpshadow' ); ?></li>
			</ol>

			<form method="post" action="">
				<?php wp_nonce_field( 'wpshadow_github_token_nonce', 'wpshadow_github_nonce' ); ?>
				<input type="hidden" name="wpshadow_github_action" value="save_token" />

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="wpshadow_github_token"><?php esc_html_e( 'Personal Access Token', 'plugin-wpshadow' ); ?></label>
						</th>
						<td>
							<input
								type="password"
								name="wpshadow_github_token"
								id="wpshadow_github_token"
								class="large-text"
								placeholder="ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
								value="<?php echo $token_set ? '••••••••••••••••' : ''; ?>"
								<?php echo $token_set ? 'title="' . esc_attr__( 'Token is configured (shown as dots for security)', 'plugin-wpshadow' ) . '"' : ''; ?>
							/>
							<p class="description">
								<?php esc_html_e( 'Your token will be stored securely and never transmitted except to GitHub API.', 'plugin-wpshadow' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button( esc_html__( 'Save Token', 'plugin-wpshadow' ), 'primary', 'submit', true ); ?>
			</form>

			<?php if ( $token_set ) : ?>
				<form method="post" action="" style="margin-top: 20px;">
					<?php wp_nonce_field( 'wpshadow_github_token_nonce', 'wpshadow_github_nonce' ); ?>
					<input type="hidden" name="wpshadow_github_action" value="save_token" />
					<input type="hidden" name="wpshadow_github_token" value="" />
					<?php submit_button( esc_html__( 'Clear Token', 'plugin-wpshadow' ), 'secondary', 'submit', true ); ?>
				</form>
			<?php endif; ?>

			<hr style="margin: 24px 0;" />

			<h3><?php esc_html_e( 'How It Works', 'plugin-wpshadow' ); ?></h3>
			<ul style="margin-left: 20px;">
				<li><?php esc_html_e( 'WordPress checks for updates every 12 hours', 'plugin-wpshadow' ); ?></li>
				<li><?php esc_html_e( 'We fetch the latest release from GitHub API', 'plugin-wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Updates appear in the Plugins dashboard automatically', 'plugin-wpshadow' ); ?></li>
				<li><?php esc_html_e( 'One-click updates work just like official WordPress.org plugins', 'plugin-wpshadow' ); ?></li>
			</ul>

			<p class="description">
				<?php esc_html_e( 'Note: Make sure your GitHub repository has official releases tagged. Updates are drawn from "Releases" on GitHub, not commits.', 'plugin-wpshadow' ); ?>
			</p>
		</div>
	</div>
	<?php
	}
}
