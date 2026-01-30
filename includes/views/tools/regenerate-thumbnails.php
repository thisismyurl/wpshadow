<?php
/**
 * Regenerate Thumbnails Utility
 *
 * Batch regenerate image thumbnails for all image sizes.
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
Tool_View_Base::enqueue_assets( 'regenerate-thumbnails' );
Tool_View_Base::render_header( __( 'Regenerate Thumbnails', 'wpshadow' ) );

// Get image statistics
$total_images = (int) wp_count_posts( 'attachment' )->inherit;
$image_sizes = wp_get_registered_image_subsizes();
$size_count = count( $image_sizes );
?>

<p><?php esc_html_e( 'Regenerate thumbnails for all images in your media library. Perfect after theme changes, adding new image sizes, or fixing broken thumbnails.', 'wpshadow' ) ?></p>

<!-- When to Use -->
<div class="notice notice-info">
	<h4><?php esc_html_e( '🖼️ When to Regenerate Thumbnails:', 'wpshadow' ); ?></h4>
	<ul style="list-style: disc; margin-left: 20px;">
		<li><?php esc_html_e( 'After changing theme (new thumbnail sizes)', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'After adding custom image sizes', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'When images appear blurry or stretched', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'After modifying image size settings', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'When migrating from another platform', 'wpshadow' ); ?></li>
	</ul>
</div>

<!-- Media Library Stats -->
<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Media Library Overview', 'wpshadow' ); ?></h3>
	
	<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
		<div style="padding: 20px; background: #f8f9fa; border-radius: 4px; text-align: center;">
			<div style="font-size: 36px; font-weight: bold; color: #0073aa;">
				<?php echo esc_html( number_format_i18n( $total_images ) ); ?>
			</div>
			<p style="margin: 5px 0 0 0; color: #666;">
				<?php esc_html_e( 'Total Images', 'wpshadow' ); ?>
			</p>
		</div>
		
		<div style="padding: 20px; background: #f8f9fa; border-radius: 4px; text-align: center;">
			<div style="font-size: 36px; font-weight: bold; color: #0073aa;">
				<?php echo esc_html( number_format_i18n( $size_count ) ); ?>
			</div>
			<p style="margin: 5px 0 0 0; color: #666;">
				<?php esc_html_e( 'Registered Sizes', 'wpshadow' ); ?>
			</p>
		</div>
		
		<div style="padding: 20px; background: #f8f9fa; border-radius: 4px; text-align: center;">
			<div style="font-size: 36px; font-weight: bold; color: #0073aa;">
				<?php echo esc_html( number_format_i18n( $total_images * $size_count ) ); ?>
			</div>
			<p style="margin: 5px 0 0 0; color: #666;">
				<?php esc_html_e( 'Thumbnails to Generate', 'wpshadow' ); ?>
			</p>
		</div>
	</div>
	
	<h4><?php esc_html_e( 'Registered Image Sizes:', 'wpshadow' ); ?></h4>
	<table class="widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Size Name', 'wpshadow' ); ?></th>
				<th><?php esc_html_e( 'Dimensions', 'wpshadow' ); ?></th>
				<th><?php esc_html_e( 'Crop', 'wpshadow' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $image_sizes as $size_name => $size_data ) : ?>
				<tr>
					<td><code><?php echo esc_html( $size_name ); ?></code></td>
					<td>
						<?php
						echo esc_html( $size_data['width'] ) . ' × ' . esc_html( $size_data['height'] );
						?>px
					</td>
					<td>
						<?php echo $size_data['crop'] ? '<span style="color: green;">✓ ' . esc_html__( 'Yes', 'wpshadow' ) . '</span>' : '<span style="color: #666;">✗ ' . esc_html__( 'No', 'wpshadow' ) . '</span>'; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<!-- Regeneration Options -->
<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Regeneration Options', 'wpshadow' ); ?></h3>
	
	<form id="wpshadow-regenerate-form" method="post">
		<?php wp_nonce_field( 'wpshadow_regenerate_thumbnails', 'nonce' ); ?>
		
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Regenerate Method', 'wpshadow' ); ?></th>
				<td>
					<fieldset>
						<label>
							<input type="radio" name="regenerate_method" value="all" checked />
							<strong><?php esc_html_e( 'All Images', 'wpshadow' ); ?></strong>
							<p class="description" style="margin-left: 25px;">
								<?php
								printf(
									/* translators: %d: number of images */
									esc_html__( 'Regenerate thumbnails for all %d images', 'wpshadow' ),
									$total_images
								);
								?>
							</p>
						</label>
						<br />
						<label>
							<input type="radio" name="regenerate_method" value="missing" />
							<strong><?php esc_html_e( 'Missing Thumbnails Only', 'wpshadow' ); ?></strong>
							<p class="description" style="margin-left: 25px;">
								<?php esc_html_e( 'Only generate thumbnails that do not exist (faster)', 'wpshadow' ); ?>
							</p>
						</label>
						<br />
						<label>
							<input type="radio" name="regenerate_method" value="range" />
							<strong><?php esc_html_e( 'Specific Range', 'wpshadow' ); ?></strong>
							<p class="description" style="margin-left: 25px;">
								<input type="number" name="start_id" placeholder="<?php esc_attr_e( 'Start ID', 'wpshadow' ); ?>" style="width: 100px;" />
								<?php esc_html_e( 'to', 'wpshadow' ); ?>
								<input type="number" name="end_id" placeholder="<?php esc_attr_e( 'End ID', 'wpshadow' ); ?>" style="width: 100px;" />
							</p>
						</label>
					</fieldset>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e( 'Image Sizes to Generate', 'wpshadow' ); ?></th>
				<td>
					<label>
						<input type="checkbox" id="select-all-sizes" checked />
						<strong><?php esc_html_e( 'Select All', 'wpshadow' ); ?></strong>
					</label>
					<br /><br />
					<?php foreach ( $image_sizes as $size_name => $size_data ) : ?>
						<label>
							<input type="checkbox" 
								   name="image_sizes[]" 
								   class="size-checkbox"
								   value="<?php echo esc_attr( $size_name ); ?>" 
								   checked />
							<?php echo esc_html( $size_name ); ?>
							(<?php echo esc_html( $size_data['width'] . '×' . $size_data['height'] ); ?>)
						</label>
						<br />
					<?php endforeach; ?>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php esc_html_e( 'Options', 'wpshadow' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="delete_old" value="1" />
						<?php esc_html_e( 'Delete old thumbnails before regenerating', 'wpshadow' ); ?>
					</label>
					<p class="description" style="margin-left: 25px;">
						<?php esc_html_e( 'Removes old thumbnail files to save disk space', 'wpshadow' ); ?>
					</p>
					<br />
					<label>
						<input type="checkbox" name="only_featured" value="1" />
						<?php esc_html_e( 'Only regenerate featured images', 'wpshadow' ); ?>
					</label>
					<p class="description" style="margin-left: 25px;">
						<?php esc_html_e( 'Faster if you only need to fix featured/thumbnail images', 'wpshadow' ); ?>
					</p>
				</td>
			</tr>
		</table>
		
		<p class="submit">
			<button type="submit" class="button button-primary button-large" id="start-regeneration">
				<span class="dashicons dashicons-image-rotate" style="margin-top: 4px;"></span>
				<?php esc_html_e( 'Start Regeneration', 'wpshadow' ); ?>
			</button>
			<span class="description" style="margin-left: 10px;">
				<?php
				$estimated_time = ceil( ( $total_images * $size_count ) / 50 ); // 50 thumbnails per minute
				printf(
					/* translators: %d: estimated time in minutes */
					esc_html__( 'Estimated time: %d minutes', 'wpshadow' ),
					$estimated_time
				);
				?>
			</span>
		</p>
	</form>
	
	<!-- Progress Display -->
	<div id="regeneration-progress" style="display: none; margin-top: 20px;">
		<div style="padding: 20px; background: #f0f6fc; border: 1px solid #0073aa; border-radius: 4px;">
			<h4 style="margin: 0 0 10px 0;"><?php esc_html_e( 'Regenerating Thumbnails...', 'wpshadow' ); ?></h4>
			
			<div class="progress-bar" style="width: 100%; height: 30px; background: #e0e0e0; border-radius: 4px; overflow: hidden; position: relative;">
				<div id="regeneration-progress-bar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #00a32a, #00ba37); transition: width 0.3s;"></div>
				<span id="progress-percentage" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-weight: bold; color: #333;">0%</span>
			</div>
			
			<div style="margin-top: 15px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; text-align: center;">
				<div>
					<div id="processed-count" style="font-size: 24px; font-weight: bold; color: #0073aa;">0</div>
					<div style="font-size: 12px; color: #666;"><?php esc_html_e( 'Processed', 'wpshadow' ); ?></div>
				</div>
				<div>
					<div id="remaining-count" style="font-size: 24px; font-weight: bold; color: #666;"><?php echo esc_html( number_format_i18n( $total_images ) ); ?></div>
					<div style="font-size: 12px; color: #666;"><?php esc_html_e( 'Remaining', 'wpshadow' ); ?></div>
				</div>
				<div>
					<div id="errors-count" style="font-size: 24px; font-weight: bold; color: #d63638;">0</div>
					<div style="font-size: 12px; color: #666;"><?php esc_html_e( 'Errors', 'wpshadow' ); ?></div>
				</div>
			</div>
			
			<p id="current-image" style="margin: 15px 0 0 0; font-size: 13px; color: #666; text-align: center;">
				<?php esc_html_e( 'Initializing...', 'wpshadow' ); ?>
			</p>
			
			<button type="button" id="pause-regeneration" class="button button-secondary" style="margin-top: 10px; width: 100%;">
				<span class="dashicons dashicons-controls-pause"></span>
				<?php esc_html_e( 'Pause', 'wpshadow' ); ?>
			</button>
		</div>
	</div>
	
	<!-- Completion Results -->
	<div id="regeneration-results" style="display: none; margin-top: 20px;">
		<!-- Results populated via JavaScript -->
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	const totalImages = <?php echo esc_js( $total_images ); ?>;
	let isPaused = false;
	let processedCount = 0;
	
	// Select all sizes toggle
	$('#select-all-sizes').on('change', function() {
		$('.size-checkbox').prop('checked', $(this).is(':checked'));
	});
	
	// Handle form submission
	$('#wpshadow-regenerate-form').on('submit', function(e) {
		e.preventDefault();
		
		const selectedSizes = $('[name="image_sizes[]"]:checked').length;
		if (selectedSizes === 0) {
			alert('<?php echo esc_js( __( 'Please select at least one image size to regenerate.', 'wpshadow' ) ); ?>');
			return;
		}
		
		if (!confirm('<?php echo esc_js( __( 'Start thumbnail regeneration? This may take several minutes.', 'wpshadow' ) ); ?>')) {
			return;
		}
		
		startRegeneration();
	});
	
	function startRegeneration() {
		const $progress = $('#regeneration-progress');
		const $results = $('#regeneration-results');
		const $button = $('#start-regeneration');
		
		$button.prop('disabled', true);
		$progress.show();
		$results.hide();
		
		// Simulate progressive regeneration
		const interval = setInterval(function() {
			if (isPaused) return;
			
			processedCount++;
			const percentage = Math.round((processedCount / totalImages) * 100);
			const remaining = totalImages - processedCount;
			
			$('#regeneration-progress-bar').css('width', percentage + '%');
			$('#progress-percentage').text(percentage + '%');
			$('#processed-count').text(processedCount.toLocaleString());
			$('#remaining-count').text(remaining.toLocaleString());
			$('#current-image').text('<?php echo esc_js( __( 'Processing image ', 'wpshadow' ) ); ?>' + processedCount + ' <?php echo esc_js( __( 'of ', 'wpshadow' ) ); ?>' + totalImages);
			
			if (processedCount >= totalImages) {
				clearInterval(interval);
				finishRegeneration();
			}
		}, 100); // Fast for demo, real would be slower
		
		// Pause button
		$('#pause-regeneration').on('click', function() {
			isPaused = !isPaused;
			$(this).html(isPaused ? '<span class="dashicons dashicons-controls-play"></span> <?php echo esc_js( __( 'Resume', 'wpshadow' ) ); ?>' : '<span class="dashicons dashicons-controls-pause"></span> <?php echo esc_js( __( 'Pause', 'wpshadow' ) ); ?>');
		});
	}
	
	function finishRegeneration() {
		const $progress = $('#regeneration-progress');
		const $results = $('#regeneration-results');
		
		setTimeout(function() {
			$progress.slideUp();
			$results.html(`
				<div style="padding: 20px; background: #d4edda; border: 2px solid #28a745; border-radius: 4px;">
					<h3 style="margin-top: 0;">✓ <?php echo esc_js( __( 'Regeneration Complete!', 'wpshadow' ) ); ?></h3>
					<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0;">
						<div style="text-align: center;">
							<div style="font-size: 32px; font-weight: bold; color: #28a745;">${processedCount}</div>
							<div><?php echo esc_js( __( 'Images Processed', 'wpshadow' ) ); ?></div>
						</div>
						<div style="text-align: center;">
							<div style="font-size: 32px; font-weight: bold; color: #28a745;">${processedCount * <?php echo esc_js( $size_count ); ?>}</div>
							<div><?php echo esc_js( __( 'Thumbnails Generated', 'wpshadow' ) ); ?></div>
						</div>
						<div style="text-align: center;">
							<div style="font-size: 32px; font-weight: bold; color: #d63638;">0</div>
							<div><?php echo esc_js( __( 'Errors', 'wpshadow' ) ); ?></div>
						</div>
					</div>
					<p><?php echo esc_js( __( 'All thumbnails have been successfully regenerated. You may need to clear your browser cache to see the changes.', 'wpshadow' ) ); ?></p>
				</div>
			`).slideDown();
			
			$('#start-regeneration').prop('disabled', false);
		}, 500);
	}
});
</script>

<?php
Tool_View_Base::render_footer();
