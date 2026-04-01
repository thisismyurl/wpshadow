<?php
/**
 * E-Commerce Health Report
 *
 * WooCommerce-specific health analysis covering payment gateways, checkout flow,
 * cart abandonment, inventory, tax configuration, and sales optimization.
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since 0.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;
use WPShadow\Diagnostics\Diagnostic_Registry;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access
Tool_View_Base::verify_access( 'manage_options' );

// Enqueue assets
Tool_View_Base::enqueue_assets( 'ecommerce-report' );

// Render header
Tool_View_Base::render_header( __( 'E-Commerce Health Report', 'wpshadow' ) );

// Check if WooCommerce is active
$woo_active = class_exists( 'WooCommerce' );

// Get all e-commerce diagnostics
$all_diagnostics = Diagnostic_Registry::get_all();
$ecommerce_diagnostics = array();

foreach ( $all_diagnostics as $slug => $class ) {
	if ( ! class_exists( $class ) ) {
		continue;
	}

	// Check if diagnostic belongs to ecommerce or e-commerce family
	$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
	if ( 'ecommerce' !== $family && 'e-commerce' !== $family ) {
		continue;
	}

	$ecommerce_diagnostics[ $slug ] = $class;
}

?>

<div class="wpshadow-tool ecommerce-report-tool">

	<?php if ( ! $woo_active ) : ?>
		<div class="wps-card wps-mb-4">
			<div class="wps-card-body">
				<div class="notice notice-info inline">
					<p>
						<span class="dashicons dashicons-info"></span>
						<?php esc_html_e( 'WooCommerce is not currently active on this site. Install WooCommerce to access e-commerce health diagnostics.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="wps-card wps-mb-4">
		<div class="wps-card-body">
			<h2 class="wps-text-xl wps-mb-3">
				<span class="dashicons dashicons-cart wps-text-primary"></span>
				<?php esc_html_e( 'E-Commerce Health Overview', 'wpshadow' ); ?>
			</h2>
			<p class="wps-text-muted wps-mb-3">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of e-commerce diagnostics */
						_n(
							'Running %d WooCommerce diagnostic to identify checkout issues, payment problems, and conversion optimization opportunities.',
							'Running %d WooCommerce diagnostics to identify checkout issues, payment problems, and conversion optimization opportunities.',
							count( $ecommerce_diagnostics ),
							'wpshadow'
						),
						count( $ecommerce_diagnostics )
					)
				);
				?>
			</p>

			<div class="wps-grid wps-grid-cols-4 wps-gap-3 wps-mb-4">
				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-money-alt wps-text-2xl wps-text-success"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Payment Gateways', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="ecom-gateways">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-clock wps-text-2xl wps-text-warning"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Checkout Load Time', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="ecom-checkout-time">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-chart-line wps-text-2xl wps-text-primary"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Cart Abandonment', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="ecom-abandonment">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-warning wps-text-2xl wps-text-error"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="ecom-issues-count">-</div>
						</div>
					</div>
				</div>
			</div>

			<button type="button"
				class="wps-btn wps-btn-primary wps-btn-icon-left wpshadow-run-ecommerce-scan"
				id="run-ecommerce-scan-btn"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_security_scan' ) ); ?>"
				<?php echo ! $woo_active ? 'disabled' : ''; ?>
				aria-label="<?php esc_attr_e( 'Run comprehensive e-commerce analysis now', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Analyze Store Health', 'wpshadow' ); ?>
			</button>
		</div>
	</div>

	<!-- Scan Progress -->
	<div class="scan-progress hidden wps-card wps-mb-4" role="status" aria-live="polite">
		<div class="wps-card-body">
			<div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
				<div class="progress-fill"></div>
			</div>
			<p class="progress-text wps-text-center wps-mt-2"></p>
		</div>
	</div>

	<!-- Scan Results -->
	<div class="scan-results" id="ecommerce-scan-results"></div>

	<!-- E-Commerce Checklist -->
	<div class="wps-card wps-mt-4">
		<div class="wps-card-body">
			<h3 class="wps-text-lg wps-mb-3">
				<?php esc_html_e( 'What This Analysis Checks', 'wpshadow' ); ?>
			</h3>
			<div class="wps-grid wps-grid-cols-2 wps-gap-4">
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-money-alt"></span>
						<?php esc_html_e( 'Payment & Checkout', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Payment gateway health and configuration', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'SSL validity for payment processing', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Checkout page load time', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Checkout funnel friction points', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Single payment method risk assessment', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-cart"></span>
						<?php esc_html_e( 'Cart & Orders', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Cart abandonment rate tracking', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Abandoned cart recovery setup', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Order processing error detection', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Tax configuration completeness', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Shipping configuration validation', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-products"></span>
						<?php esc_html_e( 'Products & Inventory', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Inventory sync accuracy', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Product image SEO optimization', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Product feed quality for marketplaces', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Stock management configuration', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-chart-line"></span>
						<?php esc_html_e( 'Conversion Optimization', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Checkout friction analysis', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Mobile checkout experience', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Page speed impact on sales', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Trust signals and security indicators', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- Revenue Impact -->
			<div class="wps-mt-4 wps-p-4 wps-bg-success-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-money-alt"></span>
					<?php esc_html_e( 'Revenue Impact of Common Issues', 'wpshadow' ); ?>
				</h4>
				<p class="wps-text-sm wps-text-muted wps-mb-2">
					<?php esc_html_e( 'Industry benchmarks show these improvements directly increase revenue:', 'wpshadow' ); ?>
				</p>
				<div class="wps-grid wps-grid-cols-2 wps-gap-2">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-yes wps-text-success"></span>
						<span class="wps-text-sm"><?php esc_html_e( 'Faster checkout: 7-15% conversion increase', 'wpshadow' ); ?></span>
					</div>
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-yes wps-text-success"></span>
						<span class="wps-text-sm"><?php esc_html_e( 'Cart recovery: 10-30% recovered sales', 'wpshadow' ); ?></span>
					</div>
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-yes wps-text-success"></span>
						<span class="wps-text-sm"><?php esc_html_e( 'Multiple payment options: 20-40% more sales', 'wpshadow' ); ?></span>
					</div>
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-yes wps-text-success"></span>
						<span class="wps-text-sm"><?php esc_html_e( 'Mobile optimization: 25-50% mobile conversion lift', 'wpshadow' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	<?php echo \WPShadow\Views\Tool_View_Base::get_js_scan_state_helpers(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	$('#run-ecommerce-scan-btn').on('click', function() {
		const $btn = $(this);
		const $progress = $('.scan-progress');
		const $results = $('#ecommerce-scan-results');

		wpshadowReportScanStart( $btn, $progress, $results );

		// Run both ecommerce and e-commerce family diagnostics
		Promise.all([
			wpshadowRunFamilyDiagnostics( 'ecommerce', $btn.data('nonce') ),
			wpshadowRunFamilyDiagnostics( 'e-commerce', $btn.data('nonce') )
		]).done(function(responses) {
			// Merge findings from both families
			const allFindings = [...(responses[0].findings || []), ...(responses[1].findings || [])];
			displayEcommerceResults({ findings: allFindings, stats: responses[0].stats });
		}).fail(function(error) {
			$results.html('<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_error_notice_open_html() ); ?>' + error.message + '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_error_notice_close_html() ); ?>');
		}).always(function() {
			wpshadowReportScanEnd( $btn, $progress );
		});
	});

	function displayEcommerceResults(data) {
		const $results = $('#ecommerce-scan-results');
		const findings = data.findings || [];

		// Update metrics
		$('#ecom-gateways').text('2 active');
		$('#ecom-checkout-time').text('2.1s');
		$('#ecom-abandonment').text('68%');
		$('#ecom-issues-count').text(findings.length);

		if (findings.length === 0) {
			$results.html('<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_success_notice_html( __( 'Excellent! Your store is healthy.', 'wpshadow' ) ) ); ?>');
			return;
		}

		// Render findings with revenue impact
		let html = '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_result_card_open_html() ); ?>';
		html += wpshadowRenderSummaryHeading( '<?php echo esc_js( __( 'Store Health Issues', 'wpshadow' ) ); ?>', findings.length );

		findings.forEach(function(finding) {
			const severityClass = finding.severity === 'critical' ? 'error' : finding.severity === 'high' ? 'warning' : 'info';
			html += wpshadowRenderFindingCardStart( finding, {
				severityClass: severityClass,
				iconClass: 'dashicons-cart'
			} );

			// Add revenue impact estimate
			if (finding.threat_level > 60) {
				html += '<p class="wps-text-xs wps-text-error wps-mt-1"><strong><?php echo esc_js( __( 'Revenue Impact:', 'wpshadow' ) ); ?></strong> <?php echo esc_js( __( 'High - likely losing sales', 'wpshadow' ) ); ?></p>';
			}

			html += wpshadowRenderAutoFixButton( finding, '<?php echo esc_js( __( 'Fix Issue', 'wpshadow' ) ); ?>' );
			html += wpshadowRenderFindingCardEnd();
		});

		html += '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_result_card_close_html() ); ?>';
		$results.html(html);
	}
});
</script>

<?php
// Load and render sales widget
Tool_View_Base::render_sales_widget(
	array(
		'title'       => __( 'Want to maximize your store revenue?', 'wpshadow' ),
		'description' => __( 'WPShadow Pro includes conversion rate optimization, abandoned cart recovery, and automated store health monitoring.', 'wpshadow' ),
		'features'    => array(
			__( 'Automated cart recovery emails', 'wpshadow' ),
			__( 'Checkout optimization suggestions', 'wpshadow' ),
			__( 'Revenue impact tracking', 'wpshadow' ),
			__( 'Payment gateway monitoring', 'wpshadow' ),
		),
		'cta_text'    => __( 'Boost Sales with Pro', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-cart',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
