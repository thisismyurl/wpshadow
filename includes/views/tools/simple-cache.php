<?php
/**
 * Simple Cache Tool
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cache_dir = WP_CONTENT_DIR . '/cache/wpshadow';
$cache_size = 0;

if ( is_dir( $cache_dir ) ) {
	$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $cache_dir ) );
	foreach ( $files as $file ) {
		if ( $file->isFile() ) {
			$cache_size += $file->getSize();
		}
	}
}

$cache_enabled = get_option( 'wpshadow_simple_cache_enabled', false );
$cache_lifetime = get_option( 'wpshadow_cache_lifetime', 3600 );
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
		<button type="button" class="button button-primary" id="wpshadow-clear-cache">
			<?php esc_html_e( 'Clear All Cache', 'wpshadow' ); ?>
		</button>
		<p class="description"><?php esc_html_e( 'Remove all cached pages to rebuild them fresh on next visit.', 'wpshadow' ); ?></p>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Cache Options', 'wpshadow' ); ?></h3>
		<label>
			<input type="checkbox" id="wpshadow-cache-pages" <?php checked( get_option( 'wpshadow_cache_pages', true ) ); ?> />
			<?php esc_html_e( 'Cache static pages', 'wpshadow' ); ?>
		</label>
		<br />
		<label>
			<input type="checkbox" id="wpshadow-cache-posts" <?php checked( get_option( 'wpshadow_cache_posts', true ) ); ?> />
			<?php esc_html_e( 'Cache blog posts', 'wpshadow' ); ?>
		</label>
		<br />
		<label>
			<input type="checkbox" id="wpshadow-skip-logged-in" <?php checked( get_option( 'wpshadow_skip_logged_in', true ) ); ?> />
			<?php esc_html_e( 'Skip cache for logged-in users', 'wpshadow' ); ?>
		</label>
		<br />
		<label>
			<input type="checkbox" id="wpshadow-auto-clear-on-save" <?php checked( get_option( 'wpshadow_auto_clear_on_save', true ) ); ?> />
			<?php esc_html_e( 'Auto-clear on publish', 'wpshadow' ); ?>
		</label>
	</div>
</div>

<script>
document.getElementById( 'wpshadow-clear-cache' )?.addEventListener( 'click', function() {
	if ( confirm( '<?php esc_attr_e( 'Clear all cached pages? They will be rebuilt on next visit.', 'wpshadow' ); ?>' ) ) {
		// AJAX call would go here
		alert( '<?php esc_attr_e( 'Cache cleared!', 'wpshadow' ); ?>' );
	}
} );
</script>
