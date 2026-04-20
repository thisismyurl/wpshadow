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
 * @since 0.6095
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

use WPShadow\Admin\File_Write_Trust;

// Bail if somehow rendered outside the review page context.
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
?>

	.wps-file-review-list {
		display: grid;
		gap: 20px;
	}

	.wps-file-review-card {
		border: 1px solid #dfe6ee;
		border-radius: 16px;
		background: #fff;
		box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
		overflow: hidden;
		scroll-margin-top: 24px;
	}

	.wps-file-review-card:target {
		box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.14), 0 14px 34px rgba(37, 99, 235, 0.18);
		border-color: #93c5fd;
	}

	.wps-file-review-card--manual {
		background: linear-gradient(180deg, #fffdf8 0%, #ffffff 100%);
	}

	.wps-file-review-card-header {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 16px;
		padding: 22px 24px 18px;
		background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
		border-bottom: 1px solid #edf2f7;
	}

	.wps-file-review-card-title {
		margin: 0 0 8px;
		font-size: 20px;
		line-height: 1.3;
		color: #132238;
	}

	.wps-file-review-path {
		margin: 0;
		font-size: 13px;
		line-height: 1.6;
		color: #5b6f84;
	}

	.wps-file-review-path code,
	.wps-file-review-modal-file-label code {
		display: inline-block;
		padding: 3px 8px;
		border-radius: 8px;
		background: #f4f7fb;
		color: #17324c;
		font-size: 12px;
	}

	.wps-file-review-pill-group {
		display: flex;
		gap: 8px;
		flex-wrap: wrap;
		justify-content: flex-end;
	}

	.wps-file-review-pill {
		display: inline-flex;
		align-items: center;
		padding: 7px 12px;
		border-radius: 999px;
		font-size: 12px;
		font-weight: 700;
	}

	.wps-file-review-pill--success {
		background: #e9f8ef;
		color: #1f7a4f;
	}

	.wps-file-review-pill--manual {
		background: #fff2df;
		color: #a55a00;
	}

	.wps-file-review-pill--preview {
		background: #eef4fb;
		color: #204566;
	}

	.wps-file-review-empty-state {
		padding: 18px;
		border-radius: 16px;
		border: 1px dashed #d6e0eb;
		background: linear-gradient(180deg, #fbfdff 0%, #ffffff 100%);
	}

	.wps-file-review-status-row,
	.wps-file-review-actions,
	.wps-file-review-section,
	.wps-file-review-status-box,
	.wps-file-review-manual-alert,
	.wps-file-review-two-column {
		padding-left: 24px;
		padding-right: 24px;
	}

	.wps-file-review-status-row {
		display: flex;
		gap: 10px;
		flex-wrap: wrap;
		padding-top: 16px;
	}

	.wps-file-review-status {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 8px 12px;
		border-radius: 999px;
		font-size: 12px;
		font-weight: 600;
	}

	.wps-file-review-status--success {
		background: #edf9f1;
		color: #1f7a4f;
	}

	.wps-file-review-status--warning {
		background: #fff4e5;
		color: #9a5a00;
	}

	.wps-file-review-status--error {
		background: #fff0f0;
		color: #b42318;
	}

	.wps-file-review-section {
		padding-top: 18px;
	}

	.wps-file-review-section-title {
		margin: 0 0 10px;
		font-size: 15px;
		font-weight: 700;
		color: #17324c;
	}

	.wps-file-review-snippet {
		margin: 0;
		padding: 18px;
		border-radius: 14px;
		background: #0f172a;
		color: #dbeafe;
		font-size: 13px;
		line-height: 1.65;
		white-space: pre-wrap;
		word-break: break-word;
		overflow: auto;
	}

	.wps-file-review-helptext {
		margin: 10px 0 0;
		font-size: 13px;
		line-height: 1.6;
		color: #61768d;
	}

	.wps-file-review-diff-area {
		display: none;
		padding: 18px 24px 0;
	}

	.wps-file-review-diff-inner {
		border: 1px solid #dce5f0;
		border-radius: 14px;
		overflow: hidden;
		background: #fbfdff;
	}

	.wps-file-review-diff-table {
		width: 100%;
		border-collapse: collapse;
		font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
		font-size: 12px;
	}

	.wps-file-review-diff-prefix {
		width: 34px;
		padding: 10px 12px;
		text-align: center;
		color: #64748b;
		border-right: 1px solid #e5edf6;
	}

	.wps-file-review-diff-content {
		padding: 10px 12px;
		white-space: pre-wrap;
		word-break: break-word;
	}

	.wps-file-review-diff-row--add {
		background: #effcf3;
	}

	.wps-file-review-diff-row--remove {
		background: #fff1f2;
	}

	.wps-file-review-actions {
		display: flex;
		align-items: center;
		gap: 10px;
		flex-wrap: wrap;
		padding-top: 20px;
		padding-bottom: 20px;
	}

	.wps-file-review-spacer {
		flex: 1 1 auto;
	}

	.wps-file-review-status-box {
		display: none;
		padding-top: 0;
		padding-bottom: 22px;
		font-weight: 600;
	}

	.wps-file-review-status-box.is-visible {
		display: block;
	}

	.wps-file-review-status-box--success {
		color: #1f7a4f;
	}

	.wps-file-review-status-box--error {
		color: #b42318;
	}

	.wps-file-review-status-box--info {
		color: #205493;
	}

	.wps-file-review-manual-alert {
		margin: 18px 24px 0;
	}

	.wps-file-review-two-column {
		display: grid;
		grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
		gap: 20px;
		padding-bottom: 22px;
	}

	.wps-file-review-manual-steps {
		margin: 0;
		padding-left: 20px;
		color: #34495e;
		line-height: 1.7;
	}

	.wps-file-review-manual-steps li + li {
		margin-top: 8px;
	}

	.wps-file-review-preferences {
		background: #ffffff;
		border: 1px solid #dde7f1;
		border-radius: 16px;
		padding: 22px 24px;
	}

	.wps-file-review-pref-label {
		display: flex;
		align-items: flex-start;
		gap: 10px;
		font-weight: 600;
		color: #17324c;
	}

	.wps-file-review-pref-note {
		margin: 10px 0 0 28px;
		font-size: 13px;
		line-height: 1.6;
		color: #61768d;
	}

	@media (max-width: 900px) {
		.wps-file-review-two-column {
			grid-template-columns: 1fr;
		}

		.wps-file-review-card-header,
		.wps-file-review-section-heading,
		.wps-file-review-hero-top {
			flex-direction: column;
		}

		.wps-file-review-pill-group {
			justify-content: flex-start;
		}

		.wps-file-review-spacer {
			display: none;
		}
	}
</style>

<div class="wrap wpshadow-file-review-wrap">
	<div class="wps-file-review-shell">
		<div class="wps-file-review-hero">
			<div class="wps-file-review-hero-top">
				<div>
					<span class="wps-file-review-kicker"><?php esc_html_e( 'File Change Review', 'wpshadow' ); ?></span>
					<h1 class="wps-file-review-title"><?php esc_html_e( 'Review Proposed File Changes', 'wpshadow' ); ?></h1>
					<p class="wps-file-review-description"><?php esc_html_e( 'WPShadow has split these changes into two groups. The first group contains files WordPress can update directly in this beta because the site exposes direct filesystem access. The second group contains files that still need attention, but must be updated manually because WordPress cannot safely write them from inside the site.', 'wpshadow' ); ?></p>
				</div>
				<div class="wps-file-review-hero-actions">
					<?php if ( empty( $manual ) && ! $preview_manual_enabled ) : ?>
						<a href="<?php echo esc_url( $preview_manual_url ); ?>" class="wps-file-review-secondary-link"><?php esc_html_e( 'Preview a manual-only example', 'wpshadow' ); ?></a>
					<?php elseif ( $preview_manual_enabled ) : ?>
						<a href="<?php echo esc_url( $preview_exit_url ); ?>" class="wps-file-review-secondary-link"><?php esc_html_e( 'Exit preview mode', 'wpshadow' ); ?></a>
					<?php endif; ?>
					<a href="<?php echo esc_url( $guardian_url ); ?>" class="wps-file-review-back-link">&larr; <?php esc_html_e( 'Back to Guardian', 'wpshadow' ); ?></a>
				</div>
			</div>

			<div class="wps-file-review-stats">
				<div class="wps-file-review-stat">
					<span class="wps-file-review-stat-value"><?php echo (int) count( $actionable ); ?></span>
					<span class="wps-file-review-stat-label"><?php esc_html_e( 'Changes WPShadow can apply', 'wpshadow' ); ?></span>
				</div>
				<div class="wps-file-review-stat">
					<span class="wps-file-review-stat-value"><?php echo (int) count( $manual ); ?></span>
					<span class="wps-file-review-stat-label"><?php esc_html_e( 'Files you need to update yourself', 'wpshadow' ); ?></span>
				</div>
				<div class="wps-file-review-stat">
					<span class="wps-file-review-stat-value"><?php echo (int) count( $pending ); ?></span>
					<span class="wps-file-review-stat-label"><?php esc_html_e( 'Total file-based fixes waiting for review', 'wpshadow' ); ?></span>
				</div>
			</div>

			<div class="wps-file-review-flow">
				<div class="wps-file-review-flow-step">
					<span class="wps-file-review-flow-label">1</span>
					<strong><?php esc_html_e( 'Review the exact code', 'wpshadow' ); ?></strong>
					<p><?php esc_html_e( 'Every card shows the file path and the exact snippet WPShadow wants to add or change.', 'wpshadow' ); ?></p>
				</div>
				<div class="wps-file-review-flow-step">
					<span class="wps-file-review-flow-label">2</span>
					<strong><?php esc_html_e( 'Apply or update manually', 'wpshadow' ); ?></strong>
					<p><?php esc_html_e( 'Writable files can be backed up and applied here. Locked files include plain-English instructions for doing it yourself.', 'wpshadow' ); ?></p>
				</div>
				<div class="wps-file-review-flow-step">
					<span class="wps-file-review-flow-label">3</span>
					<strong><?php esc_html_e( 'Run Guardian again', 'wpshadow' ); ?></strong>
					<p><?php esc_html_e( 'After the file change is made, rerun the check so WPShadow can confirm the issue is resolved.', 'wpshadow' ); ?></p>
				</div>
			</div>

			<?php if ( $preview_manual_enabled ) : ?>
				<div class="wps-file-review-preview-banner">
					<strong><?php esc_html_e( 'Preview mode is on', 'wpshadow' ); ?></strong>
					<p><?php esc_html_e( 'WPShadow added one fake manual card below so you can review the manual-only layout safely. No site files are changed in this mode.', 'wpshadow' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="wps-file-review-preview-banner">
				<strong><?php esc_html_e( 'Beta safeguard', 'wpshadow' ); ?></strong>
				<p><?php esc_html_e( 'Automatic file changes are intentionally limited to direct filesystem access. If your host requires FTP, SSH, or another credentialed transport, WPShadow will keep the fix in the manual-review section and show the exact code to apply yourself.', 'wpshadow' ); ?></p>
			</div>
		</div>

		<?php if ( empty( $pending ) ) : ?>
			<div class="wps-file-review-section-block">
				<div class="wps-alert wps-alert--success">
					<div class="wps-alert-icon">✓</div>
					<div class="wps-alert-content">
						<strong><?php esc_html_e( 'All clear!', 'wpshadow' ); ?></strong>
						<p class="wps-alert-copy"><?php esc_html_e( 'There are no pending file-write changes to review at this time.', 'wpshadow' ); ?></p>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<section class="wps-file-review-section-block wps-file-review-section-block--actionable">
			<div class="wps-file-review-section-heading">
				<div>
					<h2><?php esc_html_e( 'Things WPShadow Can Apply In Beta', 'wpshadow' ); ?></h2>
					<p><?php esc_html_e( 'These files are readable, writable, and available through WordPress direct filesystem access. You can preview each change, create a backup, and apply the fix directly from this page.', 'wpshadow' ); ?></p>
				</div>
				<span class="wps-file-review-count-badge wps-file-review-count-badge--actionable"><?php echo (int) count( $actionable ); ?></span>
			</div>

			<div class="wps-file-review-list">
				<?php if ( ! empty( $actionable ) ) : ?>
					<?php foreach ( $actionable as $treatment ) : ?>
						<?php $render_actionable_card( $treatment ); ?>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="wps-alert wps-alert--info wps-file-review-empty-state">
						<div class="wps-alert-icon">i</div>
						<div class="wps-alert-content">
							<strong><?php esc_html_e( 'Nothing in this section right now', 'wpshadow' ); ?></strong>
							<p class="wps-alert-copy"><?php esc_html_e( 'WPShadow does not currently have any writable file changes it can apply automatically.', 'wpshadow' ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<section class="wps-file-review-section-block wps-file-review-section-block--manual">
			<div class="wps-file-review-section-heading">
				<div>
					<h2><?php esc_html_e( 'Files That Need Manual Updates', 'wpshadow' ); ?></h2>
					<p><?php esc_html_e( 'These changes still matter, but WordPress does not have enough direct filesystem access to save them safely in beta. For each one below, WPShadow explains the reason in plain English and shows the exact code you need to add yourself.', 'wpshadow' ); ?></p>
				</div>
				<span class="wps-file-review-count-badge wps-file-review-count-badge--manual"><?php echo (int) count( $manual ); ?></span>
			</div>

			<div class="wps-file-review-list">
				<?php if ( ! empty( $manual ) ) : ?>
					<?php foreach ( $manual as $treatment ) : ?>
						<?php $render_manual_card( $treatment ); ?>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="wps-alert wps-alert--success wps-file-review-empty-state">
						<div class="wps-alert-icon">✓</div>
						<div class="wps-alert-content">
							<strong><?php esc_html_e( 'No blocked files right now', 'wpshadow' ); ?></strong>
							<p class="wps-alert-copy"><?php esc_html_e( 'Every file-based fix currently in this review can be written by WPShadow directly. If a future file is locked by permissions, it will appear here with manual steps.', 'wpshadow' ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<?php if ( ! empty( $actionable ) ) : ?>
			<div class="wps-file-review-preferences">
				<h3 class="wps-file-review-section-title"><?php esc_html_e( 'Warning Preferences', 'wpshadow' ); ?></h3>
				<p class="wps-file-review-path"><?php esc_html_e( 'Once you are comfortable with the file-write process, you can skip the SFTP acknowledgment step for future fixes.', 'wpshadow' ); ?></p>
				<label class="wps-file-review-pref-label">
					<input type="checkbox" id="wpshadow-trust-all" <?php checked( File_Write_Trust::is_all_trusted() ); ?>>
					<?php esc_html_e( 'Skip SFTP acknowledgment for all future file-write fixes (global)', 'wpshadow' ); ?>
				</label>
				<p class="wps-file-review-pref-note"><?php esc_html_e( 'Per-file trust is also available in the confirmation dialog when you apply a specific fix.', 'wpshadow' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
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
