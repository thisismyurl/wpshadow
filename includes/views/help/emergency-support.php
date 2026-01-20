<?php
/**
 * Emergency Support Page
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'read' ) ) {
	wp_die( 'Insufficient permissions.' );
}

// Get recent critical errors
$critical_errors = get_option( 'wpshadow_critical_errors', array() );
$recent_errors = array_slice( array_reverse( $critical_errors ), 0, 5 );
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Emergency Support', 'wpshadow' ); ?></h1>
	<p><?php esc_html_e( 'Monitor critical errors and access emergency recovery options.', 'wpshadow' ); ?></p>

	<?php if ( ! empty( $recent_errors ) ) : ?>
		<div class="notice notice-error" style="margin-top: 20px;">
			<p><strong><?php esc_html_e( 'Recent critical errors detected!', 'wpshadow' ); ?></strong></p>
		</div>
	<?php endif; ?>

	<div class="wpshadow-tool-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px;">
		<h2><?php esc_html_e( 'Recent Critical Errors', 'wpshadow' ); ?></h2>
		
		<?php if ( empty( $recent_errors ) ) : ?>
			<p style="color: #00a32a;"><?php esc_html_e( '✓ No critical errors detected in the last 24 hours.', 'wpshadow' ); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Time', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Severity', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Message', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Location', 'wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent_errors as $error ) : ?>
						<tr>
							<td><?php echo esc_html( wp_date( 'Y-m-d H:i:s', $error['timestamp'] ) ); ?></td>
							<td><span style="color: #d63638;"><?php echo esc_html( $error['severity'] ?? 'ERROR' ); ?></span></td>
							<td><?php echo esc_html( $error['message'] ?? 'Unknown error' ); ?></td>
							<td><code><?php echo esc_html( ( $error['file'] ?? '' ) . ':' . ( $error['line'] ?? 0 ) ); ?></code></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<div class="wpshadow-tool-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px;">
		<h2><?php esc_html_e( 'Emergency Actions', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'Quick actions to recover from critical issues:', 'wpshadow' ); ?></p>
		
		<p>
			<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="button">
				<?php esc_html_e( 'Disable Plugins', 'wpshadow' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>" class="button">
				<?php esc_html_e( 'Switch Theme', 'wpshadow' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>" class="button">
				<?php esc_html_e( 'Check Settings', 'wpshadow' ); ?>
			</a>
		</p>
	</div>

	<div class="wpshadow-tool-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px;">
		<h2><?php esc_html_e( 'Get Help', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'If you need immediate assistance:', 'wpshadow' ); ?></p>
		<ul>
			<li><a href="https://github.com/thisismyurl/wpshadow/issues" target="_blank"><?php esc_html_e( 'Report an Issue on GitHub', 'wpshadow' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>"><?php esc_html_e( 'WordPress Site Health', 'wpshadow' ); ?></a></li>
		</ul>
	</div>
</div>
