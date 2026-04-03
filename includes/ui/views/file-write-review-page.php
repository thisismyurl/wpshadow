<?php
/**
 * File Write Review Page — View Template
 *
 * Displays pending file-write treatments with before/after diff preview,
 * backup/restore controls, and a gated Apply flow that requires the admin to
 * read and acknowledge SFTP recovery instructions before any file is touched.
 *
 * Variables available in scope (set by File_Write_Review_Page::render()):
 *   $pending  array[]  List of treatment info arrays from File_Write_Registry.
 *
 * @package WPShadow
 * @since 0.6093.1300
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Admin\File_Write_Trust;

// Bail if somehow rendered outside the review page context.
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

$dashboard_url = admin_url( 'admin.php?page=wpshadow' );
?>
<div class="wrap wpshadow-file-review-wrap">

	<!-- Page header -->
	<div class="wpshadow-page-header wps-file-review-header">
		<a href="<?php echo esc_url( $dashboard_url ); ?>" class="wps-file-review-back-link">
			&larr; <?php esc_html_e( 'Back to Dashboard', 'wpshadow' ); ?>
		</a>
	</div>

	<h1 class="wps-file-review-title"><?php esc_html_e( 'Review Proposed File Changes', 'wpshadow' ); ?></h1>
	<p class="description wps-file-review-description">
		<?php esc_html_e( 'WPShadow would like to make the following changes to your site\'s system files. Review each proposed change, run a dry-run preview, create a backup, and apply when you\'re ready. No file is modified until you explicitly approve it.', 'wpshadow' ); ?>
	</p>

	<?php if ( empty( $pending ) ) : ?>

		<!-- Empty state -->
		<div class="notice notice-success wps-file-review-empty-notice">
			<p>
				<strong><?php esc_html_e( 'All clear!', 'wpshadow' ); ?></strong>
				<?php esc_html_e( 'There are no pending file-write changes to review at this time.', 'wpshadow' ); ?>
			</p>
		</div>

	<?php else : ?>

		<!-- Treatment cards -->
		<?php foreach ( $pending as $treatment ) :
			$finding_id        = esc_attr( $treatment['finding_id'] );
			$file_path         = $treatment['target_file'];
			$file_label        = $treatment['file_label'];
			$change_summary    = $treatment['change_summary'];
			$snippet           = $treatment['snippet'];
			$sftp_instructions = $treatment['sftp_instructions'];
			$file_exists       = file_exists( $file_path );
			$file_readable     = $file_exists && is_readable( $file_path );
			$file_writable     = $file_exists && is_writable( $file_path );

			// Backup state.
			$backup_key     = 'wpshadow_file_backup_' . md5( $file_path );
			$backup_data    = get_option( $backup_key, null );
			$has_backup     = is_array( $backup_data ) && ! empty( $backup_data['content'] );
			$backup_at      = $has_backup ? (int) $backup_data['created_at'] : 0;

			// Trust state.
			$needs_warning  = File_Write_Trust::needs_warning( $file_path );
			?>
			<div class="wpshadow-file-review-card wps-file-review-card"
			     id="wpshadow-review-card-<?php echo $finding_id; ?>"
			     data-finding-id="<?php echo $finding_id; ?>"
			     data-file-path="<?php echo esc_attr( $file_path ); ?>">

				<!-- Card header -->
				<div class="wps-file-review-card-header">
					<div>
						<h2 class="wps-file-review-card-title">
							<?php echo esc_html( $change_summary ); ?>
						</h2>
						<p class="wps-file-review-path">
							<strong><?php esc_html_e( 'Target file:', 'wpshadow' ); ?></strong>
							<code><?php echo esc_html( $file_path ); ?></code>
						</p>
					</div>
					<span class="wpshadow-risk-badge wps-file-review-risk">
						⚠ <?php esc_html_e( 'File Write Required', 'wpshadow' ); ?>
					</span>
				</div>

				<!-- File status row -->
				<div class="wps-file-review-status-row">
					<?php if ( ! $file_exists ) : ?>
						<span class="wps-file-review-status wps-file-review-status--error">
							✗ <?php esc_html_e( 'File not found', 'wpshadow' ); ?>
						</span>
					<?php elseif ( ! $file_readable ) : ?>
						<span class="wps-file-review-status wps-file-review-status--error">
							✗ <?php esc_html_e( 'File not readable', 'wpshadow' ); ?>
						</span>
					<?php elseif ( ! $file_writable ) : ?>
						<span class="wps-file-review-status wps-file-review-status--error">
							✗ <?php esc_html_e( 'File not writable — check permissions', 'wpshadow' ); ?>
						</span>
					<?php else : ?>
						<span class="wps-file-review-status wps-file-review-status--success">
							✓ <?php esc_html_e( 'File accessible', 'wpshadow' ); ?>
						</span>
					<?php endif; ?>

					<?php if ( $has_backup ) : ?>
						<span class="wpshadow-backup-status wps-file-review-status wps-file-review-status--success">
							✓ <?php
							/* translators: %s: human-readable date */
							printf(
								esc_html__( 'Backup created %s', 'wpshadow' ),
								esc_html( human_time_diff( $backup_at, time() ) . ' ' . __( 'ago', 'wpshadow' ) )
							);
							?>
						</span>
					<?php else : ?>
						<span class="wpshadow-backup-status wps-file-review-status wps-file-review-status--warning">
							⚠ <?php esc_html_e( 'No backup yet', 'wpshadow' ); ?>
						</span>
					<?php endif; ?>
				</div>

				<!-- Proposed change snippet -->
				<div class="wps-file-review-section">
					<h3 class="wps-file-review-section-title"><?php esc_html_e( 'Proposed Change', 'wpshadow' ); ?></h3>
					<pre class="wps-file-review-snippet"><?php echo esc_html( $snippet ); ?></pre>
					<p class="wps-file-review-helptext">
						<?php esc_html_e( 'This is the exact content that will be written to the file. Nothing else will be changed.', 'wpshadow' ); ?>
					</p>
				</div>

				<!-- Dry-run diff area (hidden until dry-run runs) -->
				<div class="wpshadow-diff-area wps-file-review-diff-area"
				     id="wpshadow-diff-<?php echo $finding_id; ?>">
					<h3 class="wps-file-review-section-title"><?php esc_html_e( 'Dry-Run Preview', 'wpshadow' ); ?></h3>
					<div class="wpshadow-diff-inner wps-file-review-diff-inner">
						<!-- Populated by JS -->
					</div>
				</div>

				<!-- Action buttons row -->
				<div class="wps-file-review-actions">

					<!-- Dry run -->
					<button type="button"
					        class="button wpshadow-btn-dry-run"
					        data-finding-id="<?php echo $finding_id; ?>"
					        <?php disabled( ! $file_readable ); ?>>
						<?php esc_html_e( 'Preview Changes', 'wpshadow' ); ?>
					</button>

					<!-- Backup -->
					<button type="button"
					        class="button wpshadow-btn-backup"
					        data-finding-id="<?php echo $finding_id; ?>"
					        data-file-path="<?php echo esc_attr( $file_path ); ?>"
					        <?php disabled( ! $file_readable ); ?>>
						<?php $has_backup ? esc_html_e( 'Refresh Backup', 'wpshadow' ) : esc_html_e( 'Create Backup', 'wpshadow' ); ?>
					</button>

					<!-- Restore (only visible when backup exists) -->
					<button type="button"
					        class="button wpshadow-btn-restore wps-file-review-restore<?php echo $has_backup ? '' : ' wps-file-review-restore--hidden'; ?>"
					        data-finding-id="<?php echo $finding_id; ?>"
					        data-file-path="<?php echo esc_attr( $file_path ); ?>">
						<?php esc_html_e( 'Restore from Backup', 'wpshadow' ); ?>
					</button>

					<div class="wps-file-review-spacer"></div><!-- spacer -->

					<!-- Apply (triggers SFTP modal if warning needed) -->
					<button type="button"
					        class="button button-primary wpshadow-btn-apply"
					        data-finding-id="<?php echo $finding_id; ?>"
					        data-file-path="<?php echo esc_attr( $file_path ); ?>"
					        data-needs-warning="<?php echo $needs_warning ? '1' : '0'; ?>"
					        data-sftp-instructions="<?php echo esc_attr( $sftp_instructions ); ?>"
					        data-file-label="<?php echo esc_attr( $file_label ); ?>"
					        <?php disabled( ! $file_writable ); ?>>
						<?php esc_html_e( 'Apply Fix', 'wpshadow' ); ?>
					</button>

				</div>

				<!-- Inline status message area -->
				<div class="wpshadow-card-status wps-file-review-status-box"
				     id="wpshadow-status-<?php echo $finding_id; ?>">
				</div>

			</div><!-- /.wpshadow-file-review-card -->
		<?php endforeach; ?>

	<?php endif; ?>

	<!-- Trust settings box -->
	<?php if ( ! empty( $pending ) ) : ?>
	<div class="wps-file-review-preferences">
		<h3 class="wps-file-review-section-title"><?php esc_html_e( 'Warning Preferences', 'wpshadow' ); ?></h3>
		<p class="wps-file-review-path">
			<?php esc_html_e( 'Once you are comfortable with the file-write process, you can skip the SFTP acknowledgment step for future fixes.', 'wpshadow' ); ?>
		</p>
		<label class="wps-file-review-pref-label">
			<input type="checkbox" id="wpshadow-trust-all" <?php checked( File_Write_Trust::is_all_trusted() ); ?>>
			<?php esc_html_e( 'Skip SFTP acknowledgment for all future file-write fixes (global)', 'wpshadow' ); ?>
		</label>
		<p class="wps-file-review-pref-note">
			<?php esc_html_e( 'Per-file trust is also available — select it in the SFTP acknowledgment dialog when applying a specific fix.', 'wpshadow' ); ?>
		</p>
	</div>
	<?php endif; ?>

</div><!-- /.wrap -->

<!-- =========================================================
     SFTP Acknowledgment Static Modal
     Opened by JS when Apply is clicked and needs_warning=1.
     The JS populates #wpshadow-sftp-modal-instructions before opening.
     ========================================================= -->
<div id="wpshadow-sftp-modal"
     class="wpshadow-static-modal wps-file-review-modal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="wpshadow-sftp-modal-title">

	<!-- Overlay -->
	<div class="wpshadow-modal-overlay wps-file-review-modal-overlay"></div>

	<!-- Dialog -->
	<div class="wps-file-review-modal-dialog">

		<!-- Header -->
		<div class="wps-file-review-modal-header">
			<span class="wps-file-review-modal-icon">⚠</span>
			<div>
				<h2 id="wpshadow-sftp-modal-title" class="wps-file-review-modal-title">
					<?php esc_html_e( 'Before You Proceed: Recovery Instructions', 'wpshadow' ); ?>
				</h2>
				<p class="wps-file-review-modal-subtitle">
					<?php esc_html_e( 'Please read and store the following SFTP recovery steps in case anything goes wrong.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>

		<!-- Body -->
		<div class="wps-file-review-modal-body">

			<div class="wps-file-review-modal-warning">
				<strong><?php esc_html_e( 'Why is this important?', 'wpshadow' ); ?></strong>
				<?php esc_html_e( 'If the change causes an issue (e.g. a white screen or redirect loop), you may not be able to access WordPress to undo it. The SFTP method below lets you revert the file even without WordPress running.', 'wpshadow' ); ?>
			</div>

			<h3 class="wps-file-review-section-title">
				<?php esc_html_e( 'SFTP Recovery Instructions', 'wpshadow' ); ?>
			</h3>

			<div id="wpshadow-sftp-modal-file-label" class="wps-file-review-modal-file-label">
				<!-- Populated by JS -->
			</div>

			<ol id="wpshadow-sftp-modal-instructions" class="wps-file-review-modal-instructions">
				<!-- Populated by JS -->
			</ol>

			<div class="wps-file-review-modal-fallback">
				<strong><?php esc_html_e( 'If you use cPanel File Manager:', 'wpshadow' ); ?></strong><br>
				<?php esc_html_e( 'Log in to your hosting → cPanel → File Manager → navigate to the file → right-click → Edit → paste the original content → Save.', 'wpshadow' ); ?>
			</div>

			<!-- Acknowledgment checkboxes -->
			<div class="wps-file-review-modal-acks">
				<label class="wps-file-review-modal-ack">
					<input type="checkbox" id="wpshadow-ack-read" class="wps-file-review-modal-ack-input">
					<span><?php esc_html_e( 'I have read these recovery instructions and stored them somewhere safe (e.g. a password manager, printed copy, or a text file outside this site).', 'wpshadow' ); ?></span>
				</label>

				<label class="wps-file-review-modal-ack">
					<input type="checkbox" id="wpshadow-ack-file-trust" class="wps-file-review-modal-ack-input">
					<span id="wpshadow-ack-file-trust-label">
						<?php esc_html_e( 'Skip this warning for this file in future (per-file trust)', 'wpshadow' ); ?>
					</span>
				</label>

				<label class="wps-file-review-modal-ack">
					<input type="checkbox" id="wpshadow-ack-all-trust" class="wps-file-review-modal-ack-input">
					<span><?php esc_html_e( 'Skip SFTP acknowledgment for all future file-write fixes (global trust)', 'wpshadow' ); ?></span>
				</label>
			</div>
		</div>

		<!-- Footer -->
		<div class="wps-file-review-modal-footer">
			<button type="button"
			        id="wpshadow-sftp-modal-cancel"
			        class="button">
				<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
			</button>
			<button type="button"
			        id="wpshadow-sftp-modal-confirm"
			        class="button button-primary"
			        disabled>
				<?php esc_html_e( 'I Understand — Apply Fix', 'wpshadow' ); ?>
			</button>
		</div>

	</div>
</div><!-- /#wpshadow-sftp-modal -->
