<?php
/**
 * Smart Code Snippets Manager Utility
 *
 * Intelligent snippet manager with syntax validation, sandboxing, and rollback.
 *
 * @package WPShadow
 * @since   1.2601.2200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

Tool_View_Base::verify_access( 'manage_options' );
Tool_View_Base::enqueue_assets( 'code-snippets' );
Tool_View_Base::render_header( __( 'Smart Code Snippets Manager', 'wpshadow' ) );

// Get existing snippets
$snippets = get_option( 'wpshadow_code_snippets', array() );
$snippet_count = count( $snippets );
$snippet_limit_free = 10; // Free tier limit

// Calculate active snippets
$active_count = 0;
foreach ( $snippets as $snippet ) {
	if ( ! empty( $snippet['active'] ) ) {
		$active_count++;
	}
}
?>

<p><?php esc_html_e( 'Add custom PHP, JavaScript, and CSS code to your site safely. Built-in syntax validation prevents white screens, and automatic sandboxing catches fatal errors before they break your site.', 'wpshadow' ); ?></p>

<!-- Safety Features Notice -->
<div class="notice notice-success">
	<h4><?php esc_html_e( '🛡️ Safety Features Built-In:', 'wpshadow' ); ?></h4>
	<ul style="list-style: disc; margin-left: 20px;">
		<li><?php esc_html_e( 'Syntax validation before activation', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Auto-disable on fatal errors', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Version history and one-click rollback', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Conditional execution (logged-in, specific pages, user roles)', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Sandboxed testing mode', 'wpshadow' ); ?></li>
	</ul>
</div>

<!-- Usage Stats -->
<div class="wpshadow-tool-section" style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
	<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
		<div>
			<h4 style="margin: 0 0 5px 0;"><?php esc_html_e( 'Snippet Limit', 'wpshadow' ); ?></h4>
			<div style="font-size: 28px; font-weight: bold; color: <?php echo $snippet_count >= $snippet_limit_free ? '#d63638' : '#00a32a'; ?>;">
				<?php echo esc_html( $snippet_count ); ?>/<?php echo esc_html( $snippet_limit_free ); ?>
			</div>
			<p style="margin: 5px 0 0 0; color: #666; font-size: 13px;">
				<?php esc_html_e( 'Free Tier', 'wpshadow' ); ?>
			</p>
		</div>
		<div>
			<h4 style="margin: 0 0 5px 0;"><?php esc_html_e( 'Active Snippets', 'wpshadow' ); ?></h4>
			<div style="font-size: 28px; font-weight: bold; color: #0073aa;">
				<?php echo esc_html( $active_count ); ?>
			</div>
			<p style="margin: 5px 0 0 0; color: #666; font-size: 13px;">
				<?php esc_html_e( 'Currently running', 'wpshadow' ); ?>
			</p>
		</div>
	</div>
	<?php if ( $snippet_count >= $snippet_limit_free ) : ?>
		<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
			<p style="margin: 0;">
				<strong><?php esc_html_e( 'Need more snippets?', 'wpshadow' ); ?></strong>
				<a href="https://wpshadow.com/pro/?utm_source=plugin&utm_medium=snippets&utm_campaign=upgrade" target="_blank" class="button button-primary" style="margin-left: 10px;">
					<?php esc_html_e( 'Upgrade to Pro for Unlimited', 'wpshadow' ); ?>
				</a>
			</p>
		</div>
	<?php endif; ?>
</div>

<!-- Add New Snippet -->
<div class="wpshadow-tool-section">
	<h3>
		<?php esc_html_e( 'Add New Snippet', 'wpshadow' ); ?>
		<button type="button" class="button button-secondary" id="toggle-snippet-form" style="margin-left: 10px;">
			<span class="dashicons dashicons-plus"></span>
			<?php esc_html_e( 'New Snippet', 'wpshadow' ); ?>
		</button>
	</h3>
	
	<form id="wpshadow-snippet-form" method="post" style="display: none; margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 4px;">
		<?php wp_nonce_field( 'wpshadow_save_snippet', 'nonce' ); ?>
		<input type="hidden" name="snippet_id" id="snippet_id" value="" />
		
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="snippet_title"><?php esc_html_e( 'Snippet Title', 'wpshadow' ); ?></label>
				</th>
				<td>
					<input type="text" 
						   id="snippet_title" 
						   name="snippet_title" 
						   class="regular-text" 
						   placeholder="<?php esc_attr_e( 'e.g., Custom Login Redirect', 'wpshadow' ); ?>"
						   required />
					<p class="description">
						<?php esc_html_e( 'A descriptive name for this snippet', 'wpshadow' ); ?>
					</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label for="snippet_type"><?php esc_html_e( 'Code Type', 'wpshadow' ); ?></label>
				</th>
				<td>
					<select id="snippet_type" name="snippet_type" class="regular-text">
						<option value="php"><?php esc_html_e( 'PHP', 'wpshadow' ); ?></option>
						<option value="js"><?php esc_html_e( 'JavaScript', 'wpshadow' ); ?></option>
						<option value="css"><?php esc_html_e( 'CSS', 'wpshadow' ); ?></option>
					</select>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label for="snippet_code"><?php esc_html_e( 'Code', 'wpshadow' ); ?></label>
				</th>
				<td>
					<textarea id="snippet_code" 
							  name="snippet_code" 
							  rows="15" 
							  class="large-text code" 
							  style="font-family: 'Courier New', monospace; font-size: 13px;"
							  required></textarea>
					<p class="description">
						<?php esc_html_e( 'Enter your code here. PHP snippets should not include opening/closing PHP tags.', 'wpshadow' ); ?>
					</p>
					<div id="syntax-validation" style="margin-top: 10px; padding: 10px; border-radius: 4px; display: none;"></div>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e( 'Execution Conditions', 'wpshadow' ); ?></th>
				<td>
					<fieldset>
						<label>
							<input type="radio" name="snippet_scope" value="global" checked />
							<?php esc_html_e( 'Run everywhere', 'wpshadow' ); ?>
						</label>
						<br />
						<label>
							<input type="radio" name="snippet_scope" value="admin" />
							<?php esc_html_e( 'Admin area only', 'wpshadow' ); ?>
						</label>
						<br />
						<label>
							<input type="radio" name="snippet_scope" value="frontend" />
							<?php esc_html_e( 'Frontend only', 'wpshadow' ); ?>
						</label>
						<br />
						<label>
							<input type="radio" name="snippet_scope" value="logged_in" />
							<?php esc_html_e( 'Logged-in users only', 'wpshadow' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label for="snippet_description"><?php esc_html_e( 'Description', 'wpshadow' ); ?></label>
				</th>
				<td>
					<textarea id="snippet_description" 
							  name="snippet_description" 
							  rows="3" 
							  class="large-text"
							  placeholder="<?php esc_attr_e( 'What does this snippet do?', 'wpshadow' ); ?>"></textarea>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e( 'Testing Mode', 'wpshadow' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="snippet_sandbox" id="snippet_sandbox" value="1" />
						<?php esc_html_e( 'Enable sandboxed testing (safer)', 'wpshadow' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Snippet will run in isolated environment first to detect errors', 'wpshadow' ); ?>
					</p>
				</td>
			</tr>
		</table>
		
		<p class="submit">
			<button type="button" class="button button-primary button-large" id="validate-snippet">
				<span class="dashicons dashicons-yes"></span>
				<?php esc_html_e( 'Validate & Save', 'wpshadow' ); ?>
			</button>
			<button type="button" class="button button-secondary" id="cancel-snippet">
				<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
			</button>
		</p>
	</form>
</div>

<!-- Existing Snippets -->
<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Your Snippets', 'wpshadow' ); ?></h3>
	
	<?php if ( ! empty( $snippets ) ) : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width: 50px;"><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Title', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Type', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Scope', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Modified', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $snippets as $snippet_id => $snippet ) : ?>
					<tr>
						<td>
							<label class="switch">
								<input type="checkbox" 
									   class="snippet-toggle" 
									   data-snippet-id="<?php echo esc_attr( $snippet_id ); ?>"
									   <?php checked( ! empty( $snippet['active'] ) ); ?> />
								<span class="slider"></span>
							</label>
						</td>
						<td>
							<strong><?php echo esc_html( $snippet['title'] ); ?></strong>
							<?php if ( ! empty( $snippet['description'] ) ) : ?>
								<br /><small style="color: #666;"><?php echo esc_html( $snippet['description'] ); ?></small>
							<?php endif; ?>
							<?php if ( ! empty( $snippet['error'] ) ) : ?>
								<br /><span style="color: #d63638; font-size: 12px;">⚠ <?php echo esc_html( $snippet['error'] ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php
							$type_badges = array(
								'php' => '<span class="badge" style="background: #8892BF; color: white; padding: 2px 8px; border-radius: 3px;">PHP</span>',
								'js'  => '<span class="badge" style="background: #F0DB4F; color: black; padding: 2px 8px; border-radius: 3px;">JS</span>',
								'css' => '<span class="badge" style="background: #2965F1; color: white; padding: 2px 8px; border-radius: 3px;">CSS</span>',
							);
							echo $type_badges[ $snippet['type'] ] ?? esc_html( strtoupper( $snippet['type'] ) );
							?>
						</td>
						<td>
							<?php
							$scopes = array(
								'global'    => __( 'Everywhere', 'wpshadow' ),
								'admin'     => __( 'Admin', 'wpshadow' ),
								'frontend'  => __( 'Frontend', 'wpshadow' ),
								'logged_in' => __( 'Logged In', 'wpshadow' ),
							);
							echo esc_html( $scopes[ $snippet['scope'] ] ?? __( 'Everywhere', 'wpshadow' ) );
							?>
						</td>
						<td>
							<?php
							if ( ! empty( $snippet['modified'] ) ) {
								echo esc_html( human_time_diff( $snippet['modified'], time() ) . ' ' . __( 'ago', 'wpshadow' ) );
							} else {
								echo '—';
							}
							?>
						</td>
						<td>
							<button class="button edit-snippet-button" data-snippet-id="<?php echo esc_attr( $snippet_id ); ?>">
								<span class="dashicons dashicons-edit"></span>
								<?php esc_html_e( 'Edit', 'wpshadow' ); ?>
							</button>
							<button class="button delete-snippet-button" data-snippet-id="<?php echo esc_attr( $snippet_id ); ?>">
								<span class="dashicons dashicons-trash"></span>
								<?php esc_html_e( 'Delete', 'wpshadow' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<div style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 4px;">
			<span class="dashicons dashicons-editor-code" style="font-size: 64px; color: #ccc;"></span>
			<h3><?php esc_html_e( 'No Snippets Yet', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Click "New Snippet" above to add your first code snippet.', 'wpshadow' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<!-- Snippet Library -->
<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Snippet Library', 'wpshadow' ); ?></h3>
	<p><?php esc_html_e( 'Popular pre-built snippets you can use with one click:', 'wpshadow' ); ?></p>
	
	<div class="wps-grid wps-grid-auto-320">
		<div class="wps-card">
			<div class="wps-card-body">
				<h4 class="wps-card-title"><?php esc_html_e( 'Disable WordPress Emojis', 'wpshadow' ); ?></h4>
				<p class="wps-text-muted"><?php esc_html_e( 'Remove emoji scripts for better performance', 'wpshadow' ); ?></p>
			</div>
			<div class="wps-card-footer">
				<button class="wps-btn wps-btn--secondary use-library-snippet" data-snippet="disable-emojis">
					<?php esc_html_e( 'Use This Snippet', 'wpshadow' ); ?>
				</button>
			</div>
		</div>
		
		<div class="wps-card">
			<div class="wps-card-body">
				<h4 class="wps-card-title"><?php esc_html_e( 'Custom Login Logo', 'wpshadow' ); ?></h4>
				<p class="wps-text-muted"><?php esc_html_e( 'Replace WordPress logo on login page', 'wpshadow' ); ?></p>
			</div>
			<div class="wps-card-footer">
				<button class="wps-btn wps-btn--secondary use-library-snippet" data-snippet="custom-login-logo">
					<?php esc_html_e( 'Use This Snippet', 'wpshadow' ); ?>
				</button>
			</div>
		</div>
		
		<div class="wps-card">
			<div class="wps-card-body">
				<h4 class="wps-card-title"><?php esc_html_e( 'Increase Upload Limit', 'wpshadow' ); ?></h4>
				<p class="wps-text-muted"><?php esc_html_e( 'Raise maximum file upload size', 'wpshadow' ); ?></p>
			</div>
			<div class="wps-card-footer">
				<button class="wps-btn wps-btn--secondary use-library-snippet" data-snippet="increase-upload">
					<?php esc_html_e( 'Use This Snippet', 'wpshadow' ); ?>
				</button>
			</div>
		</div>
		
		<div class="wps-card">
			<div class="wps-card-body">
				<h4 class="wps-card-title"><?php esc_html_e( 'Disable XML-RPC', 'wpshadow' ); ?></h4>
				<p class="wps-text-muted"><?php esc_html_e( 'Improve security by disabling XML-RPC', 'wpshadow' ); ?></p>
			</div>
			<div class="wps-card-footer">
				<button class="wps-btn wps-btn--secondary use-library-snippet" data-snippet="disable-xmlrpc">
					<?php esc_html_e( 'Use This Snippet', 'wpshadow' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<style>
.switch {
	position: relative;
	display: inline-block;
	width: 50px;
	height: 24px;
}

.switch input {
	opacity: 0;
	width: 0;
	height: 0;
}

.slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	transition: .4s;
	border-radius: 24px;
}

.slider:before {
	position: absolute;
	content: "";
	height: 18px;
	width: 18px;
	left: 3px;
	bottom: 3px;
	background-color: white;
	transition: .4s;
	border-radius: 50%;
}

input:checked + .slider {
	background-color: #00a32a;
}

input:checked + .slider:before {
	transform: translateX(26px);
}
</style>

<script>
jQuery(document).ready(function($) {
	const snippetLimit = <?php echo esc_js( $snippet_limit_free ); ?>;
	const currentCount = <?php echo esc_js( $snippet_count ); ?>;
	
	// Toggle snippet form
	$('#toggle-snippet-form').on('click', function() {
		if (currentCount >= snippetLimit) {
			alert('<?php echo esc_js( __( 'You have reached the free tier limit. Upgrade to Pro for unlimited snippets.', 'wpshadow' ) ); ?>');
			return;
		}
		$('#wpshadow-snippet-form').slideToggle();
	});
	
	$('#cancel-snippet').on('click', function() {
		$('#wpshadow-snippet-form').slideUp();
		$('#wpshadow-snippet-form')[0].reset();
	});
	
	// Syntax validation
	$('#snippet_code').on('blur', function() {
		const code = $(this).val();
		const type = $('#snippet_type').val();
		
		if (!code.trim()) return;
		
		$('#syntax-validation').html('<span style="color: #666;">⏳ <?php echo esc_js( __( 'Validating...', 'wpshadow' ) ); ?></span>').show();
		
		$.post(ajaxurl, {
			action: 'wpshadow_validate_snippet',
			nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_validate_snippet' ) ); ?>',
			code: code,
			type: type
		}, function(response) {
			if (response.success && response.data.valid) {
				$('#syntax-validation')
					.css('background', '#d4edda')
					.css('border', '1px solid #28a745')
					.css('color', '#155724')
					.html('✓ <?php echo esc_js( __( 'Syntax is valid', 'wpshadow' ) ); ?>');
			} else {
				$('#syntax-validation')
					.css('background', '#f8d7da')
					.css('border', '1px solid #dc3545')
					.css('color', '#721c24')
					.html('✗ ' + (response.data.error || '<?php echo esc_js( __( 'Syntax error detected', 'wpshadow' ) ); ?>'));
			}
		});
	});
	
	// Validate and save snippet
	$('#validate-snippet').on('click', function() {
		const $button = $(this);
		const code = $('#snippet_code').val();
		const type = $('#snippet_type').val();
		
		$button.prop('disabled', true).html('<span class="spinner is-active" style="float: none; margin: 0;"></span> <?php echo esc_js( __( 'Validating...', 'wpshadow' ) ); ?>');
		
		$.post(ajaxurl, {
			action: 'wpshadow_save_snippet',
			nonce: $('[name="nonce"]').val(),
			snippet_id: $('#snippet_id').val(),
			title: $('#snippet_title').val(),
			code: code,
			type: type,
			scope: $('[name="snippet_scope"]:checked').val(),
			description: $('#snippet_description').val(),
			sandbox: $('#snippet_sandbox').is(':checked') ? 1 : 0
		}, function(response) {
			$button.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> <?php echo esc_js( __( 'Validate & Save', 'wpshadow' ) ); ?>');
			
			if (response.success) {
				alert('<?php echo esc_js( __( 'Snippet saved successfully!', 'wpshadow' ) ); ?>');
				location.reload();
			} else {
				alert(response.data.message || '<?php echo esc_js( __( 'Failed to save snippet', 'wpshadow' ) ); ?>');
			}
		});
	});
	
	// Toggle snippet active/inactive
	$('.snippet-toggle').on('change', function() {
		const snippetId = $(this).data('snippet-id');
		const active = $(this).is(':checked');
		
		$.post(ajaxurl, {
			action: 'wpshadow_toggle_snippet',
			nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_toggle_snippet' ) ); ?>',
			snippet_id: snippetId,
			active: active ? 1 : 0
		}, function(response) {
			if (!response.success) {
				alert(response.data.message || '<?php echo esc_js( __( 'Failed to toggle snippet', 'wpshadow' ) ); ?>');
				location.reload();
			}
		});
	});
	
	// Delete snippet
	$('.delete-snippet-button').on('click', function() {
		if (!confirm('<?php echo esc_js( __( 'Delete this snippet? This cannot be undone.', 'wpshadow' ) ); ?>')) {
			return;
		}
		
		const snippetId = $(this).data('snippet-id');
		
		$.post(ajaxurl, {
			action: 'wpshadow_delete_snippet',
			nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_delete_snippet' ) ); ?>',
			snippet_id: snippetId
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert(response.data.message || '<?php echo esc_js( __( 'Failed to delete snippet', 'wpshadow' ) ); ?>');
			}
		});
	});
});
</script>

<?php
Tool_View_Base::render_footer();
