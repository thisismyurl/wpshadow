<?php
/**
 * WPShadow Cache Tool
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access
Tool_View_Base::verify_access( 'manage_options' );

// Enqueue assets
Tool_View_Base::enqueue_assets( 'simple-cache' );

// Render header
Tool_View_Base::render_header( __( 'WPShadow Cache', 'wpshadow' ) );

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

$cache_dir_exists   = is_dir( $cache_dir );
$cache_dir_writable = function_exists( 'wp_is_writable' ) ? wp_is_writable( $cache_dir_exists ? $cache_dir : dirname( $cache_dir ) ) : is_writable( $cache_dir_exists ? $cache_dir : dirname( $cache_dir ) );
$fs_method          = function_exists( 'get_filesystem_method' ) ? get_filesystem_method() : 'direct';

$activity_result = \WPShadow\Core\Activity_Logger::get_activities( array( 'category' => 'performance' ), 5, 0 );
$activity_items  = isset( $activity_result['activities'] ) ? $activity_result['activities'] : array();

$kanban_cards = array(
	array(
		'title'  => __( 'Cache directory writable', 'wpshadow' ),
		'body'   => $cache_dir_writable ? __( 'We can write to the cache directory.', 'wpshadow' ) : __( 'Cache directory is not writable. Please check permissions for wp-content/cache/', 'wpshadow' ),
		'status' => $cache_dir_writable ? 'ok' : 'issue',
		'action' => ! $cache_dir_writable && 'direct' === $fs_method ? 'fix_permissions' : '',
	),
	array(
		'title'  => __( 'Filesystem access', 'wpshadow' ),
		'body'   => 'direct' === $fs_method ? __( 'Direct filesystem access available.', 'wpshadow' ) : sprintf( __( 'Using %s method. If cache clear fails, check credentials.', 'wpshadow' ), esc_html( $fs_method ) ),
		'status' => 'direct' === $fs_method ? 'ok' : 'check',
	),
	array(
		'title'  => __( 'Cache toggle', 'wpshadow' ),
		'body'   => $cache_enabled ? __( 'Caching is currently enabled.', 'wpshadow' ) : __( 'Caching is disabled. Enable it below.', 'wpshadow' ),
		'status' => $cache_enabled ? 'ok' : 'action',
	),
);
?>

<div class="wpshadow-tool-container">
	<h2><?php esc_html_e( 'WPShadow Cache', 'wpshadow' ); ?></h2>
	<p><?php esc_html_e( 'Save copies of your pages so they load instantly for visitors.', 'wpshadow' ); ?></p>

	<style>
		.wps-kanban { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 8px; }
		.wps-kanban-card { background: #fff; border: 1px solid #dcdcde; border-left: 4px solid #2271b1; padding: 12px; width: 260px; box-sizing: border-box; }
		.wps-kanban-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #555; margin-bottom: 6px; }
		.wps-kanban-card.wps-status-ok { border-left-color: #46b450; }
		.wps-kanban-card.wps-status-issue { border-left-color: #d63638; }
		.wps-kanban-card.wps-status-action { border-left-color: #d98300; }
	</style>

	<div class="notice notice-info" style="margin-top:10px;">
		<p><strong><?php esc_html_e( 'Enable caching to speed up your site.', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Use the switch below to turn on the page cache and choose what gets cached.', 'wpshadow' ); ?></p>
	</div>

	<div class="notice notice-success" style="margin-top:10px;">
		<p><strong><?php esc_html_e( 'Free Offsite Storage for Registered Users', 'wpshadow' ); ?></strong></p>
		<p><?php esc_html_e( 'When you register for WPShadow (free!), you get secure offsite storage for your last three WPShadow Vault Light snapshots and free restores whenever you need them.', 'wpshadow' ); ?> <a href="https://wpshadow.com/features/offsite-backup/" target="_blank"><?php esc_html_e( 'Learn more', 'wpshadow' ); ?></a></p>
	</div>

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
			<tr>
				<td><strong><?php esc_html_e( 'Cache Directory', 'wpshadow' ); ?></strong></td>
				<td>
					<?php
					if ( $cache_dir_exists ) {
						echo $cache_dir_writable ? '<span style="color: green;">' . esc_html__( 'Writable', 'wpshadow' ) . '</span>' : '<span style="color: red;">' . esc_html__( 'Not writable', 'wpshadow' ) . '</span>';
					} else {
						echo '<span style="color: #d98300;">' . esc_html__( 'Will be created on first cache', 'wpshadow' ) . '</span>';
					}
					?>
				</td>
			</tr>
		</table>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Health & Checks', 'wpshadow' ); ?></h3>
		<div class="wps-kanban">
			<?php
			foreach ( $kanban_cards as $card ) :
				$status_label = $card['status'];
				$label_text   = '';
				if ( 'ok' === $status_label ) {
					$label_text = __( 'OK', 'wpshadow' );
				} elseif ( 'check' === $status_label ) {
					$label_text = __( 'Check', 'wpshadow' );
				} else {
					$label_text = __( 'Action needed', 'wpshadow' );
				}
				?>
				<div class="wps-kanban-card wps-status-<?php echo esc_attr( $status_label ); ?>">
					<div class="wps-kanban-label"><?php echo esc_html( $label_text ); ?></div>
					<h4><?php echo esc_html( $card['title'] ); ?></h4>
					<p><?php echo esc_html( $card['body'] ); ?></p>
					<?php if ( ! empty( $card['action'] ) && 'fix_permissions' === $card['action'] ) : ?>
						<button type="button" class="button button-small wpshadow-fix-permissions" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_fix_permissions' ) ); ?>" style="margin-top: 8px;">
							<?php esc_html_e( 'Fix Permissions', 'wpshadow' ); ?>
						</button>
						<p class="description" style="margin-top: 4px; font-size: 11px;">
							<?php
							printf(
								/* translators: %s: KB article URL */
								__( 'Or <a href="%s" target="_blank">learn how to fix manually</a>', 'wpshadow' ),
								'https://wpshadow.com/kb/cache-directory-permissions'
							);
							?>
						</p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Cache Actions', 'wpshadow' ); ?></h3>
		<button type="button" class="wps-btn wps-btn-primary wps-btn-icon-left" id="wpshadow-clear-cache" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_cache_nonce' ) ); ?>"><span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Clear All Cache', 'wpshadow' ); ?>
		</button>
		<p class="description"><?php esc_html_e( 'Remove all cached pages to rebuild them fresh on next visit.', 'wpshadow' ); ?></p>
		<div id="wpshadow-cache-message" class="wps-none"></div>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Cache Options', 'wpshadow' ); ?></h3>
		<form id="wpshadow-cache-options-form">
			<?php wp_nonce_field( 'wpshadow_cache_options', 'wpshadow_cache_options_nonce' ); ?>
			<label>
				<input type="checkbox" id="wpshadow-cache-enabled" name="cache_enabled" value="1" <?php checked( $cache_enabled ); ?> />
				<?php esc_html_e( 'Enable page caching', 'wpshadow' ); ?>
			</label>
			<p class="description" style="margin-top:0;">
				<?php esc_html_e( 'Turn this on to start caching pages. You can still clear and adjust options below.', 'wpshadow' ); ?>
			</p>
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
			<button type="submit" class="wps-btn wps-btn-primary">
				<?php esc_html_e( 'Save Cache Settings', 'wpshadow' ); ?>
			</button>
			<div id="wpshadow-cache-options-message" class="wps-none"></div>
		</form>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Activity History', 'wpshadow' ); ?></h3>
		<?php if ( ! empty( $activity_items ) ) : ?>
			<ul class="wps-activity-list">
				<?php foreach ( $activity_items as $activity ) : ?>
					<li>
						<strong><?php echo esc_html( $activity['details'] ); ?></strong>
						<div class="description">
							<?php
							printf(
								/* translators: 1: user name, 2: time since. */
								esc_html__( 'By %1$s • %2$s ago', 'wpshadow' ),
								esc_html( $activity['user_name'] ),
								esc_html( human_time_diff( $activity['timestamp'], current_time( 'timestamp' ) ) )
							);
							?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="description"><?php esc_html_e( 'No cache activity yet. Changes you make here will appear in this timeline.', 'wpshadow' ); ?></p>
		<?php endif; ?>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Fix permissions
	$('.wpshadow-fix-permissions').on('click', function() {
		var $btn = $(this);
		var nonce = $btn.data('nonce');
		var originalText = $btn.text();

		$btn.prop('disabled', true).text('<?php esc_attr_e( 'Fixing...', 'wpshadow' ); ?>');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpshadow_fix_cache_permissions',
				nonce: nonce
			},
			success: function(response) {
				if (response.success) {
					$btn.text('<?php esc_attr_e( '✓ Fixed', 'wpshadow' ); ?>').css('background-color', '#46b450');
					setTimeout(function() {
						location.reload();
					}, 1500);
				} else {
					alert(response.data && response.data.message ? response.data.message : '<?php esc_attr_e( 'Could not fix permissions automatically. Please check the KB article for manual instructions.', 'wpshadow' ); ?>');
					$btn.prop('disabled', false).text(originalText);
				}
			},
			error: function() {
				alert('<?php esc_attr_e( 'Error attempting to fix permissions.', 'wpshadow' ); ?>');
				$btn.prop('disabled', false).text(originalText);
			}
		});
	});

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
			cache_enabled: $form.find('[name="cache_enabled"]').is(':checked') ? 1 : 0,
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

<?php
// Load and render sales widget
require_once WPSHADOW_PATH . 'includes/views/components/sales-widget.php';

wpshadow_render_sales_widget(
	array(
		'title'       => __( 'Supercharge Your Backups with WPShadow Pro', 'wpshadow' ),
		'description' => __( 'WPShadow Pro and the WPShadow Vault module make backups a breeze with automated schedules, cloud storage, and one-click restores.', 'wpshadow' ),
		'features'    => array(
			__( 'Automated backup scheduling', 'wpshadow' ),
			__( 'Unlimited cloud storage with Vault', 'wpshadow' ),
			__( 'One-click restore from any backup', 'wpshadow' ),
			__( 'Off-site storage for disaster recovery', 'wpshadow' ),
			__( 'Priority support and updates', 'wpshadow' ),
		),
		'cta_text'    => __( 'Learn More About WPShadow Pro & Vault', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-database-export',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
