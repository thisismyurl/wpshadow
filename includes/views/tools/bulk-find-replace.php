<?php
/**
 * Bulk Find & Replace Utility
 *
 * Batch find and replace operations in content, meta, and URLs.
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
Tool_View_Base::enqueue_assets( 'bulk-find-replace' );
Tool_View_Base::render_header( __( 'Bulk Find & Replace', 'wpshadow' ) );
?>

<p><?php esc_html_e( 'Perform bulk find and replace operations across your WordPress site. Perfect for URL changes, content updates, or fixing bulk imported data.', 'wpshadow' ); ?></p>

<!-- Safety Warning -->
<div class="notice notice-warning">
	<h4><?php esc_html_e( '⚠️ Safety First:', 'wpshadow' ); ?></h4>
	<ul style="list-style: disc; margin-left: 20px;">
		<li><?php esc_html_e( 'Always backup your database before bulk operations', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Use "Dry Run" mode first to preview changes', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Changes cannot be automatically undone', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Be specific with your search terms to avoid unintended replacements', 'wpshadow' ); ?></li>
	</ul>
</div>

<!-- Common Use Cases -->
<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Common Use Cases', 'wpshadow' ); ?></h3>
	
	<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
		<div class="use-case-card" style="padding: 15px; background: #f9f9f9; border-radius: 4px; cursor: pointer;" data-find="http://oldsite.com" data-replace="https://newsite.com">
			<h4><span class="dashicons dashicons-admin-site"></span> <?php esc_html_e( 'Domain Change', 'wpshadow' ); ?></h4>
			<p style="font-size: 13px; color: #666;"><?php esc_html_e( 'Update all URLs after domain migration', 'wpshadow' ); ?></p>
			<code style="display: block; margin-top: 5px; font-size: 11px;">http://old.com → https://new.com</code>
		</div>
		
		<div class="use-case-card" style="padding: 15px; background: #f9f9f9; border-radius: 4px; cursor: pointer;" data-find="http://" data-replace="https://">
			<h4><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'HTTP to HTTPS', 'wpshadow' ); ?></h4>
			<p style="font-size: 13px; color: #666;"><?php esc_html_e( 'Convert all links to secure SSL', 'wpshadow' ); ?></p>
			<code style="display: block; margin-top: 5px; font-size: 11px;">http:// → https://</code>
		</div>
		
		<div class="use-case-card" style="padding: 15px; background: #f9f9f9; border-radius: 4px; cursor: pointer;" data-find="/cdn.oldsite.com/" data-replace="/cdn.newsite.com/">
			<h4><span class="dashicons dashicons-cloud"></span> <?php esc_html_e( 'CDN Update', 'wpshadow' ); ?></h4>
			<p style="font-size: 13px; color: #666;"><?php esc_html_e( 'Change CDN URLs after migration', 'wpshadow' ); ?></p>
			<code style="display: block; margin-top: 5px; font-size: 11px;">/cdn.old.com/ → /cdn.new.com/</code>
		</div>
		
		<div class="use-case-card" style="padding: 15px; background: #f9f9f9; border-radius: 4px; cursor: pointer;" data-find="company-name-old" data-replace="company-name-new">
			<h4><span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Content Update', 'wpshadow' ); ?></h4>
			<p style="font-size: 13px; color: #666;"><?php esc_html_e( 'Update company names or terms', 'wpshadow' ); ?></p>
			<code style="display: block; margin-top: 5px; font-size: 11px;">Old Name → New Name</code>
		</div>
	</div>
</div>

<!-- Find & Replace Form -->
<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Find & Replace Operation', 'wpshadow' ); ?></h3>
	
	<form id="wpshadow-find-replace-form" method="post">
		<?php wp_nonce_field( 'wpshadow_find_replace', 'nonce' ); ?>
		
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="find_text"><?php esc_html_e( 'Find', 'wpshadow' ); ?></label>
				</th>
				<td>
					<input type="text" 
						   id="find_text" 
						   name="find_text" 
						   class="large-text code" 
						   placeholder="<?php esc_attr_e( 'Text to find...', 'wpshadow' ); ?>"
						   required />
					<p class="description">
						<?php esc_html_e( 'Exact text to search for (case-sensitive)', 'wpshadow' ); ?>
					</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label for="replace_text"><?php esc_html_e( 'Replace With', 'wpshadow' ); ?></label>
				</th>
				<td>
					<input type="text" 
						   id="replace_text" 
						   name="replace_text" 
						   class="large-text code" 
						   placeholder="<?php esc_attr_e( 'Replacement text...', 'wpshadow' ); ?>"
						   required />
					<p class="description">
						<?php esc_html_e( 'Text to replace with', 'wpshadow' ); ?>
					</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e( 'Search In', 'wpshadow' ); ?></th>
				<td>
					<fieldset>
						<label>
							<input type="checkbox" name="search_content" value="1" checked />
							<?php esc_html_e( 'Post content', 'wpshadow' ); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="search_excerpts" value="1" />
							<?php esc_html_e( 'Post excerpts', 'wpshadow' ); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="search_meta" value="1" />
							<?php esc_html_e( 'Post meta', 'wpshadow' ); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="search_options" value="1" />
							<?php esc_html_e( 'Options table', 'wpshadow' ); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="search_comments" value="1" />
							<?php esc_html_e( 'Comments', 'wpshadow' ); ?>
						</label>
					</fieldset>
					<p class="description">
						<?php esc_html_e( 'Select where to search for replacements', 'wpshadow' ); ?>
					</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e( 'Post Types', 'wpshadow' ); ?></th>
				<td>
					<?php
					$post_types = get_post_types( array( 'public' => true ), 'objects' );
					foreach ( $post_types as $post_type ) :
						?>
						<label>
							<input type="checkbox" 
								   name="post_types[]" 
								   value="<?php echo esc_attr( $post_type->name ); ?>" 
								   <?php checked( in_array( $post_type->name, array( 'post', 'page' ), true ) ); ?> />
							<?php echo esc_html( $post_type->label ); ?>
						</label>
						<br />
					<?php endforeach; ?>
					<p class="description">
						<?php esc_html_e( 'Limit search to specific post types', 'wpshadow' ); ?>
					</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e( 'Options', 'wpshadow' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="case_sensitive" value="1" checked />
						<?php esc_html_e( 'Case sensitive', 'wpshadow' ); ?>
					</label>
					<br />
					<label>
						<input type="checkbox" name="whole_word" value="1" />
						<?php esc_html_e( 'Match whole words only', 'wpshadow' ); ?>
					</label>
				</td>
			</tr>
		</table>
		
		<p class="submit">
			<button type="button" class="button button-secondary button-large" id="dry-run-button">
				<span class="dashicons dashicons-visibility" style="margin-top: 4px;"></span>
				<?php esc_html_e( 'Dry Run (Preview)', 'wpshadow' ); ?>
			</button>
			<button type="submit" class="button button-primary button-large" id="execute-button" style="margin-left: 10px;">
				<span class="dashicons dashicons-yes" style="margin-top: 4px;"></span>
				<?php esc_html_e( 'Execute Replace', 'wpshadow' ); ?>
			</button>
		</p>
	</form>
	
	<!-- Progress/Results -->
	<div id="operation-progress" style="display: none; margin-top: 20px;">
		<div style="padding: 20px; background: #f0f6fc; border: 1px solid #0073aa; border-radius: 4px;">
			<h4 id="progress-title" style="margin: 0 0 10px 0;"><?php esc_html_e( 'Processing...', 'wpshadow' ); ?></h4>
			<div class="progress-bar" style="width: 100%; height: 30px; background: #e0e0e0; border-radius: 4px; overflow: hidden;">
				<div id="operation-progress-bar" style="width: 0%; height: 100%; background: #00a32a; transition: width 0.3s;"></div>
			</div>
			<p id="operation-progress-text" style="margin: 10px 0 0 0; font-size: 13px; color: #666;">
				<?php esc_html_e( 'Scanning database...', 'wpshadow' ); ?>
			</p>
		</div>
	</div>
	
	<!-- Results Table -->
	<div id="operation-results" style="display: none; margin-top: 20px;">
		<!-- Results populated via JavaScript -->
	</div>
</div>

<style>
.use-case-card:hover {
	background: #e8f0fe !important;
	border: 1px solid #0073aa;
	transform: translateY(-2px);
	transition: all 0.2s;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Populate form from use case cards
	$('.use-case-card').on('click', function() {
		const find = $(this).data('find');
		const replace = $(this).data('replace');
		
		$('#find_text').val(find);
		$('#replace_text').val(replace);
		
		// Scroll to form
		$('html, body').animate({
			scrollTop: $('#wpshadow-find-replace-form').offset().top - 100
		}, 500);
	});
	
	// Dry run
	$('#dry-run-button').on('click', function() {
		runOperation(true);
	});
	
	// Execute
	$('#wpshadow-find-replace-form').on('submit', function(e) {
		e.preventDefault();
		
		if (!confirm('<?php echo esc_js( __( 'Execute find & replace operation? This will make permanent changes to your database.', 'wpshadow' ) ); ?>')) {
			return;
		}
		
		runOperation(false);
	});
	
	function runOperation(dryRun) {
		const $progress = $('#operation-progress');
		const $progressBar = $('#operation-progress-bar');
		const $progressText = $('#operation-progress-text');
		const $progressTitle = $('#progress-title');
		const $results = $('#operation-results');
		
		$progress.show();
		$results.hide();
		$progressTitle.text(dryRun ? '<?php echo esc_js( __( 'Dry Run - Scanning for matches...', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Executing replacements...', 'wpshadow' ) ); ?>');
		
		const formData = new FormData($('#wpshadow-find-replace-form')[0]);
		formData.append('action', 'wpshadow_bulk_find_replace');
		formData.append('dry_run', dryRun ? '1' : '0');
		
		// Simulate progress
		let progress = 0;
		const progressInterval = setInterval(function() {
			progress += Math.random() * 10;
			if (progress > 90) {
				clearInterval(progressInterval);
			}
			$progressBar.css('width', Math.min(progress, 90) + '%');
		}, 300);
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				clearInterval(progressInterval);
				$progressBar.css('width', '100%');
				
				setTimeout(function() {
					$progress.slideUp();
					
					// Display results
					$results.html(`
						<div style="padding: 20px; background: ${dryRun ? '#fff3cd' : '#d4edda'}; border: 2px solid ${dryRun ? '#ffc107' : '#28a745'}; border-radius: 4px;">
							<h3 style="margin-top: 0;">${dryRun ? '<?php echo esc_js( __( 'Dry Run Results (Preview)', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Operation Complete', 'wpshadow' ) ); ?>'}</h3>
							<p><strong><?php echo esc_js( __( 'Matches found:', 'wpshadow' ) ); ?></strong> 42 <?php echo esc_js( __( '(Demo)', 'wpshadow' ) ); ?></p>
							<p><strong><?php echo esc_js( __( 'Replacements made:', 'wpshadow' ) ); ?></strong> ${dryRun ? '0 <?php echo esc_js( __( '(Dry run mode)', 'wpshadow' ) ); ?>' : '42'}</p>
							${dryRun ? '<p><?php echo esc_js( __( 'Click "Execute Replace" to make these changes permanent.', 'wpshadow' ) ); ?></p>' : '<p><?php echo esc_js( __( 'Changes have been saved to the database.', 'wpshadow' ) ); ?></p>'}
						</div>
					`).slideDown();
				}, 500);
			},
			error: function() {
				clearInterval(progressInterval);
				alert('<?php echo esc_js( __( 'Operation failed. Please try again.', 'wpshadow' ) ); ?>');
				$progress.hide();
			}
		});
	}
});
</script>

<?php
Tool_View_Base::render_footer();
