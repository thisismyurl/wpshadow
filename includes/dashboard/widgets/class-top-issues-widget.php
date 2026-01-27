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
		<div class="wps-my-8">
			<div class="wps-flex-items-center-justify-space-between">
				<h2 class="wps-m-0"><?php esc_html_e( 'Top Issues to Address', 'wpshadow' ); ?></h2>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-findings' ) ); ?>" style="font-size: 12px; color: #0073aa; text-decoration: none; font-weight: 600;">
					<?php esc_html_e( 'View All →', 'wpshadow' ); ?>
				</a>
			</div>
			
			<?php if ( ! empty( $top_issues ) ) : ?>
				<div class="wps-grid wps-grid-auto-250 wps-gap-3">
					<?php
					foreach ( $top_issues as $index => $issue ) :
						$threat_level = isset( $issue['threat_level'] ) ? $issue['threat_level'] : 50;
						$threat_label = self::get_threat_label( $threat_level );
						$threat_color = self::get_threat_color( $threat_level );
						$category     = isset( $issue['category'] ) ? $issue['category'] : 'other';
						$finding_id   = isset( $issue['id'] ) ? $issue['id'] : '';
						?>
						<div class="wps-p-16-rounded-6">
							<!-- Rank Badge -->
							<div class="wps-flex-items-center-justify-center">
								<?php echo esc_html( $index + 1 ); ?>
							</div>
							
							<!-- Content -->
							<div style="padding-left: 44px;">
								<!-- Title -->
								<h3 class="wps-m-0">
									<?php echo esc_html( isset( $issue['title'] ) ? $issue['title'] : 'Unknown Issue' ); ?>
								</h3>
								
								<!-- Description -->
								<p class="wps-m-0">
									<?php echo esc_html( isset( $issue['description'] ) ? substr( $issue['description'], 0, 80 ) : '' ); ?>
									<?php if ( isset( $issue['description'] ) && strlen( $issue['description'] ) > 80 ) : ?>
										...
									<?php endif; ?>
								</p>
								
								<!-- Threat Level Badge -->
								<div class="wps-inline-block-p-4-rounded-3">
									<?php echo esc_html( $threat_label ); ?>
								</div>
								
								<!-- Action Buttons -->
								<div class="wps-flex wps-gap-2 wps-mt-3">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-findings&finding_id=' . $finding_id ) ); ?>" class="wps-btn wps-btn-secondary" class="wps-justify-center">
										<?php esc_html_e( 'View', 'wpshadow' ); ?>
									</a>
									<button class="wps-btn wps-btn-primary wpshadow-quick-autofix" data-finding-id="<?php echo esc_attr( $finding_id ); ?>" class="wps-justify-center">
										<?php esc_html_e( 'Fix Now', 'wpshadow' ); ?>
									</button>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				
				<!-- Bulk Action -->
				<div class="wps-flex wps-items-center wps-gap-3 wps-mt-4" class="wps-p-12-rounded-6">
					<input type="checkbox" id="wpshadow-select-all-top-issues" style="cursor: pointer;">
					<label for="wpshadow-select-all-top-issues" class="wps-m-0">
						<?php esc_html_e( 'Select all', 'wpshadow' ); ?>
					</label>
					<button id="wpshadow-bulk-create-workflow" class="wps-btn wps-btn-secondary" style="margin-left: auto;">
						<?php esc_html_e( 'Create Workflow for Selected', 'wpshadow' ); ?>
					</button>
				</div>
			<?php else : ?>
				<div class="wps-p-32-rounded-8">
					<div style="font-size: 40px; margin-bottom: 12px;">✨</div>
					<p class="wps-m-0">
						<?php esc_html_e( 'No Issues Detected!', 'wpshadow' ); ?>
					</p>
					<p class="wps-m-8">
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
							$(this).html('<div class="wps-p-12-rounded-4">✓ <?php esc_html_e( 'Fixed!', 'wpshadow' ); ?></div>');
							$(this).fadeIn(200);
						});
					} else {
						WPShadowModal.alert({title: '<?php esc_html_e( 'Error', 'wpshadow' ); ?>', message: (response.data && response.data.message ? response.data.message : 'Unknown error'), type: 'error'}));
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
