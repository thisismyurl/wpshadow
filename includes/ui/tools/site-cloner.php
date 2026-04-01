<?php
/**
 * One-Click Site Cloner Utility
 *
 * Clone entire WordPress site to staging subdomain/subdirectory using Vault Light.
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

Tool_View_Base::verify_access( 'manage_options' );
Tool_View_Base::enqueue_assets( 'site-cloner' );
Tool_View_Base::render_header( __( 'One-Click Site Cloner', 'wpshadow' ) );

// Get existing clones
$clones = get_option( 'wpshadow_site_clones', array() );
$clone_count = count( $clones );
$clone_limit_free = 2; // Free tier limit

// Get site info
$site_url = get_site_url();
$site_name = get_bloginfo( 'name' );
?>

<p><?php esc_html_e( 'Create perfect copies of your WordPress site for staging, testing, or development. Each clone is a fully functional duplicate with its own database and files.', 'wpshadow' ); ?></p>

<!-- Feature Benefits -->
<div class="notice notice-info">
	<h4><?php esc_html_e( '🚀 What You Can Do With Site Cloner:', 'wpshadow' ); ?></h4>
	<ul style="list-style: disc; margin-left: 20px;">
		<li><?php esc_html_e( 'Test plugin/theme updates safely before going live', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Create staging environments for clients', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Duplicate sites for A/B testing or experimentation', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Provide development copies to your team', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Sync changes back to production when ready', 'wpshadow' ); ?></li>
	</ul>
</div>

<!-- Usage Limits -->
<div class="wpshadow-tool-section" style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
	<div style="display: flex; justify-content: space-between; align-items: center;">
		<div>
			<h4 style="margin: 0 0 5px 0;"><?php esc_html_e( 'Clone Limit', 'wpshadow' ); ?></h4>
			<p style="margin: 0; color: #666; font-size: 13px;">
				<?php
				printf(
					/* translators: %1$d: current clone count, %2$d: clone limit */
					esc_html__( 'You have created %1$d of %2$d clones (Free Tier)', 'wpshadow' ),
					$clone_count,
					$clone_limit_free
				);
				?>
			</p>
		</div>
		<div style="text-align: right;">
			<div style="font-size: 32px; font-weight: bold; color: <?php echo $clone_count >= $clone_limit_free ? '#d63638' : '#00a32a'; ?>;">
				<?php echo esc_html( $clone_count ); ?>/<?php echo esc_html( $clone_limit_free ); ?>
			</div>
		</div>
	</div>
	<?php if ( $clone_count >= $clone_limit_free ) : ?>
		<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
			<p style="margin: 0;">
				<strong><?php esc_html_e( 'Upgrade to Pro for unlimited clones:', 'wpshadow' ); ?></strong>
				<a href="https://wpshadow.com/pro/?utm_source=plugin&utm_medium=site-cloner&utm_campaign=upgrade" target="_blank" class="button button-primary" style="margin-left: 10px;">
					<?php esc_html_e( 'Upgrade to Pro', 'wpshadow' ); ?>
				</a>
			</p>
		</div>
	<?php endif; ?>
</div>

<!-- Create New Clone -->
<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Create New Clone', 'wpshadow' ); ?></h3>

	<form id="wpshadow-create-clone-form" method="post">
		<?php wp_nonce_field( 'wpshadow_create_clone', 'nonce' ); ?>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="clone_name"><?php esc_html_e( 'Clone Name', 'wpshadow' ); ?></label>
				</th>
				<td>
					<input type="text"
						   id="clone_name"
						   name="clone_name"
						   class="regular-text"
						   placeholder="<?php esc_attr_e( 'e.g., Staging Site', 'wpshadow' ); ?>"
						   required />
					<p class="description">
						<?php esc_html_e( 'A friendly name to identify this clone', 'wpshadow' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="clone_type"><?php esc_html_e( 'Clone Type', 'wpshadow' ); ?></label>
				</th>
				<td>
					<select id="clone_type" name="clone_type" class="regular-text">
						<option value="subdomain"><?php esc_html_e( 'Subdomain (staging.yoursite.com)', 'wpshadow' ); ?></option>
						<option value="subdirectory" selected><?php esc_html_e( 'Subdirectory (yoursite.com/staging)', 'wpshadow' ); ?></option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Choose where to create the clone. Subdirectory is easier to set up.', 'wpshadow' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="clone_slug"><?php esc_html_e( 'Clone Path/Subdomain', 'wpshadow' ); ?></label>
				</th>
				<td>
					<input type="text"
						   id="clone_slug"
						   name="clone_slug"
						   class="regular-text"
						   placeholder="<?php esc_attr_e( 'staging', 'wpshadow' ); ?>"
						   pattern="[a-z0-9-]+"
						   required />
					<p class="description">
						<?php esc_html_e( 'Lowercase letters, numbers, and hyphens only', 'wpshadow' ); ?>
					</p>
					<p id="clone-url-preview" style="margin-top: 10px; padding: 10px; background: #f0f0f1; border-radius: 4px;">
						<strong><?php esc_html_e( 'Clone URL:', 'wpshadow' ); ?></strong>
						<span id="clone-url-display"><?php echo esc_html( $site_url ); ?>/staging</span>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Clone Options', 'wpshadow' ); ?></th>
				<td>
					<fieldset>
						<label>
							<input type="checkbox" name="clone_database" value="1" checked />
							<?php esc_html_e( 'Clone database', 'wpshadow' ); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="clone_uploads" value="1" checked />
							<?php esc_html_e( 'Clone uploads folder', 'wpshadow' ); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="clone_themes" value="1" checked />
							<?php esc_html_e( 'Clone themes', 'wpshadow' ); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="clone_plugins" value="1" checked />
							<?php esc_html_e( 'Clone plugins', 'wpshadow' ); ?>
						</label>
					</fieldset>
					<p class="description">
						<?php esc_html_e( 'Select what to include in the clone', 'wpshadow' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<p class="submit">
			<button type="submit"
					class="button button-primary button-large"
					id="create-clone-button"
					<?php echo $clone_count >= $clone_limit_free ? 'disabled' : ''; ?>>
				<span class="dashicons dashicons-admin-site" style="margin-top: 4px;"></span>
				<?php esc_html_e( 'Clone Site Now', 'wpshadow' ); ?>
			</button>
			<span class="description" style="margin-left: 10px;">
				<?php esc_html_e( 'This may take 5-15 minutes depending on site size', 'wpshadow' ); ?>
			</span>
		</p>
	</form>

	<!-- Progress Indicator -->
	<div id="clone-progress" style="display: none; margin-top: 20px;">
		<div style="padding: 20px; background: #f0f6fc; border: 1px solid #0073aa; border-radius: 4px;">
			<h4 style="margin: 0 0 10px 0;"><?php esc_html_e( 'Cloning in Progress...', 'wpshadow' ); ?></h4>
			<div class="progress-bar" style="width: 100%; height: 30px; background: #e0e0e0; border-radius: 4px; overflow: hidden;">
				<div id="clone-progress-bar" style="width: 0%; height: 100%; background: #00a32a; transition: width 0.3s;"></div>
			</div>
			<p id="clone-progress-text" style="margin: 10px 0 0 0; font-size: 13px; color: #666;">
				<?php esc_html_e( 'Initializing...', 'wpshadow' ); ?>
			</p>
		</div>
	</div>
</div>

<!-- Existing Clones -->
<?php if ( ! empty( $clones ) ) : ?>
	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Existing Clones', 'wpshadow' ); ?></h3>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Clone Name', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'URL', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Created', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Size', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $clones as $clone_id => $clone ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $clone['name'] ); ?></strong>
							<?php if ( ! empty( $clone['description'] ) ) : ?>
								<br /><small style="color: #666;"><?php echo esc_html( $clone['description'] ); ?></small>
							<?php endif; ?>
						</td>
						<td>
							<a href="<?php echo esc_url( $clone['url'] ); ?>" target="_blank">
								<?php echo esc_html( $clone['url'] ); ?>
								<span class="dashicons dashicons-external" style="font-size: 14px;"></span>
							</a>
						</td>
						<td>
							<?php echo esc_html( human_time_diff( $clone['created'], time() ) . ' ' . __( 'ago', 'wpshadow' ) ); ?>
						</td>
						<td>
							<?php echo esc_html( size_format( $clone['size'] ?? 0, 2 ) ); ?>
						</td>
						<td>
							<button class="button sync-clone-button" data-clone-id="<?php echo esc_attr( $clone_id ); ?>">
								<span class="dashicons dashicons-update"></span>
								<?php esc_html_e( 'Sync', 'wpshadow' ); ?>
							</button>
							<button class="button delete-clone-button" data-clone-id="<?php echo esc_attr( $clone_id ); ?>">
								<span class="dashicons dashicons-trash"></span>
								<?php esc_html_e( 'Delete', 'wpshadow' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php else : ?>
	<div class="wpshadow-tool-section">
		<div style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 4px;">
			<span class="dashicons dashicons-admin-site" style="font-size: 64px; color: #ccc;"></span>
			<h3><?php esc_html_e( 'No Clones Yet', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Create your first clone to get started with safe testing and staging.', 'wpshadow' ); ?></p>
		</div>
	</div>
<?php endif; ?>

<!-- How It Works -->
<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'How Site Cloner Works', 'wpshadow' ); ?></h3>

	<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
		<div style="padding: 20px; background: #f9f9f9; border-radius: 4px;">
			<h4><span class="dashicons dashicons-backup" style="color: #0073aa;"></span> 1. <?php esc_html_e( 'Snapshot', 'wpshadow' ); ?></h4>
			<p><?php esc_html_e( 'Creates Vault Light snapshot of your site', 'wpshadow' ); ?></p>
		</div>

		<div style="padding: 20px; background: #f9f9f9; border-radius: 4px;">
			<h4><span class="dashicons dashicons-admin-site" style="color: #0073aa;"></span> 2. <?php esc_html_e( 'Clone', 'wpshadow' ); ?></h4>
			<p><?php esc_html_e( 'Duplicates files and database to new location', 'wpshadow' ); ?></p>
		</div>

		<div style="padding: 20px; background: #f9f9f9; border-radius: 4px;">
			<h4><span class="dashicons dashicons-admin-tools" style="color: #0073aa;"></span> 3. <?php esc_html_e( 'Configure', 'wpshadow' ); ?></h4>
			<p><?php esc_html_e( 'Auto-updates URLs and paths for new location', 'wpshadow' ); ?></p>
		</div>

		<div style="padding: 20px; background: #f9f9f9; border-radius: 4px;">
			<h4><span class="dashicons dashicons-yes-alt" style="color: #0073aa;"></span> 4. <?php esc_html_e( 'Ready', 'wpshadow' ); ?></h4>
			<p><?php esc_html_e( 'Clone is fully functional and ready to use', 'wpshadow' ); ?></p>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Update clone URL preview
	function updateClonePreview() {
		const cloneType = $('#clone_type').val();
		const cloneSlug = $('#clone_slug').val() || 'staging';
		const siteUrl = '<?php echo esc_js( $site_url ); ?>';
		let cloneUrl;

		if (cloneType === 'subdomain') {
			const domain = siteUrl.replace(/^https?:\/\//, '').replace(/^www\./, '');
			cloneUrl = siteUrl.split('//')[0] + '//' + cloneSlug + '.' + domain;
		} else {
			cloneUrl = siteUrl + '/' + cloneSlug;
		}

		$('#clone-url-display').text(cloneUrl);
	}

	$('#clone_type, #clone_slug').on('change input', updateClonePreview);

	// Handle form submission
	$('#wpshadow-create-clone-form').on('submit', function(e) {
		e.preventDefault();

		if (!confirm('<?php echo esc_js( __( 'Are you sure you want to clone this site? This may take 5-15 minutes.', 'wpshadow' ) ); ?>')) {
			return;
		}

		const $form = $(this);
		const $button = $('#create-clone-button');
		const $progress = $('#clone-progress');
		const $progressBar = $('#clone-progress-bar');
		const $progressText = $('#clone-progress-text');

		$button.prop('disabled', true);
		$progress.show();

		const formData = new FormData(this);
		formData.append('action', 'wpshadow_create_clone');

		// Simulate progress (real implementation would use WebSocket or polling)
		let progress = 0;
		const progressInterval = setInterval(function() {
			progress += Math.random() * 15;
			if (progress > 90) {
				clearInterval(progressInterval);
			}
			$progressBar.css('width', Math.min(progress, 90) + '%');

			if (progress < 30) {
				$progressText.text('<?php echo esc_js( __( 'Creating snapshot...', 'wpshadow' ) ); ?>');
			} else if (progress < 60) {
				$progressText.text('<?php echo esc_js( __( 'Copying files...', 'wpshadow' ) ); ?>');
			} else {
				$progressText.text('<?php echo esc_js( __( 'Cloning database...', 'wpshadow' ) ); ?>');
			}
		}, 500);

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				clearInterval(progressInterval);
				$progressBar.css('width', '100%');
				$progressText.text('<?php echo esc_js( __( 'Clone created successfully!', 'wpshadow' ) ); ?>');

				setTimeout(function() {
					location.reload();
				}, 2000);
			},
			error: function(xhr) {
				clearInterval(progressInterval);
				$button.prop('disabled', false);
				$progress.hide();
				alert('<?php echo esc_js( __( 'Failed to create clone. Please try again.', 'wpshadow' ) ); ?>');
			}
		});
	});

	// Handle sync button
	$('.sync-clone-button').on('click', function() {
		const cloneId = $(this).data('clone-id');
		if (!confirm('<?php echo esc_js( __( 'Sync changes from production to this clone?', 'wpshadow' ) ); ?>')) {
			return;
		}

		// TODO: Implement sync functionality
		alert('<?php echo esc_js( __( 'Sync feature coming soon!', 'wpshadow' ) ); ?>');
	});

	// Handle delete button
	$('.delete-clone-button').on('click', function() {
		const cloneId = $(this).data('clone-id');
		if (!confirm('<?php echo esc_js( __( 'Permanently delete this clone? This cannot be undone.', 'wpshadow' ) ); ?>')) {
			return;
		}

		$.post(ajaxurl, {
			action: 'wpshadow_delete_clone',
			nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_delete_clone' ) ); ?>',
			clone_id: cloneId
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert(response.data.message || '<?php echo esc_js( __( 'Failed to delete clone', 'wpshadow' ) ); ?>');
			}
		});
	});
});
</script>

<?php
Tool_View_Base::render_footer();
