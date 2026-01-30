<?php
/**
 * SEO Report
 *
 * Comprehensive SEO analysis covering search visibility, meta tags,
 * structured data, mobile-first indexing, and Core Web Vitals.
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since      1.26030.1200
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
Tool_View_Base::enqueue_assets( 'seo-report' );

// Render header
Tool_View_Base::render_header( __( 'SEO Report', 'wpshadow' ) );

// Get all SEO diagnostics
$all_diagnostics = Diagnostic_Registry::get_all();
$seo_diagnostics = array();

foreach ( $all_diagnostics as $slug => $class ) {
	if ( ! class_exists( $class ) ) {
		continue;
	}

	// Check if diagnostic belongs to SEO family
	$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
	if ( 'seo' !== $family ) {
		continue;
	}

	$seo_diagnostics[ $slug ] = $class;
}

?>

<div class="wpshadow-tool seo-report-tool">
	
	<div class="wps-card wps-mb-4">
		<div class="wps-card-body">
			<h2 class="wps-text-xl wps-mb-3">
				<span class="dashicons dashicons-search wps-text-primary"></span>
				<?php esc_html_e( 'SEO Overview', 'wpshadow' ); ?>
			</h2>
			<p class="wps-text-muted wps-mb-3">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of SEO diagnostics */
						_n(
							'Running %d SEO diagnostic to analyze search engine visibility, mobile-first indexing, and ranking opportunities.',
							'Running %d SEO diagnostics to analyze search engine visibility, mobile-first indexing, and ranking opportunities.',
							count( $seo_diagnostics ),
							'wpshadow'
						),
						count( $seo_diagnostics )
					)
				);
				?>
			</p>

			<div class="wps-grid wps-grid-cols-4 wps-gap-3 wps-mb-4">
				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-visibility wps-text-2xl wps-text-success"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Search Visibility', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="seo-visibility-status">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-smartphone wps-text-2xl wps-text-primary"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Mobile Ready', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="seo-mobile-status">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-tag wps-text-2xl wps-text-warning"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Meta Issues', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="seo-meta-issues">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-chart-line wps-text-2xl wps-text-success"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'SEO Score', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="seo-score">-</div>
						</div>
					</div>
				</div>
			</div>

			<button type="button" 
				class="wps-btn wps-btn-primary wps-btn-icon-left wpshadow-run-seo-scan" 
				id="run-seo-scan-btn"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_seo_scan' ) ); ?>"
				aria-label="<?php esc_attr_e( 'Run comprehensive SEO analysis now', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Run SEO Analysis', 'wpshadow' ); ?>
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
	<div class="scan-results" id="seo-scan-results"></div>

	<!-- SEO Checklist -->
	<div class="wps-card wps-mt-4">
		<div class="wps-card-body">
			<h3 class="wps-text-lg wps-mb-3">
				<?php esc_html_e( 'What This Analysis Covers', 'wpshadow' ); ?>
			</h3>
			<div class="wps-grid wps-grid-cols-2 wps-gap-4">
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-visibility"></span>
						<?php esc_html_e( 'Search Visibility', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Search engine indexing status', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Robots.txt configuration', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'XML sitemap presence and validity', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Noindex/nofollow tag usage', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Canonical URL implementation', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-tag"></span>
						<?php esc_html_e( 'Meta Tags & Markup', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Title tag optimization', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Meta description quality', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Open Graph tags (social sharing)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Schema.org structured data', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Heading hierarchy (H1-H6)', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-smartphone"></span>
						<?php esc_html_e( 'Mobile SEO', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Mobile-first indexing compatibility', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Responsive design validation', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Mobile page speed', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Touch target size', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Viewport configuration', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-chart-line"></span>
						<?php esc_html_e( 'Core Web Vitals', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Largest Contentful Paint (LCP)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'First Input Delay (FID)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Cumulative Layout Shift (CLS)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Performance impact on rankings', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Page experience signals', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- SEO Tips -->
			<div class="wps-mt-4 wps-p-4 wps-bg-success-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-lightbulb"></span>
					<?php esc_html_e( 'SEO Quick Wins', 'wpshadow' ); ?>
				</h4>
				<p class="wps-text-sm wps-text-muted">
					<?php esc_html_e( 'These SEO improvements can significantly boost your search engine rankings:', 'wpshadow' ); ?>
				</p>
				<div class="wps-grid wps-grid-cols-2 wps-gap-3 wps-mt-2">
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Add unique meta descriptions to all pages', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Optimize images with descriptive alt text', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Fix broken internal/external links', 'wpshadow' ); ?></li>
					</ul>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Implement schema markup for rich snippets', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Improve Core Web Vitals scores', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Ensure mobile-first compatibility', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#run-seo-scan-btn').on('click', function() {
		const $btn = $(this);
		const $progress = $('.scan-progress');
		const $results = $('#seo-scan-results');
		
		$btn.prop('disabled', true).addClass('wps-loading');
		$progress.removeClass('hidden');
		$results.empty();
		
		// Run SEO diagnostics
		wp.ajax.post('wpshadow_run_family_diagnostics', {
			family: 'seo',
			nonce: $btn.data('nonce')
		}).done(function(response) {
			displaySEOResults(response);
		}).fail(function(error) {
			$results.html('<div class="notice notice-error"><p>' + error.message + '</p></div>');
		}).always(function() {
			$btn.prop('disabled', false).removeClass('wps-loading');
			$progress.addClass('hidden');
		});
	});

	function displaySEOResults(data) {
		const $results = $('#seo-scan-results');
		const findings = data.findings || [];
		
		// Calculate SEO score (100 - findings * 5)
		const totalChecks = <?php echo count( $seo_diagnostics ); ?>;
		const seoScore = Math.max(0, 100 - (findings.length * 5));
		
		// Update summary
		const visibilityIssues = findings.filter(f => f.title && f.title.toLowerCase().includes('visibility')).length;
		const mobileIssues = findings.filter(f => f.title && f.title.toLowerCase().includes('mobile')).length;
		const metaIssues = findings.filter(f => f.title && (f.title.toLowerCase().includes('meta') || f.title.toLowerCase().includes('title'))).length;
		
		$('#seo-visibility-status').text(visibilityIssues === 0 ? '<?php echo esc_js( __( 'Good', 'wpshadow' ) ); ?>' : visibilityIssues + ' <?php echo esc_js( __( 'issues', 'wpshadow' ) ); ?>');
		$('#seo-mobile-status').text(mobileIssues === 0 ? '<?php echo esc_js( __( 'Yes', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Issues found', 'wpshadow' ) ); ?>');
		$('#seo-meta-issues').text(metaIssues);
		$('#seo-score').text(seoScore + '/100');
		
		// Display findings
		if (findings.length === 0) {
			$results.html('<div class="notice notice-success wps-card"><p><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_js( __( 'Excellent! Your SEO is optimized.', 'wpshadow' ) ); ?></p></div>');
			return;
		}
		
		// Group findings by priority
		const critical = findings.filter(f => f.severity === 'critical' || f.severity === 'high');
		const moderate = findings.filter(f => f.severity === 'medium');
		const minor = findings.filter(f => f.severity === 'low');
		
		let html = '<div class="wps-card"><div class="wps-card-body">';
		html += '<h3 class="wps-text-lg wps-mb-3"><?php echo esc_js( __( 'SEO Issues Found', 'wpshadow' ) ); ?> (' + findings.length + ')</h3>';
		
		// Display critical issues first
		if (critical.length > 0) {
			html += '<h4 class="wps-font-semibold wps-text-error wps-mb-2"><?php echo esc_js( __( 'Critical SEO Issues', 'wpshadow' ) ); ?> (' + critical.length + ')</h4>';
			critical.forEach(function(finding) {
				html += renderSEOFinding(finding, 'error');
			});
		}
		
		// Moderate issues
		if (moderate.length > 0) {
			html += '<h4 class="wps-font-semibold wps-text-warning wps-mt-4 wps-mb-2"><?php echo esc_js( __( 'Moderate SEO Issues', 'wpshadow' ) ); ?> (' + moderate.length + ')</h4>';
			moderate.forEach(function(finding) {
				html += renderSEOFinding(finding, 'warning');
			});
		}
		
		// Minor issues
		if (minor.length > 0) {
			html += '<h4 class="wps-font-semibold wps-text-info wps-mt-4 wps-mb-2"><?php echo esc_js( __( 'Minor SEO Issues', 'wpshadow' ) ); ?> (' + minor.length + ')</h4>';
			minor.forEach(function(finding) {
				html += renderSEOFinding(finding, 'info');
			});
		}
		
		html += '</div></div>';
		$results.html(html);
	}

	function renderSEOFinding(finding, severity) {
		let html = '<div class="wps-mb-3 wps-p-3 wps-border wps-border-' + severity + ' wps-rounded">';
		html += '<div class="wps-flex wps-items-start wps-gap-3">';
		html += '<span class="dashicons dashicons-info wps-text-' + severity + '"></span>';
		html += '<div class="wps-flex-1">';
		html += '<h5 class="wps-font-semibold">' + finding.title + '</h5>';
		html += '<p class="wps-text-muted wps-text-sm">' + finding.description + '</p>';
		
		// Impact statement
		if (finding.threat_level) {
			const impact = finding.threat_level > 70 ? '<?php echo esc_js( __( 'High impact on rankings', 'wpshadow' ) ); ?>' :
			               finding.threat_level > 40 ? '<?php echo esc_js( __( 'Moderate impact', 'wpshadow' ) ); ?>' :
			               '<?php echo esc_js( __( 'Low impact', 'wpshadow' ) ); ?>';
			html += '<p class="wps-text-xs wps-text-muted wps-mt-1"><strong><?php echo esc_js( __( 'Impact:', 'wpshadow' ) ); ?></strong> ' + impact + '</p>';
		}
		
		if (finding.auto_fixable) {
			html += '<button class="wps-btn wps-btn-sm wps-btn-success wps-mt-2" data-finding="' + finding.id + '"><?php echo esc_js( __( 'Auto-Fix', 'wpshadow' ) ); ?></button>';
		}
		if (finding.kb_link) {
			html += '<a href="' + finding.kb_link + '" target="_blank" class="wps-btn wps-btn-sm wps-btn-link wps-mt-2"><?php echo esc_js( __( 'Learn More', 'wpshadow' ) ); ?></a>';
		}
		html += '</div></div></div>';
		return html;
	}
});
</script>

<?php
// Load and render sales widget
require_once WPSHADOW_PATH . 'includes/views/components/sales-widget.php';

wpshadow_render_sales_widget(
	array(
		'title'       => __( 'Want advanced SEO features?', 'wpshadow' ),
		'description' => __( 'WPShadow Pro includes automated SEO optimization, keyword tracking, and competitor analysis to boost your rankings.', 'wpshadow' ),
		'features'    => array(
			__( 'Automated schema markup', 'wpshadow' ),
			__( 'Keyword rank tracking', 'wpshadow' ),
			__( 'Competitor analysis', 'wpshadow' ),
			__( 'Content optimization suggestions', 'wpshadow' ),
		),
		'cta_text'    => __( 'Improve SEO with Pro', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-search',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
