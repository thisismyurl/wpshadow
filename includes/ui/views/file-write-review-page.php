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
	<div class="wpshadow-page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
		<a href="<?php echo esc_url( $dashboard_url ); ?>" style="text-decoration:none;color:#666;font-size:13px;">
			&larr; <?php esc_html_e( 'Back to Dashboard', 'wpshadow' ); ?>
		</a>
	</div>

	<h1 style="margin-bottom:4px;"><?php esc_html_e( 'Review Proposed File Changes', 'wpshadow' ); ?></h1>
	<p class="description" style="font-size:14px;color:#555;margin-bottom:28px;">
		<?php esc_html_e( 'WPShadow would like to make the following changes to your site\'s system files. Review each proposed change, run a dry-run preview, create a backup, and apply when you\'re ready. No file is modified until you explicitly approve it.', 'wpshadow' ); ?>
	</p>

	<?php if ( empty( $pending ) ) : ?>

		<!-- Empty state -->
		<div class="notice notice-success" style="padding:16px 20px;">
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
			<div class="wpshadow-file-review-card"
			     id="wpshadow-review-card-<?php echo $finding_id; ?>"
			     data-finding-id="<?php echo $finding_id; ?>"
			     data-file-path="<?php echo esc_attr( $file_path ); ?>"
			     style="background:#fff;border:1px solid #ddd;border-radius:6px;padding:24px;margin-bottom:28px;box-shadow:0 1px 3px rgba(0,0,0,.06);">

				<!-- Card header -->
				<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
					<div>
						<h2 style="margin:0 0 6px;font-size:18px;">
							<?php echo esc_html( $change_summary ); ?>
						</h2>
						<p style="margin:0;color:#555;font-size:13px;">
							<strong><?php esc_html_e( 'Target file:', 'wpshadow' ); ?></strong>
							<code style="background:#f5f5f5;padding:2px 6px;border-radius:3px;"><?php echo esc_html( $file_path ); ?></code>
						</p>
					</div>
					<span class="wpshadow-risk-badge"
					      style="display:inline-block;padding:4px 10px;background:#fff3cd;color:#856404;border:1px solid #ffc107;border-radius:20px;font-size:12px;font-weight:600;white-space:nowrap;">
						⚠ <?php esc_html_e( 'File Write Required', 'wpshadow' ); ?>
					</span>
				</div>

				<!-- File status row -->
				<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
					<?php if ( ! $file_exists ) : ?>
						<span style="color:#d63638;font-size:13px;">
							✗ <?php esc_html_e( 'File not found', 'wpshadow' ); ?>
						</span>
					<?php elseif ( ! $file_readable ) : ?>
						<span style="color:#d63638;font-size:13px;">
							✗ <?php esc_html_e( 'File not readable', 'wpshadow' ); ?>
						</span>
					<?php elseif ( ! $file_writable ) : ?>
						<span style="color:#d63638;font-size:13px;">
							✗ <?php esc_html_e( 'File not writable — check permissions', 'wpshadow' ); ?>
						</span>
					<?php else : ?>
						<span style="color:#1e7e34;font-size:13px;">
							✓ <?php esc_html_e( 'File accessible', 'wpshadow' ); ?>
						</span>
					<?php endif; ?>

					<?php if ( $has_backup ) : ?>
						<span class="wpshadow-backup-status" style="color:#1e7e34;font-size:13px;">
							✓ <?php
							/* translators: %s: human-readable date */
							printf(
								esc_html__( 'Backup created %s', 'wpshadow' ),
								esc_html( human_time_diff( $backup_at, time() ) . ' ' . __( 'ago', 'wpshadow' ) )
							);
							?>
						</span>
					<?php else : ?>
						<span class="wpshadow-backup-status" style="color:#856404;font-size:13px;">
							⚠ <?php esc_html_e( 'No backup yet', 'wpshadow' ); ?>
						</span>
					<?php endif; ?>
				</div>

				<!-- Proposed change snippet -->
				<div style="margin-bottom:20px;">
					<h3 style="font-size:14px;font-weight:600;margin:0 0 8px;"><?php esc_html_e( 'Proposed Change', 'wpshadow' ); ?></h3>
					<pre style="background:#f8f8f8;border:1px solid #e2e2e2;border-left:4px solid #2271b1;border-radius:4px;padding:14px 16px;font-size:12px;overflow-x:auto;white-space:pre-wrap;word-break:break-word;margin:0;"><?php echo esc_html( $snippet ); ?></pre>
					<p style="font-size:12px;color:#666;margin:6px 0 0;">
						<?php esc_html_e( 'This is the exact content that will be written to the file. Nothing else will be changed.', 'wpshadow' ); ?>
					</p>
				</div>

				<!-- Dry-run diff area (hidden until dry-run runs) -->
				<div class="wpshadow-diff-area"
				     id="wpshadow-diff-<?php echo $finding_id; ?>"
				     style="display:none;margin-bottom:20px;">
					<h3 style="font-size:14px;font-weight:600;margin:0 0 8px;"><?php esc_html_e( 'Dry-Run Preview', 'wpshadow' ); ?></h3>
					<div class="wpshadow-diff-inner"
					     style="background:#fafafa;border:1px solid #e2e2e2;border-radius:4px;font-size:12px;font-family:monospace;overflow-x:auto;">
						<!-- Populated by JS -->
					</div>
				</div>

				<!-- Action buttons row -->
				<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;border-top:1px solid #eee;padding-top:18px;">

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
					        class="button wpshadow-btn-restore"
					        data-finding-id="<?php echo $finding_id; ?>"
					        data-file-path="<?php echo esc_attr( $file_path ); ?>"
					        style="<?php echo $has_backup ? '' : 'display:none;'; ?>color:#d63638;">
						<?php esc_html_e( 'Restore from Backup', 'wpshadow' ); ?>
					</button>

					<div style="flex:1;"></div><!-- spacer -->

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
				<div class="wpshadow-card-status"
				     id="wpshadow-status-<?php echo $finding_id; ?>"
				     style="display:none;margin-top:14px;padding:10px 14px;border-radius:4px;font-size:13px;">
				</div>

			</div><!-- /.wpshadow-file-review-card -->
		<?php endforeach; ?>

	<?php endif; ?>

	<!-- Trust settings box -->
	<?php if ( ! empty( $pending ) ) : ?>
	<div style="background:#f9f9f9;border:1px solid #ddd;border-radius:6px;padding:20px;margin-top:8px;">
		<h3 style="font-size:14px;font-weight:600;margin:0 0 12px;"><?php esc_html_e( 'Warning Preferences', 'wpshadow' ); ?></h3>
		<p style="font-size:13px;color:#555;margin:0 0 14px;">
			<?php esc_html_e( 'Once you are comfortable with the file-write process, you can skip the SFTP acknowledgment step for future fixes.', 'wpshadow' ); ?>
		</p>
		<label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;margin-bottom:10px;">
			<input type="checkbox" id="wpshadow-trust-all" <?php checked( File_Write_Trust::is_all_trusted() ); ?>>
			<?php esc_html_e( 'Skip SFTP acknowledgment for all future file-write fixes (global)', 'wpshadow' ); ?>
		</label>
		<p style="font-size:12px;color:#888;margin:0;">
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
     class="wpshadow-static-modal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="wpshadow-sftp-modal-title"
     style="display:none;position:fixed;inset:0;z-index:100000;overflow-y:auto;">

	<!-- Overlay -->
	<div class="wpshadow-modal-overlay"
	     style="position:fixed;inset:0;background:rgba(0,0,0,.65);"></div>

	<!-- Dialog -->
	<div style="position:relative;z-index:1;max-width:680px;margin:40px auto;background:#fff;border-radius:8px;box-shadow:0 8px 40px rgba(0,0,0,.25);overflow:hidden;">

		<!-- Header -->
		<div style="background:#d63638;color:#fff;padding:20px 24px;display:flex;align-items:center;gap:12px;">
			<span style="font-size:22px;">⚠</span>
			<div>
				<h2 id="wpshadow-sftp-modal-title" style="margin:0;font-size:18px;color:#fff;">
					<?php esc_html_e( 'Before You Proceed: Recovery Instructions', 'wpshadow' ); ?>
				</h2>
				<p style="margin:4px 0 0;font-size:13px;opacity:.9;">
					<?php esc_html_e( 'Please read and store the following SFTP recovery steps in case anything goes wrong.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>

		<!-- Body -->
		<div style="padding:24px;">

			<div style="background:#fff8e1;border:1px solid #ffe082;border-radius:4px;padding:14px 16px;margin-bottom:20px;font-size:13px;">
				<strong><?php esc_html_e( 'Why is this important?', 'wpshadow' ); ?></strong>
				<?php esc_html_e( 'If the change causes an issue (e.g. a white screen or redirect loop), you may not be able to access WordPress to undo it. The SFTP method below lets you revert the file even without WordPress running.', 'wpshadow' ); ?>
			</div>

			<h3 style="font-size:14px;font-weight:600;margin:0 0 10px;">
				<?php esc_html_e( 'SFTP Recovery Instructions', 'wpshadow' ); ?>
			</h3>

			<div id="wpshadow-sftp-modal-file-label"
			     style="font-size:13px;color:#555;margin-bottom:12px;">
				<!-- Populated by JS -->
			</div>

			<ol id="wpshadow-sftp-modal-instructions"
			    style="font-size:13px;line-height:1.8;padding-left:20px;margin:0 0 20px;">
				<!-- Populated by JS -->
			</ol>

			<div style="background:#f5f5f5;border:1px solid #ddd;border-radius:4px;padding:12px 14px;font-size:12px;font-family:monospace;margin-bottom:20px;">
				<strong><?php esc_html_e( 'If you use cPanel File Manager:', 'wpshadow' ); ?></strong><br>
				<?php esc_html_e( 'Log in to your hosting → cPanel → File Manager → navigate to the file → right-click → Edit → paste the original content → Save.', 'wpshadow' ); ?>
			</div>

			<!-- Acknowledgment checkboxes -->
			<div style="border-top:1px solid #eee;padding-top:18px;">
				<label style="display:flex;align-items:flex-start;gap:10px;font-size:13px;cursor:pointer;margin-bottom:12px;line-height:1.5;">
					<input type="checkbox" id="wpshadow-ack-read" style="margin-top:2px;flex-shrink:0;">
					<span><?php esc_html_e( 'I have read these recovery instructions and stored them somewhere safe (e.g. a password manager, printed copy, or a text file outside this site).', 'wpshadow' ); ?></span>
				</label>

				<label style="display:flex;align-items:flex-start;gap:10px;font-size:13px;cursor:pointer;margin-bottom:12px;line-height:1.5;">
					<input type="checkbox" id="wpshadow-ack-file-trust" style="margin-top:2px;flex-shrink:0;">
					<span id="wpshadow-ack-file-trust-label">
						<?php esc_html_e( 'Skip this warning for this file in future (per-file trust)', 'wpshadow' ); ?>
					</span>
				</label>

				<label style="display:flex;align-items:flex-start;gap:10px;font-size:13px;cursor:pointer;margin-bottom:0;line-height:1.5;">
					<input type="checkbox" id="wpshadow-ack-all-trust" style="margin-top:2px;flex-shrink:0;">
					<span><?php esc_html_e( 'Skip SFTP acknowledgment for all future file-write fixes (global trust)', 'wpshadow' ); ?></span>
				</label>
			</div>
		</div>

		<!-- Footer -->
		<div style="background:#f9f9f9;border-top:1px solid #eee;padding:14px 24px;display:flex;justify-content:flex-end;gap:10px;">
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
