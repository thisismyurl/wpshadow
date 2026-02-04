<?php
/**
 * PDF Report Generator
 *
 * Generates PDF reports for WPShadow findings and diagnostics
 *
 * @since   1.6032.1021
 * @package WPShadow\Reporting
 */

declare(strict_types=1);

namespace WPShadow\Reporting;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDF_Report_Generator Class
 *
 * Creates PDF reports without external dependencies using native PHP
 *
 * @since 1.6032.1021
 */
class PDF_Report_Generator {

	/**
	 * Generate a PDF report from findings
	 *
	 * @since  1.6032.1021
	 * @param  string $report_type Report type (summary, detailed, audit_trail).
	 * @param  array  $findings Array of findings.
	 * @return string|false PDF file path or false on failure.
	 */
	public static function generate_pdf( string $report_type, array $findings ) {
		try {
			// Prepare report data
			$report_data = self::prepare_report_data( $report_type, $findings );

			// Generate HTML content
			$html_content = self::render_html_report( $report_data );

			// Save as file
			$file_path = self::save_report_file( $html_content, $report_type );

			if ( $file_path ) {
				// Log report generation
				\WPShadow\Core\Activity_Logger::log(
					'report_generated',
					array(
						'type'     => $report_type,
						'format'   => 'pdf',
						'findings' => count( $findings ),
						'file'     => $file_path,
					)
				);

				return $file_path;
			}
		} catch ( \Exception $e ) {
			\WPShadow\Core\Error_Handler::log_error( 'PDF generation failed: ' . $e->getMessage(), $e );
		}

		return false;
	}

	/**
	 * Prepare report data based on type
	 *
	 * @since  1.6032.1021
	 * @param  string $report_type Report type.
	 * @param  array  $findings Array of findings.
	 * @return array Prepared report data.
	 */
	private static function prepare_report_data( string $report_type, array $findings ): array {
		$data = array(
			'type'               => $report_type,
			'generated_at'       => current_time( 'mysql' ),
			'site_url'           => get_site_url(),
			'site_name'          => get_bloginfo( 'name' ),
			'wordpress_version'  => get_bloginfo( 'version' ),
			'findings'           => $findings,
			'total_findings'     => count( $findings ),
			'urgent_count'       => 0,
			'important_count'    => 0,
			'maintenance_count'  => 0,
			'polish_count'       => 0,
		);

		// Count by severity (map old names to new novice-friendly names)
		foreach ( $findings as $finding ) {
			$severity = $finding['severity'] ?? 'low';
			switch ( $severity ) {
				case 'critical':
					$data['urgent_count']++;
					break;
				case 'high':
					$data['important_count']++;
					break;
				case 'medium':
					$data['maintenance_count']++;
					break;
				default:
					$data['polish_count']++;
			}
		}

		return $data;
	}

	/**
	 * Render HTML report from data
	 *
	 * @since  1.6032.1021
	 * @param  array $data Report data.
	 * @return string HTML content.
	 */
	private static function render_html_report( array $data ): string {
		$site_name = esc_html( $data['site_name'] );
		$site_url = esc_url( $data['site_url'] );
		$generated_at = esc_html( $data['generated_at'] );
		$total = $data['total_findings'];
		$urgent = $data['urgent_count'];
		$important = $data['important_count'];
		$maintenance = $data['maintenance_count'];
		$polish = $data['polish_count'];

		$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>WPShadow Report - {$site_name}</title>
	<style>
		* { margin: 0; padding: 0; box-sizing: border-box; }
		body {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			line-height: 1.6;
			color: #333;
			background: #fff;
		}
		.container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
		.header {
			border-bottom: 3px solid #0073aa;
			padding-bottom: 20px;
			margin-bottom: 30px;
		}
		.header h1 {
			font-size: 28px;
			color: #0073aa;
			margin-bottom: 5px;
		}
		.header p {
			color: #666;
			font-size: 14px;
		}
		.summary {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 15px;
			margin-bottom: 30px;
		}
		.summary-card {
			border: 1px solid #ddd;
			border-radius: 5px;
			padding: 15px;
			text-align: center;
		}
		.summary-card.critical {
			background: #fee5e5;
			border-color: #dc3232;
		}
		.summary-card.high {
			background: #fef5e7;
			border-color: #ffb800;
		}
		.summary-card.medium {
			background: #fef9e7;
			border-color: #ffdd47;
		}
		.summary-card.low {
			background: #f3f7f9;
			border-color: #0073aa;
		}
		.summary-card strong {
			display: block;
			font-size: 24px;
			margin: 10px 0 5px;
		}
		.summary-card span {
			font-size: 12px;
			color: #666;
		}
		.findings {
			margin-top: 30px;
		}
		.findings h2 {
			font-size: 20px;
			margin-bottom: 15px;
			border-bottom: 2px solid #0073aa;
			padding-bottom: 10px;
		}
		.finding {
			border: 1px solid #ddd;
			border-radius: 5px;
			margin-bottom: 15px;
			padding: 15px;
			page-break-inside: avoid;
		}
		.finding-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 10px;
		}
		.finding-title {
			font-weight: bold;
			font-size: 16px;
			color: #333;
		}
		.finding-severity {
			font-size: 12px;
			font-weight: bold;
			padding: 4px 10px;
			border-radius: 3px;
			text-transform: uppercase;
		}
		.finding-severity.critical {
			background: #dc3232;
			color: #fff;
		}
		.finding-severity.high {
			background: #ffb800;
			color: #fff;
		}
		.finding-severity.medium {
			background: #ffdd47;
			color: #333;
		}
		.finding-severity.low {
			background: #0073aa;
			color: #fff;
		}
		.finding-description {
			color: #666;
			font-size: 14px;
			margin-bottom: 8px;
		}
		.finding-details {
			background: #f9f9f9;
			padding: 10px;
			border-left: 3px solid #0073aa;
			font-size: 13px;
			margin-top: 10px;
		}
		.footer {
			margin-top: 40px;
			padding-top: 20px;
			border-top: 1px solid #ddd;
			font-size: 12px;
			color: #999;
			text-align: center;
		}
		@media print {
			body { background: #fff; }
			.finding { page-break-inside: avoid; }
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1>🔍 WPShadow Site Health Report</h1>
			<p><strong>Site:</strong> {$site_name} ({$site_url})</p>
			<p><strong>Generated:</strong> {$generated_at}</p>
		</div>

		<div class="summary">
			<div class="summary-card critical">
				<span>🚨 Handle These First</span>
				<strong>{$urgent}</strong>
				<small style="display: block; margin-top: 5px; font-size: 11px;">Like smoke alarms</small>
			</div>
			<div class="summary-card high">
				<span>⚠️ Important Improvements</span>
				<strong>{$important}</strong>
				<small style="display: block; margin-top: 5px; font-size: 11px;">Like a check engine light</small>
			</div>
			<div class="summary-card medium">
				<span>ℹ️ Good Maintenance</span>
				<strong>{$maintenance}</strong>
				<small style="display: block; margin-top: 5px; font-size: 11px;">Like changing air filters</small>
			</div>
			<div class="summary-card low">
				<span>✨ Nice to Have</span>
				<strong>{$polish}</strong>
				<small style="display: block; margin-top: 5px; font-size: 11px;">Like waxing your car</small>
			</div>
		</div>

		<div class="findings">
			<h2>What We Found ({$total} Total)</h2>
			<p style="color: #666; margin-bottom: 20px;">
				Here's what we discovered about your site. Don't worry—many of these items are easy fixes, 
				and we can help you address them automatically. Focus on the urgent items first, then work 
				through the rest when you have time.
			</p>
HTML;

		// Add findings with novice-friendly severity labels
		foreach ( $data['findings'] as $finding ) {
			$title = esc_html( $finding['title'] ?? 'Unknown' );
			$description = esc_html( $finding['description'] ?? '' );
			$severity_raw = $finding['severity'] ?? 'low';
			
			// Map technical severity to novice-friendly labels
			$severity_map = array(
				'critical' => array( 'label' => 'Handle First', 'class' => 'critical', 'icon' => '🚨' ),
				'high'     => array( 'label' => 'Important', 'class' => 'high', 'icon' => '⚠️' ),
				'medium'   => array( 'label' => 'Maintenance', 'class' => 'medium', 'icon' => 'ℹ️' ),
				'low'      => array( 'label' => 'Polish', 'class' => 'low', 'icon' => '✨' ),
			);
			
			$severity_info = $severity_map[ $severity_raw ] ?? $severity_map['low'];
			$severity_label = $severity_info['icon'] . ' ' . $severity_info['label'];
			$severity_class = $severity_info['class'];

			$html .= <<<HTML
			<div class="finding">
				<div class="finding-header">
					<div class="finding-title">{$title}</div>
					<div class="finding-severity {$severity_class}">{$severity_label}</div>
				</div>
				<div class="finding-description">{$description}</div>
HTML;

			if ( ! empty( $finding['details'] ) ) {
				$details = is_array( $finding['details'] ) ? 
					json_encode( $finding['details'], JSON_PRETTY_PRINT ) : 
					esc_html( (string) $finding['details'] );
				$html .= <<<HTML
				<div class="finding-details">
					<strong>Details:</strong><br>
					<pre style="margin-top: 5px; font-size: 11px;">{$details}</pre>
				</div>
HTML;
			}

			$html .= <<<HTML
			</div>
HTML;
		}

		$html .= <<<HTML
		</div>

		<div class="footer">
			<p>
				<strong>Need Help?</strong> This report shows what we found. Many of these items can be fixed 
				automatically by WPShadow. Visit your WordPress dashboard to apply fixes with one click, or 
				check our knowledge base at <a href="https://wpshadow.com/kb">wpshadow.com/kb</a> to learn more.
			</p>
			<p style="margin-top: 10px;">Report generated by WPShadow Guardian</p>
		</div>
	</div>
</body>
</html>
HTML;

		return $html;
	}

	/**
	 * Save report file
	 *
	 * @since  1.6032.1021
	 * @param  string $html_content HTML content.
	 * @param  string $report_type Report type.
	 * @return string|false File path or false.
	 */
	private static function save_report_file( string $html_content, string $report_type ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$upload_dir = wp_upload_dir();
		$report_dir = $upload_dir['basedir'] . '/wpshadow-reports/';

		// Create directory if needed
		if ( ! $wp_filesystem->is_dir( $report_dir ) ) {
			if ( ! wp_mkdir_p( $report_dir ) ) {
				return false;
			}
		}

		// Generate filename
		$filename = sanitize_file_name( 
			'wpshadow-' . $report_type . '-' . gmdate( 'Y-m-d-His' ) . '.html'
		);
		$file_path = $report_dir . $filename;

		// Save file
		if ( ! $wp_filesystem->put_contents( $file_path, $html_content, FS_CHMOD_FILE ) ) {
			return false;
		}

		return $file_path;
	}

	/**
	 * Get downloadable report URL
	 *
	 * @since  1.6032.1021
	 * @param  string $file_path File path.
	 * @return string|false URL or false.
	 */
	public static function get_report_url( string $file_path ) {
		$upload_dir = wp_upload_dir();
		$base_path = $upload_dir['basedir'];
		$base_url = $upload_dir['baseurl'];

		// Validate path is within upload directory
		if ( 0 !== strpos( $file_path, $base_path ) ) {
			return false;
		}

		// Convert path to URL
		$relative_path = substr( $file_path, strlen( $base_path ) );
		return $base_url . $relative_path;
	}
}
