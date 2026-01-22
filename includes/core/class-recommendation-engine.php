<?php
/**
 * Recommendation Engine
 *
 * Analyzes findings and recommends the highest-impact fixes
 * considering both urgency and effort (Eisenhower Matrix).
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

/**
 * Smart recommendation system for prioritizing fixes
 */
class Recommendation_Engine {
	
	/**
	 * Get top N recommended fixes
	 *
	 * @param int $limit How many recommendations to return.
	 * @return array Array of recommended findings with scoring.
	 */
	public static function get_recommendations( $limit = 3 ) {
		// Get current findings
		$findings = wpshadow_get_site_findings();
		$dismissed = get_option( 'wpshadow_dismissed_findings', array() );
		
		// Filter out dismissed findings
		$active_findings = array_filter(
			$findings,
			function( $f ) use ( $dismissed ) {
				return ! isset( $f['id'] ) || ! isset( $dismissed[ $f['id'] ] );
			}
		);
		
		// Score each finding
		$scored_findings = array();
		foreach ( $active_findings as $finding ) {
			$score = self::calculate_recommendation_score( $finding );
			$scored_findings[] = array_merge( $finding, array( 'recommendation_score' => $score ) );
		}
		
		// Sort by score (highest first)
		usort(
			$scored_findings,
			function( $a, $b ) {
				return $b['recommendation_score'] <=> $a['recommendation_score'];
			}
		);
		
		// Return top N
		return array_slice( $scored_findings, 0, $limit );
	}
	
	/**
	 * Calculate recommendation score using Eisenhower Matrix
	 *
	 * Factors considered:
	 * - Urgency: threat_level (higher = more urgent)
	 * - Importance: risk reduction from KPI_Metadata
	 * - Effort: time_to_fix_minutes (lower = easier, better score)
	 * - Impact: ROI multiplier
	 *
	 * Formula: ((Urgency + Importance) * Impact) / Effort
	 *
	 * @param array $finding Finding data.
	 * @return float Recommendation score.
	 */
	private static function calculate_recommendation_score( $finding ) {
		// Get base score from threat level
		$urgency = isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
		
		// Get metadata for this finding's category
		$diagnostic_id = isset( $finding['id'] ) ? $finding['id'] : 'unknown';
		$metadata = KPI_Metadata::get( $diagnostic_id );
		
		// Extract KPI factors
		$importance = $metadata['risk_reduction'] ?? 10;
		$effort_minutes = $metadata['time_to_fix_minutes'] ?? 15;
		$impact = $metadata['roi_multiplier'] ?? 1.0;
		
		// Calculate base priority (Eisenhower)
		$priority = ( ( $urgency + $importance ) * $impact ) / max( 1, $effort_minutes / 10 );
		
		// Boost priority if it's auto-fixable (quick wins)
		if ( isset( $finding['auto_fixable'] ) && $finding['auto_fixable'] ) {
			$priority *= 1.5;
		}
		
		// Reduce priority if already in user's ignore list
		if ( isset( $finding['ignored'] ) && $finding['ignored'] ) {
			$priority *= 0.5;
		}
		
		return (float) $priority;
	}
	
	/**
	 * Get recommendations grouped by impact type
	 *
	 * Returns findings organized as:
	 * - "Quick Wins" (auto-fixable, low effort)
	 * - "Security" (high threat, critical)
	 * - "Performance" (speed improvements)
	 *
	 * @return array Recommendations grouped by type.
	 */
	public static function get_recommendations_by_impact() {
		$recommendations = self::get_recommendations( 10 );
		
		$grouped = array(
			'quick_wins'  => array(),
			'security'    => array(),
			'performance' => array(),
			'other'       => array(),
		);
		
		foreach ( $recommendations as $rec ) {
			// Quick wins: auto-fixable + low effort
			if ( isset( $rec['auto_fixable'] ) && $rec['auto_fixable'] && isset( $rec['threat_level'] ) && $rec['threat_level'] < 70 ) {
				$grouped['quick_wins'][] = $rec;
			}
			// Security: high threat
			elseif ( isset( $rec['threat_level'] ) && $rec['threat_level'] >= 80 ) {
				$grouped['security'][] = $rec;
			}
			// Performance: performance category
			elseif ( isset( $rec['category'] ) && $rec['category'] === 'performance' ) {
				$grouped['performance'][] = $rec;
			}
			// Everything else
			else {
				$grouped['other'][] = $rec;
			}
		}
		
		// Filter to top 3 by type
		return array(
			'quick_wins'  => array_slice( $grouped['quick_wins'], 0, 1 ),
			'security'    => array_slice( $grouped['security'], 0, 1 ),
			'performance' => array_slice( $grouped['performance'], 0, 1 ),
		);
	}
	
	/**
	 * Render recommendation widget
	 *
	 * @return void Outputs HTML directly.
	 */
	public static function render_recommendation_widget() {
		$recommendations = self::get_recommendations( 3 );
		
		if ( empty( $recommendations ) ) {
			?>
			<div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 20px; border-radius: 6px; text-align: center;">
				<h3 style="margin-top: 0; color: #059669;">
					<?php esc_html_e( '🎉 Perfect Score!', 'wpshadow' ); ?>
				</h3>
				<p style="margin: 8px 0; color: #065f46;">
					<?php esc_html_e( 'No critical issues found. Your site is in great shape!', 'wpshadow' ); ?>
				</p>
			</div>
			<?php
			return;
		}
		?>
		<div class="wpshadow-recommendations" style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; border-radius: 6px; margin: 20px 0;">
			<h3 style="margin-top: 0; color: #92400e; display: flex; align-items: center; gap: 8px;">
				<span style="font-size: 20px;">🎯</span>
				<?php esc_html_e( 'Recommended Actions', 'wpshadow' ); ?>
			</h3>
			<p style="margin: 8px 0 16px 0; color: #78350f;">
				<?php esc_html_e( 'Fix these issues first for maximum impact:', 'wpshadow' ); ?>
			</p>
			
			<div style="display: grid; gap: 12px;">
				<?php foreach ( $recommendations as $idx => $rec ) : ?>
					<div style="background: white; padding: 12px; border-radius: 4px; display: flex; align-items: flex-start; gap: 12px;">
						<!-- Ranking badge -->
						<div style="flex-shrink: 0; width: 32px; height: 32px; background: #f59e0b; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">
							<?php echo ( $idx + 1 ); ?>
						</div>
						
						<!-- Finding details -->
						<div style="flex: 1;">
							<h4 style="margin: 0 0 4px 0; color: #1f2937;">
								<?php echo esc_html( $rec['title'] ?? 'Unknown Issue' ); ?>
							</h4>
							<p style="margin: 4px 0; color: #6b7280; font-size: 13px;">
								<?php echo esc_html( $rec['description'] ?? '' ); ?>
							</p>
							
							<!-- Phase 5: KB & Training Links -->
							<div style="font-size: 12px; margin: 8px 0; display: flex; gap: 12px;">
								<?php 
								$kb_slug = isset( $rec['id'] ) ? sanitize_title( $rec['id'] ) : 'general-fix';
								?>
								<a href="<?php echo esc_url( 'https://wpshadow.com/kb/' . $kb_slug ); ?>" target="_blank" style="color: #2196f3; text-decoration: none;">
									📚 <?php esc_html_e( 'Learn more', 'wpshadow' ); ?>
								</a>
								<a href="<?php echo esc_url( 'https://wpshadow.com/training/' . $kb_slug ); ?>" target="_blank" style="color: #9333ea; text-decoration: none;">
									🎥 <?php esc_html_e( 'Watch video', 'wpshadow' ); ?>
								</a>
							</div>
							
							<!-- Score breakdown -->
							<div style="display: flex; gap: 12px; margin-top: 8px; font-size: 12px;">
								<?php if ( isset( $rec['threat_level'] ) ) : ?>
									<span style="color: #6b7280;">
										🔴 Threat: <strong><?php echo (int) $rec['threat_level']; ?>%</strong>
									</span>
								<?php endif; ?>
								<?php if ( isset( $rec['auto_fixable'] ) && $rec['auto_fixable'] ) : ?>
									<span style="color: #10b981;">
										⚡ Auto-fixable
									</span>
								<?php endif; ?>
								<?php if ( isset( $rec['recommendation_score'] ) ) : ?>
									<span style="color: #f59e0b;">
										✨ Priority: <strong><?php echo round( $rec['recommendation_score'], 1 ); ?></strong>
									</span>
								<?php endif; ?>
							</div>
						</div>
						
						<!-- Action button -->
						<div style="flex-shrink: 0;">
							<?php if ( isset( $rec['auto_fixable'] ) && $rec['auto_fixable'] ) : ?>
								<button class="wpshadow-quick-fix" data-finding-id="<?php echo esc_attr( $rec['id'] ?? '' ); ?>" style="background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">
									<?php esc_html_e( 'Fix Now', 'wpshadow' ); ?>
								</button>
							<?php else : ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&category=' . ( $rec['category'] ?? 'general' ) ) ); ?>" class="button button-small">
									<?php esc_html_e( 'Learn More', 'wpshadow' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Get impact summary for recommendations
	 *
	 * Returns estimated total value if all recommendations are applied
	 *
	 * @return array Impact summary.
	 */
	public static function get_impact_summary() {
		$recommendations = self::get_recommendations( 5 );
		$total_minutes = 0;
		$total_risk_reduction = 0;
		$auto_fixable_count = 0;
		
		foreach ( $recommendations as $rec ) {
			$metadata = KPI_Metadata::get( $rec['id'] ?? 'unknown' );
			$total_minutes += $metadata['time_to_fix_minutes'] ?? 15;
			$total_risk_reduction += $metadata['risk_reduction'] ?? 10;
			
			if ( isset( $rec['auto_fixable'] ) && $rec['auto_fixable'] ) {
				$auto_fixable_count++;
			}
		}
		
		$total_hours = intdiv( $total_minutes, 60 );
		$labor_cost = $total_hours * 50; // Standard $50/hr
		
		return array(
			'total_hours'         => $total_hours,
			'labor_cost_avoided'  => $labor_cost,
			'risk_reduction_pct'  => min( 100, $total_risk_reduction ),
			'auto_fixable_count'  => $auto_fixable_count,
			'total_recommendations' => count( $recommendations ),
		);
	}
}
