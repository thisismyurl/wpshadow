<?php
declare(strict_types=1);

namespace WPShadow\Reports;

/**
 * Report Renderer - Renders reports in various formats
 *
 * Handles:
 * - HTML rendering with charts and visualizations
 * - CSV export with proper formatting
 * - JSON export for API usage
 * - Printable PDF layouts
 *
 * Philosophy:
 * - #9 Show Value: Visually compelling reports
 * - #8 Inspire Confidence: Professional presentation
 * - #5 Drive to KB: Educational links throughout
 *
 * @package WPShadow\Reports
 */
class Report_Renderer {

	/**
	 * Render report as HTML
	 *
	 * @param array $report Report data
	 * @return string HTML output
	 */
	public static function render_html( array $report ): string {
		ob_start();
		?>
		<div class="wpshadow-report-html" class="wps-p-20"Segoe UI', Roboto, sans-serif;">
			
			<!-- Report Header -->
			<div class="wps-report-header">
				<h1 class="wps-m-0">
					<?php echo esc_html( $report['title'] ); ?>
				</h1>
				<p class="wps-m-0">
					<?php
					printf(
						/* translators: %1$s is start date, %2$s is end date, %3$s is generated time */
						esc_html__( 'Period: %1$s to %2$s | Generated: %3$s', 'wpshadow' ),
						esc_html( $report['date_range']['from'] ),
						esc_html( $report['date_range']['to'] ),
						esc_html( $report['generated_at'] )
					);
					?>
				</p>
			</div>
			
			<!-- Key Metrics -->
			<div class="wps-grid wps-grid-auto-200" class="wps-report-metric-grid">
				<?php
				$metrics = $report['metrics'];
				$cards   = array(
					array(
						'icon'        => '⏱️',
						'label'       => __( 'Time Saved', 'wpshadow' ),
						'value'       => sprintf( '%.1f hrs', $metrics['time_saved_hours'] ),
						'explanation' => __( 'Hours you didn\'t have to spend fixing things manually', 'wpshadow' ),
						'color'       => '#1e73be',
					),
					array(
						'icon'        => '✅',
						'label'       => __( 'Problems Fixed', 'wpshadow' ),
						'value'       => $metrics['issues_fixed'],
						'explanation' => __( 'Site issues we automatically repaired for you', 'wpshadow' ),
						'color'       => '#27ae60',
					),
					array(
						'icon'        => '⚙️',
						'label'       => __( 'Automations Created', 'wpshadow' ),
						'value'       => $metrics['workflows_created'],
						'explanation' => __( 'Automatic fixes that run without you having to do anything', 'wpshadow' ),
						'color'       => '#8e44ad',
					),
					array(
						'icon'        => '📊',
						'label'       => __( 'Success Rate', 'wpshadow' ),
						'value'       => sprintf( '%.1f%%', $metrics['success_rate'] ),
						'explanation' => __( 'How often our fixes worked perfectly the first time', 'wpshadow' ),
						'color'       => '#e67e22',
					),
				);
				?>
				
				<?php foreach ( $cards as $card ) : ?>
					<div style="background: linear-gradient(135deg, <?php echo esc_attr( $card['color'] ); ?> 0%, <?php echo esc_attr( $card['color'] ); ?>dd 100%); color: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
						<div class="wps-report-metric-icon" style="font-size: 24px; margin-bottom: 8px;"><?php echo esc_html( $card['icon'] ); ?></div>
						<div class="wps-report-metric-label" style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;"><?php echo esc_html( $card['label'] ); ?></div>
						<div class="wps-report-metric-value" style="font-size: 28px; font-weight: bold; margin-bottom: 8px;"><?php echo esc_html( $card['value'] ); ?></div>
						<?php if ( isset( $card['explanation'] ) ) : ?>
							<div class="wps-report-metric-explanation" style="font-size: 12px; opacity: 0.85; line-height: 1.4;"><?php echo esc_html( $card['explanation'] ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			
			<!-- Activity by Category -->
			<div class="wps-p-15-rounded-8">
				<h3 class="wps-report-section-title"><?php esc_html_e( 'Activities by Category', 'wpshadow' ); ?></h3>
				<div class="wps-grid wps-grid-auto-200-compact">
					<?php
					foreach ( (array) $metrics['by_category'] as $category => $count ) {
						$percent = ( $metrics['total_activities'] > 0 ) ?
							round( ( $count / $metrics['total_activities'] ) * 100 ) : 0;
						?>
						<div>
							<div class="wps-flex-justify-space-between">
								<strong><?php echo esc_html( ucfirst( $category ) ); ?></strong>
								<span class="wps-report-category-percent"><?php echo absint( $percent ); ?>%</span>
							</div>
							<div class="wps-rounded-4">
								<div style="background: #0073aa; height: 100%; width: <?php echo absint( $percent ); ?>%;"></div>
							</div>
							<div class="wps-report-category-count"><?php echo absint( $count ); ?> activities</div>
						</div>
					<?php } ?>
				</div>
			</div>
			
			<!-- Trends Chart Data -->
			<?php if ( ! empty( $report['trends'] ) ) : ?>
			<div class="wps-p-15-rounded-8">
				<h3 class="wps-report-section-title"><?php esc_html_e( 'Activity Trends', 'wpshadow' ); ?></h3>
				<table class="wps-report-table">
					<thead>
						<tr class="wps-report-table-header">
							<th class="wps-p-10"><?php esc_html_e( 'Date', 'wpshadow' ); ?></th>
							<th class="wps-p-10"><?php esc_html_e( 'Total', 'wpshadow' ); ?></th>
							<th class="wps-p-10"><?php esc_html_e( 'Workflows', 'wpshadow' ); ?></th>
							<th class="wps-p-10"><?php esc_html_e( 'Fixes', 'wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( array_slice( $report['trends'], -7 ) as $trend ) : ?>
						<tr class="wps-report-table-row">
							<td class="wps-p-10"><?php echo esc_html( $trend['date'] ); ?></td>
							<td class="wps-p-10"><?php echo absint( $trend['total'] ); ?></td>
							<td class="wps-p-10"><?php echo absint( $trend['workflows'] ); ?></td>
							<td class="wps-p-10"><?php echo absint( $trend['fixes'] ); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>
			
			<!-- Recommendations -->
			<?php if ( ! empty( $report['recommendations'] ) ) : ?>
			<div class="wps-m-20-b">
				<h3 style="color: #0073aa;"><?php esc_html_e( 'Recommendations', 'wpshadow' ); ?></h3>
				<?php foreach ( $report['recommendations'] as $rec ) : ?>
					<?php
					$rec_bg     = '#d1ecf1';
					$rec_border = '#17a2b8';
					if ( isset( $rec['severity'] ) && $rec['severity'] === 'warning' ) {
						$rec_bg     = '#fff3cd';
						$rec_border = '#ffc107';
					} elseif ( isset( $rec['severity'] ) && $rec['severity'] === 'success' ) {
						$rec_bg     = '#d4edda';
						$rec_border = '#28a745';
					}
					?>
				<div class="wps-p-15-rounded-4">
					<h4 class="wps-m-0"><?php echo esc_html( $rec['title'] ); ?></h4>
					<p class="wps-m-0"><?php echo esc_html( $rec['description'] ); ?></p>
					<a href="<?php echo esc_url( $rec['kb_link'] ); ?>" target="_blank" class="wps-report-recommendation-link">
						<?php esc_html_e( 'Learn more →', 'wpshadow' ); ?>
					</a>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			
			<!-- Footer -->
			<div class="wps-report-footer">
				<p><?php esc_html_e( 'This report was generated by WPShadow Guardian. For more information, visit the KB or contact support.', 'wpshadow' ); ?></p>
			</div>
			
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render report as JSON
	 *
	 * @param array $report Report data
	 * @return string JSON output
	 */
	public static function render_json( array $report ): string {
		return wp_json_encode( $report );
	}

	/**
	 * Render report as CSV
	 *
	 * @param array $report Report data
	 * @return string CSV output
	 */
	public static function render_csv( array $report ): string {
		return Report_Engine::export_csv( $report );
	}

	/**
	 * Download report as file
	 *
	 * @param array  $report Report data
	 * @param string $format Export format (html, csv, json)
	 * @param string $filename Filename for download
	 * @return void
	 */
	public static function download_report( array $report, string $format = 'csv', string $filename = '' ): void {
		if ( empty( $filename ) ) {
			$filename = 'wpshadow-report-' . date( 'Y-m-d-H-i-s' ) . '.' . $format;
		}

		// Set headers
		header( 'Content-Type: ' . self::get_content_type( $format ) );
		header( 'Content-Disposition: attachment; filename="' . basename( $filename ) . '"' );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Output content
		switch ( $format ) {
			case 'json':
				echo self::render_json( $report );
				break;
			case 'csv':
				echo self::render_csv( $report );
				break;
			case 'html':
			default:
				echo self::render_html( $report );
				break;
		}

		exit;
	}

	/**
	 * Get content type for format
	 *
	 * @param string $format Export format
	 * @return string MIME type
	 */
	private static function get_content_type( string $format ): string {
		$types = array(
			'json' => 'application/json',
			'csv'  => 'text/csv; charset=utf-8',
			'html' => 'text/html; charset=utf-8',
		);

		return $types[ $format ] ?? 'text/plain';
	}
}
