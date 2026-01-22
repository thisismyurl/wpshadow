<?php
/**
 * Top Issues Widget - Shows 3 highest priority issues
 * Philosophy #8: Inspire Confidence - Prioritizes what matters
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPShadow_Top_Issues_Widget {

	/**
	 * Render top issues widget
	 */
	public static function render() {
		$top_issues = self::get_top_issues( 3 );

		?>
		<div style="margin: 30px 0;">
			<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
				<h2 style="margin: 0;"><?php esc_html_e( 'Top Issues to Address', 'wpshadow' ); ?></h2>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-action-items' ) ); ?>" style="font-size: 12px; color: #0073aa; text-decoration: none; font-weight: 600;">
					<?php esc_html_e( 'View All →', 'wpshadow' ); ?>
				</a>
			</div>
			
			<?php if ( ! empty( $top_issues ) ) : ?>
				<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px;">
					<?php
					foreach ( $top_issues as $index => $issue ) :
						$threat_level = isset( $issue['threat_level'] ) ? $issue['threat_level'] : 50;
						$threat_label = self::get_threat_label( $threat_level );
						$threat_color = self::get_threat_color( $threat_level );
						$category     = isset( $issue['category'] ) ? $issue['category'] : 'other';
						$finding_id   = isset( $issue['id'] ) ? $issue['id'] : '';
						?>
						<div style="border: 2px solid <?php echo esc_attr( $threat_color ); ?>; border-radius: 6px; padding: 16px; background: #ffffff; position: relative; overflow: hidden;">
							<!-- Rank Badge -->
							<div style="position: absolute; top: 0; left: 0; background: <?php echo esc_attr( $threat_color ); ?>; color: white; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
								<?php echo esc_html( $index + 1 ); ?>
							</div>
							
							<!-- Content -->
							<div style="padding-left: 44px;">
								<!-- Title -->
								<h3 style="margin: 0 0 8px 0; font-size: 14px; color: #333; font-weight: 600;">
									<?php echo esc_html( isset( $issue['title'] ) ? $issue['title'] : 'Unknown Issue' ); ?>
								</h3>
								
								<!-- Description -->
								<p style="margin: 0 0 12px 0; font-size: 12px; color: #666; line-height: 1.5;">
									<?php echo esc_html( isset( $issue['description'] ) ? substr( $issue['description'], 0, 80 ) : '' ); ?>
									<?php if ( isset( $issue['description'] ) && strlen( $issue['description'] ) > 80 ) : ?>
										...
									<?php endif; ?>
								</p>
								
								<!-- Threat Level Badge -->
								<div style="display: inline-block; padding: 4px 8px; background: <?php echo esc_attr( $threat_color ); ?>; color: white; border-radius: 3px; font-size: 11px; font-weight: 600; margin-bottom: 12px;">
									<?php echo esc_html( $threat_label ); ?>
								</div>
								
								<!-- Action Buttons -->
								<div style="display: flex; gap: 6px; margin-top: 12px;">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-action-items&finding_id=' . $finding_id ) ); ?>" class="button button-small" style="font-size: 11px; padding: 4px 8px; text-decoration: none; flex: 1; text-align: center;">
										<?php esc_html_e( 'View', 'wpshadow' ); ?>
									</a>
									<button class="button button-small wpshadow-quick-autofix" data-finding-id="<?php echo esc_attr( $finding_id ); ?>" style="font-size: 11px; padding: 4px 8px; cursor: pointer; flex: 1;">
										<?php esc_html_e( 'Fix Now', 'wpshadow' ); ?>
									</button>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				
				<!-- Bulk Action -->
				<div style="margin-top: 16px; padding: 12px; background: #f9f9f9; border-radius: 6px; display: flex; align-items: center; gap: 12px;">
					<input type="checkbox" id="wpshadow-select-all-top-issues" style="cursor: pointer;">
					<label for="wpshadow-select-all-top-issues" style="cursor: pointer; font-size: 12px; margin: 0;">
						<?php esc_html_e( 'Select all', 'wpshadow' ); ?>
					</label>
					<button id="wpshadow-bulk-create-workflow" class="button button-small" style="margin-left: auto; font-size: 11px; padding: 4px 12px;">
						<?php esc_html_e( 'Create Workflow for Selected', 'wpshadow' ); ?>
					</button>
				</div>
			<?php else : ?>
				<div style="padding: 32px; text-align: center; background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); border: 1px solid #c8e6c9; border-radius: 8px;">
					<div style="font-size: 40px; margin-bottom: 12px;">✨</div>
					<p style="margin: 0; font-size: 14px; color: #2e7d32; font-weight: 600;">
						<?php esc_html_e( 'No Issues Detected!', 'wpshadow' ); ?>
					</p>
					<p style="margin: 8px 0 0 0; font-size: 12px; color: #558b2f;">
						<?php esc_html_e( 'Your site looks great. Keep it up!', 'wpshadow' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			// Quick auto-fix button
			$('.wpshadow-quick-autofix').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var findingId = $btn.data('finding-id');
				
				$btn.prop('disabled', true).text('Fixing...');
				
				$.post(ajaxurl, {
					action: 'wpshadow_autofix_finding',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_autofix' ); ?>',
					finding_id: findingId
				}, function(response) {
					if (response.success) {
						$btn.closest('div').fadeOut(300, function() {
							$(this).html('<div style="padding: 12px; background: #e8f5e9; border-radius: 4px; color: #2e7d32; font-weight: 600; text-align: center;">✓ <?php esc_html_e( 'Fixed!', 'wpshadow' ); ?></div>');
							$(this).fadeIn(200);
						});
					} else {
						alert('Error: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
						$btn.prop('disabled', false).text('<?php esc_html_e( 'Fix Now', 'wpshadow' ); ?>');
					}
				});
			});
			
			// Select all checkbox
			$('#wpshadow-select-all-top-issues').on('change', function() {
				var isChecked = $(this).prop('checked');
				// Could implement multi-select here in future
			});
		});
		</script>
		<?php
	}

	/**
	 * Get top 3 issues by threat level
	 */
	private static function get_top_issues( $limit = 3 ) {
		if ( ! function_exists( 'wpshadow_get_site_findings' ) ) {
			return array();
		}

		$all_findings = wpshadow_get_site_findings();
		$dismissed    = get_option( 'wpshadow_dismissed_findings', array() );

		// Filter out dismissed findings
		$all_findings = array_filter(
			$all_findings,
			function ( $f ) use ( $dismissed ) {
				return ! isset( $f['id'] ) || ! isset( $dismissed[ $f['id'] ] );
			}
		);

		// Sort by threat level (highest first)
		usort(
			$all_findings,
			function ( $a, $b ) {
				$a_threat = isset( $a['threat_level'] ) ? $a['threat_level'] : 50;
				$b_threat = isset( $b['threat_level'] ) ? $b['threat_level'] : 50;
				return $b_threat - $a_threat;
			}
		);

		return array_slice( $all_findings, 0, $limit );
	}

	/**
	 * Get threat label for display
	 */
	private static function get_threat_label( $threat_level ) {
		if ( $threat_level >= 80 ) {
			return __( 'Critical', 'wpshadow' );
		} elseif ( $threat_level >= 60 ) {
			return __( 'High', 'wpshadow' );
		} elseif ( $threat_level >= 40 ) {
			return __( 'Medium', 'wpshadow' );
		} else {
			return __( 'Low', 'wpshadow' );
		}
	}

	/**
	 * Get threat color for display
	 */
	private static function get_threat_color( $threat_level ) {
		if ( $threat_level >= 80 ) {
			return '#f44336'; // Red
		} elseif ( $threat_level >= 60 ) {
			return '#ff9800'; // Orange
		} elseif ( $threat_level >= 40 ) {
			return '#ffc107'; // Amber
		} else {
			return '#2196f3'; // Blue
		}
	}
}
