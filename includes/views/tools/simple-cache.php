<?php
/**
 * Simple Cache Tool
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cache_dir  = WP_CONTENT_DIR . '/cache/wpshadow';
$cache_size = 0;

if ( is_dir( $cache_dir ) ) {
	$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $cache_dir ) );
	foreach ( $files as $file ) {
		if ( $file->isFile() ) {
			$cache_size += $file->getSize();
		}
	}
}

$cache_enabled  = get_option( 'wpshadow_simple_cache_enabled', false );
$cache_lifetime = get_option( 'wpshadow_cache_lifetime', 3600 );
$cache_pages    = get_option( 'wpshadow_cache_pages', true );
$cache_posts    = get_option( 'wpshadow_cache_posts', true );
$skip_logged_in = get_option( 'wpshadow_skip_logged_in', true );
$auto_clear     = get_option( 'wpshadow_auto_clear_on_save', true );
?>

<div class="wpshadow-tool-container">
	<h2><?php esc_html_e( 'Page Cache Manager', 'wpshadow' ); ?></h2>
	<p><?php esc_html_e( 'Save copies of your pages so they load instantly for visitors.', 'wpshadow' ); ?></p>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Cache Status', 'wpshadow' ); ?></h3>
		<table class="widefat">
			<tr>
				<td><strong><?php esc_html_e( 'Cache Enabled', 'wpshadow' ); ?></strong></td>
				<td><?php echo $cache_enabled ? '<span style="color: green;">✓ ' . esc_html__( 'Yes', 'wpshadow' ) . '</span>' : '<span style="color: red;">✗ ' . esc_html__( 'No', 'wpshadow' ) . '</span>'; ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Cache Size', 'wpshadow' ); ?></strong></td>
				<td><?php echo esc_html( size_format( $cache_size, 2 ) ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Cache Lifetime', 'wpshadow' ); ?></strong></td>
				<td><?php echo esc_html( gmdate( 'H:i', $cache_lifetime ) ); ?></td>
			</tr>
		</table>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Cache Actions', 'wpshadow' ); ?></h3>
		<button type="button" class="button button-primary" id="wpshadow-clear-cache" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_cache_nonce' ) ); ?>">
			<?php esc_html_e( 'Clear All Cache', 'wpshadow' ); ?>
		</button>
		<p class="description"><?php esc_html_e( 'Remove all cached pages to rebuild them fresh on next visit.', 'wpshadow' ); ?></p>
		<div id="wpshadow-cache-message" style="margin-top: 10px; display: none;"></div>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Cache Options', 'wpshadow' ); ?></h3>
		<form id="wpshadow-cache-options-form">
			<?php wp_nonce_field( 'wpshadow_cache_options', 'wpshadow_cache_options_nonce' ); ?>
			<label>
				<input type="checkbox" id="wpshadow-cache-pages" name="cache_pages" value="1" <?php checked( $cache_pages ); ?> />
				<?php esc_html_e( 'Cache static pages', 'wpshadow' ); ?>
			</label>
			<br />
			<label>
				<input type="checkbox" id="wpshadow-cache-posts" name="cache_posts" value="1" <?php checked( $cache_posts ); ?> />
				<?php esc_html_e( 'Cache blog posts', 'wpshadow' ); ?>
			</label>
			<br />
			<label>
				<input type="checkbox" id="wpshadow-skip-logged-in" name="skip_logged_in" value="1" <?php checked( $skip_logged_in ); ?> />
				<?php esc_html_e( 'Skip cache for logged-in users', 'wpshadow' ); ?>
			</label>
			<br />
			<label>
				<input type="checkbox" id="wpshadow-auto-clear-on-save" name="auto_clear_on_save" value="1" <?php checked( $auto_clear ); ?> />
				<?php esc_html_e( 'Auto-clear on publish', 'wpshadow' ); ?>
			</label>
			<br /><br />
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'Save Cache Settings', 'wpshadow' ); ?>
			</button>
			<div id="wpshadow-cache-options-message" style="margin-top: 10px; display: none;"></div>
		</form>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Clear cache
	$('#wpshadow-clear-cache').on('click', function() {
		var $btn = $(this);
		var $message = $('#wpshadow-cache-message');
		var nonce = $btn.data('nonce');

		if (!confirm('<?php esc_attr_e( 'Clear all cached pages? They will be rebuilt on next visit.', 'wpshadow' ); ?>')) {
			return;
		}

		$btn.prop('disabled', true).text('<?php esc_attr_e( 'Clearing...', 'wpshadow' ); ?>');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpshadow_clear_cache',
				nonce: nonce
			},
			success: function(response) {
				if (response.success) {
					$message
						.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>')
						.show();
					setTimeout(function() {
						location.reload();
					}, 1500);
				} else {
					$message
						.html('<div class="notice notice-error"><p>' + (response.data && response.data.message ? response.data.message : '<?php esc_attr_e( 'Error clearing cache.', 'wpshadow' ); ?>') + '</p></div>')
						.show();
					$btn.prop('disabled', false).text('<?php esc_attr_e( 'Clear All Cache', 'wpshadow' ); ?>');
				}
			},
			error: function() {
				$message
					.html('<div class="notice notice-error"><p><?php esc_attr_e( 'Error clearing cache.', 'wpshadow' ); ?></p></div>')
					.show();
				$btn.prop('disabled', false).text('<?php esc_attr_e( 'Clear All Cache', 'wpshadow' ); ?>');
			}
		});
	});

	// Save cache options
	$('#wpshadow-cache-options-form').on('submit', function(e) {
		e.preventDefault();
		var $form = $(this);
		var $message = $('#wpshadow-cache-options-message');
		var $btn = $form.find('button[type="submit"]');

		var data = {
			action: 'wpshadow_save_cache_options',
			nonce: $form.find('[name="wpshadow_cache_options_nonce"]').val(),
			cache_pages: $form.find('[name="cache_pages"]').is(':checked') ? 1 : 0,
			cache_posts: $form.find('[name="cache_posts"]').is(':checked') ? 1 : 0,
			skip_logged_in: $form.find('[name="skip_logged_in"]').is(':checked') ? 1 : 0,
			auto_clear_on_save: $form.find('[name="auto_clear_on_save"]').is(':checked') ? 1 : 0
		};

		$btn.prop('disabled', true).text('<?php esc_attr_e( 'Saving...', 'wpshadow' ); ?>');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function(response) {
				if (response.success) {
					$message
						.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>')
						.show()
						.delay(3000)
						.fadeOut();
				} else {
					$message
						.html('<div class="notice notice-error"><p>' + (response.data && response.data.message ? response.data.message : '<?php esc_attr_e( 'Error saving settings.', 'wpshadow' ); ?>') + '</p></div>')
						.show();
				}
				$btn.prop('disabled', false).text('<?php esc_attr_e( 'Save Cache Settings', 'wpshadow' ); ?>');
			},
			error: function() {
				$message
					.html('<div class="notice notice-error"><p><?php esc_attr_e( 'Error saving settings.', 'wpshadow' ); ?></p></div>')
					.show();
				$btn.prop('disabled', false).text('<?php esc_attr_e( 'Save Cache Settings', 'wpshadow' ); ?>');
			}
		});
	});
});
</script>
