<?php
/**
 * Customization Audit Report
 *
 * Analyzes custom themes, plugins, and code modifications.
 *
 * @package WPShadow
 * @subpackage Reports
 * @since 1.2602.0000
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Enqueue assets
Tool_View_Base::enqueue_assets( 'customization-audit' );

// Render header
Tool_View_Base::render_header( __( 'Customization Audit', 'wpshadow' ), __( 'Audit custom WordPress modifications, themes, and plugins to identify potential risks and compliance issues.', 'wpshadow' ) );

$audit_reports = get_option( 'wpshadow_customization_audit_reports', array() );
$latest_report = ! empty( $audit_reports ) ? end( $audit_reports ) : null;
?>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Generate New Audit', 'wpshadow' ); ?></h3>
		<p><?php esc_html_e( 'Run a comprehensive audit of your site customizations to identify:', 'wpshadow' ); ?></p>
		<ul style="list-style: disc; margin-left: 20px;">
			<li><?php esc_html_e( 'Custom theme modifications and child theme changes', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Plugin customizations and custom plugins', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Database schema modifications', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Custom post types and taxonomies', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Potential security and compatibility risks', 'wpshadow' ); ?></li>
		</ul>
		<br />
		<button type="button" class="wps-btn wps-btn-primary wps-btn-icon-left" id="wpshadow-generate-audit"><span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Generate Audit Report', 'wpshadow' ); ?>
		</button>
	</div>

	<?php if ( $latest_report ) : ?>
	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Latest Audit Report', 'wpshadow' ); ?></h3>
		<table class="widefat">
			<tr>
				<td><strong><?php esc_html_e( 'Generated', 'wpshadow' ); ?></strong></td>
				<td><?php echo esc_html( gmdate( 'Y-m-d H:i:s', $latest_report['timestamp'] ?? time() ) ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Risk Level', 'wpshadow' ); ?></strong></td>
				<td>
					<?php
					$risk_level  = $latest_report['overall_risk'] ?? 'low';
					$risk_colors = array(
						'low'    => 'green',
						'medium' => 'orange',
						'high'   => 'red',
					);
					$color       = $risk_colors[ $risk_level ] ?? 'gray';
					?>
					<span style="color: <?php echo esc_attr( $color ); ?>; font-weight: bold;">
						<?php echo esc_html( strtoupper( $risk_level ) ); ?>
					</span>
				</td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Custom Themes', 'wpshadow' ); ?></strong></td>
				<td><?php echo esc_html( $latest_report['custom_themes'] ?? 0 ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Custom Plugins', 'wpshadow' ); ?></strong></td>
				<td><?php echo esc_html( $latest_report['custom_plugins'] ?? 0 ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Database Modifications', 'wpshadow' ); ?></strong></td>
				<td><?php echo esc_html( $latest_report['db_modifications'] ?? 0 ); ?></td>
			</tr>
		</table>
		<br />
		<button type="button" class="wps-btn wps-btn-secondary" id="wpshadow-export-audit">
			<?php esc_html_e( 'Export Report (CSV)', 'wpshadow' ); ?>
		</button>
	</div>
	<?php endif; ?>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Audit History', 'wpshadow' ); ?></h3>
		<?php if ( empty( $audit_reports ) ) : ?>
			<p><?php esc_html_e( 'No audit reports generated yet. Click the button above to create your first report.', 'wpshadow' ); ?></p>
		<?php else : ?>
			<table class="widefat">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Risk Level', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Action', 'wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( array_slice( $audit_reports, -5 ) as $report ) : ?>
						<tr>
							<td><?php echo esc_html( gmdate( 'Y-m-d H:i', $report['timestamp'] ?? 0 ) ); ?></td>
							<td>
								<?php
								$risk  = $report['overall_risk'] ?? 'low';
								$color = $risk_colors[ $risk ] ?? 'gray';
								?>
								<span style="color: <?php echo esc_attr( $color ); ?>;">
									<?php echo esc_html( ucfirst( $risk ) ); ?>
								</span>
							</td>
							<td><?php echo esc_html( $report['total_issues'] ?? 0 ); ?></td>
							<td>
								<button type="button" class="wps-btn wps-btn-secondary wpshadow-view-report" data-report-id="<?php echo esc_attr( $report['id'] ?? '' ); ?>">
									<?php esc_html_e( 'View', 'wpshadow' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Risk Categories', 'wpshadow' ); ?></h3>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Risk Level', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Description', 'wpshadow' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><span style="color: green; font-weight: bold;">LOW</span></td>
					<td><?php esc_html_e( 'Minor customizations that follow WordPress standards and best practices', 'wpshadow' ); ?></td>
				</tr>
				<tr>
					<td><span style="color: orange; font-weight: bold;">MEDIUM</span></td>
					<td><?php esc_html_e( 'Customizations that may cause compatibility issues or require monitoring', 'wpshadow' ); ?></td>
				</tr>
				<tr>
					<td><span style="color: red; font-weight: bold;">HIGH</span></td>
					<td><?php esc_html_e( 'Critical customizations that pose security risks or violate WordPress standards', 'wpshadow' ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<script>
document.getElementById( 'wpshadow-generate-audit' )?.addEventListener( 'click', function() {
	this.disabled = true;
	this.textContent = '<?php esc_attr_e( 'Generating...', 'wpshadow' ); ?>';
	// AJAX call would go here
	setTimeout( function() {
		WPShadowModal.alert({
			title: '<?php esc_attr_e( 'Coming Soon', 'wpshadow' ); ?>',
			message: '<?php esc_attr_e( 'Audit generation feature coming soon!', 'wpshadow' ); ?>',
			type: 'info'
		});
		location.reload();
	}, 1000 );
} );

document.getElementById( 'wpshadow-export-audit' )?.addEventListener( 'click', function() {
	WPShadowModal.alert({
		title: '<?php esc_attr_e( 'Coming Soon', 'wpshadow' ); ?>',
		message: '<?php esc_attr_e( 'Export feature coming soon!', 'wpshadow' ); ?>',
		type: 'info'
	});
} );

document.querySelectorAll( '.wpshadow-view-report' ).forEach( function( btn ) {
	btn.addEventListener( 'click', function() {
		WPShadowModal.alert({
			title: '<?php esc_attr_e( 'Coming Soon', 'wpshadow' ); ?>',
			message: '<?php esc_attr_e( 'Report viewer coming soon!', 'wpshadow' ); ?>',
			type: 'info'
		});
	} );
} );
</script>

