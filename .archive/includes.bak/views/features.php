<?php

use WPShadow\WPSHADOW_Tab_Navigation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$level    = $level ?? 'core';
$hub_id   = $hub_id ?? '';
$spoke_id = $spoke_id ?? '';
$network  = $network_scope ?? ( is_multisite() && is_network_admin() );

$grouped_features = array();
foreach ( $features as $feature ) {
	$group = $feature['widget_group'] ?? 'general';
	if ( ! isset( $grouped_features[ $group ] ) ) {
		$grouped_features[ $group ] = array(
			'label'       => $feature['widget_label'] ?? 'General',
			'description' => $feature['widget_description'] ?? 'Features',
			'features'    => array(),
		);
	}
	$grouped_features[ $group ]['features'][] = $feature;
}

$GLOBALS['wpshadow_grouped_features'] = $grouped_features;

$metabox_screen_id = $GLOBALS['wpshadow_details_page_screen_id'] ?? 'toplevel_page_wpshadow';

?>
<div class="wrap">
	<h1><?php echo esc_html__( 'Features', 'wpshadow' ); ?></h1>
	<?php settings_errors( 'wpshadow_features' ); ?>

	<?php

	$user_id = get_current_user_id();
	$wizard_dismissed_raw = get_user_meta( $user_id, 'wpshadow_setup_wizard_dismissed', true );
	$wizard_completed_raw = get_user_meta( $user_id, 'wpshadow_setup_wizard_completed', true );
	$wizard_dismissed = (bool) $wizard_dismissed_raw;
	$wizard_completed = (bool) $wizard_completed_raw;

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf( 
			'WPShadow Wizard Check - User: %d, Dismissed Raw: %s (%s), Completed Raw: %s (%s), Show Wizard: %s',
			$user_id,
			var_export( $wizard_dismissed_raw, true ),
			var_export( $wizard_dismissed, true ),
			var_export( $wizard_completed_raw, true ),
			var_export( $wizard_completed, true ),
			var_export( ( ! $wizard_dismissed && ! $wizard_completed && ! empty( $features ) ), true )
		) );
	}

	if ( ! $wizard_dismissed && ! $wizard_completed && ! empty( $features ) ) :

		$feature_explanations = array(
			'wpshadow_core_diagnostics'  => array(
				'title'       => 'Keep Your Site Healthy',
				'description' => 'Think of this as a regular check-up for your website. It watches for problems, helps you recover if something goes wrong, and keeps important safeguards in place. Like having a doctor on call for your site.',
				'recommended' => true,
			),
			'wpshadow_tips_coach'        => array(
				'title'       => 'Get Smart Suggestions',
				'description' => 'Your website gives you helpful recommendations based on what type of site you have. It\'s like having an expert advisor pointing out things you might have missed.',
				'recommended' => true,
			),
			'a11y-audit'            => array(
				'title'       => 'Make Your Site Work for Everyone',
				'description' => 'This checks that people with disabilities can use your website easily. It looks for things like proper contrast, keyboard navigation, and screen reader support. Making your site accessible isn\'t just good practice—it\'s the right thing to do.',
				'recommended' => true,
			),
			'script-deferral'       => array(
				'title'       => 'Speed Up Your Page Loading',
				'description' => 'This delays loading certain scripts until after your page content shows up, so visitors see your page faster. Think of it like getting the main course served quickly while the waiter prepares dessert.',
				'recommended' => false,
			),
			'asset-version-removal' => array(
				'title'       => 'Better Browser Caching',
				'description' => 'Removes version numbers from file links so browsers can cache them longer. This means repeat visitors load your site faster because their browser remembers files from last time.',
				'recommended' => true,
			),
			'head-cleanup'          => array(
				'title'       => 'Clean Up Extra Code',
				'description' => 'Removes unnecessary code that WordPress adds to every page. This makes your site a bit faster and slightly more secure by not advertising what version of WordPress you\'re running.',
				'recommended' => true,
			),
			'block-cleanup'         => array(
				'title'       => 'Remove Unused Editor Styles',
				'description' => 'If you don\'t use the block editor, this removes its styling code from your pages. That\'s 100KB+ your visitors don\'t need to download.',
				'recommended' => false,
			),
			'css-class-cleanup'     => array(
				'title'       => 'Simplify Your HTML',
				'description' => 'WordPress adds dozens of CSS classes to posts and menus. This strips most of them out to make your code cleaner and pages slightly smaller.',
				'recommended' => false,
			),
			'plugin-cleanup'        => array(
				'title'       => 'Stop Loading Plugin Files Everywhere',
				'description' => 'Many plugins load their CSS and scripts on every page, even where they\'re not needed. This prevents that waste, loading them only on pages where they\'re actually used.',
				'recommended' => false,
			),
			'html-cleanup'          => array(
				'title'       => 'Compress Your Page Code',
				'description' => 'Removes extra spaces, comments, and empty tags from your HTML. Makes pages 20-40% smaller, which means faster downloads for your visitors.',
				'recommended' => false,
			),
			'resource-hints'        => array(
				'title'       => 'Pre-Connect to External Resources',
				'description' => 'Tells browsers to start connecting to external services (like Google Fonts) before they\'re needed, making those resources load faster when the time comes.',
				'recommended' => true,
			),
			'nav-accessibility'     => array(
				'title'       => 'Make Menus Easier to Navigate',
				'description' => 'Adds helpful markers for screen readers and simplifies menu code so it\'s easier for everyone to use your navigation, especially people using keyboards or assistive technology.',
				'recommended' => true,
			),
			'skiplinks'             => array(
				'title'       => 'Add Quick Navigation Links',
				'description' => 'Adds invisible "skip to content" links that keyboard users can use to jump past your menu straight to the main content. A small addition that makes a big difference for accessibility.',
				'recommended' => true,
			),
			'embed-disable'         => array(
				'title'       => 'Remove Embedding Features',
				'description' => 'Removes the code that lets other sites embed your content. Unless you specifically want that feature, turning it off makes your site a little faster.',
				'recommended' => true,
			),
			'jquery-cleanup'        => array(
				'title'       => 'Remove Old jQuery Code',
				'description' => 'Removes an old compatibility script that most modern sites don\'t need anymore. Safe to turn on unless you have very old plugins.',
				'recommended' => true,
			),
		);
		?>
		<div id="wps-setup-wizard" class="wps-wizard" data-current-step="0" data-total-features="<?php echo count( $features ); ?>">
			<div class="wps-wizard-header">
				<button type="button" class="wps-wizard-dismiss" title="<?php esc_attr_e( 'Dismiss wizard', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			</div>
			<div class="wps-wizard-content">
				<div class="wps-wizard-step wps-wizard-welcome active">
					<h2><?php esc_html_e( 'Welcome! Let\'s Set Up Your Site Features', 'wpshadow' ); ?></h2>
					<p style="font-size: 16px; line-height: 1.6; max-width: 700px; margin: 0 auto 24px;">
						<?php esc_html_e( 'We\'ll walk you through each feature one at a time and explain what it does in plain English. You can turn features on or off as we go, and your choices will be saved automatically.', 'wpshadow' ); ?>
					</p>
					<p style="font-size: 14px; color: #fff; font-weight: 600; margin-bottom: 24px;">
						<?php printf( esc_html__( 'This will take about %d minutes. You can skip or dismiss this anytime.', 'wpshadow' ), ceil( count( $features ) / 10 ) ); ?>
					</p>
					<button type="button" class="button button-primary button-hero wps-wizard-start"><?php esc_html_e( 'Get Started', 'wpshadow' ); ?></button>
				</div>
				<?php
				$step_index = 0;
				foreach ( $features as $feature ) :
					$feature_id  = $feature['id'] ?? '';
					$explanation = $feature_explanations[ $feature_id ] ?? array(
						'title'       => $feature['name'] ?? $feature_id,
						'description' => $feature['description'] ?? '',
						'recommended' => false,
					);
					$is_enabled  = ! empty( $feature['enabled'] );
					++$step_index;
					?>
					<div class="wps-wizard-step wps-wizard-feature" data-feature-id="<?php echo esc_attr( $feature_id ); ?>" data-step="<?php echo esc_attr( (string) $step_index ); ?>">
						<div class="wps-wizard-step-number"><?php printf( esc_html__( 'Feature %1$d of %2$d', 'wpshadow' ), $step_index, count( $features ) ); ?></div>
						<h2><?php echo esc_html( $explanation['title'] ); ?></h2>
						<?php if ( $explanation['recommended'] ) : ?>
							<span class="wps-wizard-badge wps-recommended"><?php esc_html_e( 'Recommended', 'wpshadow' ); ?></span>
						<?php endif; ?>
						<p style="font-size: 16px; line-height: 1.6; max-width: 650px; margin: 20px auto 32px;">
							<?php echo esc_html( $explanation['description'] ); ?>
						</p>
						<div class="wps-wizard-toggle-group">
							<label class="wps-wizard-toggle-label">
								<input type="checkbox" class="wps-wizard-feature-toggle" data-feature-id="<?php echo esc_attr( $feature_id ); ?>" <?php checked( $is_enabled ); ?> />
								<span class="wps-toggle-switch"></span>
								<span class="wps-toggle-text"><?php esc_html_e( 'Enable this feature', 'wpshadow' ); ?></span>
							</label>
						</div>
						<div class="wps-wizard-actions">
							<button type="button" class="button wps-wizard-prev"><?php esc_html_e( 'Previous', 'wpshadow' ); ?></button>
							<button type="button" class="button button-primary wps-wizard-next"><?php esc_html_e( 'Next', 'wpshadow' ); ?></button>
						</div>
					</div>
				<?php endforeach; ?>
				<div class="wps-wizard-step wps-wizard-complete">
					<div class="dashicons dashicons-yes-alt" style="font-size: 80px; width: 80px; height: 80px; color: #46b450; margin: 0 auto 24px;"></div>
					<h2><?php esc_html_e( 'All Set!', 'wpshadow' ); ?></h2>
					<p style="font-size: 16px; line-height: 1.6; max-width: 600px; margin: 0 auto 32px;">
						<?php esc_html_e( 'Your features are configured and ready to go. You can always change these settings later from the features list below.', 'wpshadow' ); ?>
					</p>
					<button type="button" class="button button-primary button-hero wps-wizard-finish"><?php esc_html_e( 'Finish Setup', 'wpshadow' ); ?></button>
				</div>
			</div>
			<div class="wps-wizard-progress">
				<div class="wps-wizard-progress-bar" style="width: 0%;"></div>
			</div>
		</div>
	<?php endif; ?>

	<div class="wps-features-container">
		<?php $form_action = \WPShadow\CoreSupport\WPSHADOW_Tab_Navigation::build_tab_url( 'features' ); ?>
		<form method="post" id="wps-features-form" action="<?php echo esc_url( $form_action ); ?>">
			<?php wp_nonce_field( 'wpshadow_save_features', 'wpshadow_features_nonce' ); ?>
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			<input type="hidden" name="wpshadow_features_context[level]" value="<?php echo esc_attr( $level ); ?>" />
			<input type="hidden" name="wpshadow_features_context[hub]" value="<?php echo esc_attr( $hub_id ); ?>" />
			<input type="hidden" name="wpshadow_features_context[spoke]" value="<?php echo esc_attr( $spoke_id ); ?>" />

			<?php if ( empty( $features ) ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'No features registered for this context yet.', 'wpshadow' ); ?></p>
				</div>
			<?php else : ?>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<?php do_meta_boxes( $metabox_screen_id, 'normal', null ); ?>
						</div>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<?php do_meta_boxes( $metabox_screen_id, 'side', null ); ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

			<p class="submit">
				<button type="submit" class="button button-primary">
					<?php echo esc_html__( 'Save features', 'wpshadow' ); ?>
				</button>
				<div style="display: inline-flex; align-items: center; gap: 15px; margin-left: 15px;">
					<?php if ( $network ) : ?>
						<span style="color:#666; font-size:12px;">
							<?php echo esc_html__( 'Network scope', 'wpshadow' ); ?>
						</span>
					<?php endif; ?>
				</div>
			</p>
			<?php

			if ( $wizard_dismissed || $wizard_completed ) :
				?>
				<p style="margin-top: 0; padding-top: 0;">
					<a href="#" class="wps-rerun-wizard" style="color: #667eea; text-decoration: none; font-size: 13px;">
						<?php esc_html_e( 'Rerun Setup Wizard', 'wpshadow' ); ?>
					</a>
				</p>
				<?php
				endif;
			?>
		</form>
	</div>
</div>

<style>
	.meta-box-sortables {
		display: flex;
		flex-direction: column;
		gap: 0;
	}

	.postbox .inside {
		margin: 0 !important;
		padding: 0 !important;
	}

	.wp-list-table.widefat.fixed.striped {
		border: none;
		box-shadow: none;
	}

	.wps-feature-widget {
		background: #fff;
		border: 1px solid #ddd;
		border-radius: 3px;
		margin-bottom: 20px;
		cursor: move;
	}

	.wps-feature-widget.closed .wps-widget-content {
		display: none;
	}

	.wps-feature-widget .wp-list-table {
		border-collapse: collapse;
	}

	.wps-feature-widget tbody tr {
		border-bottom: 1px solid #ddd;
	}

	.wps-feature-widget tbody tr:last-child {
		border-bottom: none;
	}

	.wps-feature-widget td,
	.wps-feature-widget th {
		padding: 10px 8px;
		vertical-align: top;
	}

	.wps-feature-widget .check-column {
		text-align: center;
	}

	.wps-feature-toggle-label {
		display: inline-block;
		position: relative;
		cursor: pointer;
		padding-top: 10px;
	}

	.wps-feature-toggle-input {
		position: absolute;
		opacity: 0;
		width: 0;
		height: 0;
	}

	.wps-feature-toggle-switch {
		display: inline-block;
		position: relative;
		width: 44px;
		height: 24px;
		background: #dcdcde;
		border-radius: 12px;
		transition: background-color 0.3s ease;
		vertical-align: middle;
	}

	.wps-feature-toggle-switch::after {
		content: '';
		position: absolute;
		top: 2px;
		left: 2px;
		width: 20px;
		height: 20px;
		background: #fff;
		border-radius: 50%;
		transition: transform 0.3s ease;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
	}

	.wps-feature-toggle-input:checked + .wps-feature-toggle-switch {
		background: #2271b1;
	}

	.wps-feature-toggle-input:checked + .wps-feature-toggle-switch::after {
		transform: translateX(20px);
	}

	.wps-feature-toggle-input:focus + .wps-feature-toggle-switch {
		box-shadow: 0 0 0 2px #fff, 0 0 0 4px #2271b1;
		outline: 2px solid transparent;
	}

	.wps-feature-toggle-label:hover .wps-feature-toggle-switch {
		background: #c3c4c7;
	}

	.wps-feature-toggle-label:hover .wps-feature-toggle-input:checked + .wps-feature-toggle-switch {
		background: #135e96;
	}
</style>

<script>
jQuery(document).ready(function($) {

});
</script>

<style>
.wps-wizard {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #fff;
	padding: 40px 20px;
	border-radius: 8px;
	margin-bottom: 30px;
	box-shadow: 0 4px 20px rgba(0,0,0,0.15);
	position: relative;
}
.wps-wizard-header {
	position: relative;
	margin-bottom: 30px;
}
.wps-wizard-progress {
	height: 4px;
	background: rgba(255,255,255,0.2);
	border-radius: 2px;
	overflow: hidden;
	margin-top: 30px;
}
.wps-wizard-progress-bar {
	height: 100%;
	background: #fff;
	transition: width 0.3s ease;
	border-radius: 2px;
}
.wps-wizard-dismiss {
	position: absolute;
	top: -20px;
	right: 10px;
	background: rgba(255,255,255,0.2);
	border: none;
	color: #fff;
	width: 32px;
	height: 32px;
	border-radius: 50%;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: background 0.2s;
}
.wps-wizard-dismiss:hover {
	background: rgba(255,255,255,0.3);
}
.wps-wizard-content {
	max-width: 900px;
	margin: 0 auto;
	text-align: center;
}
.wps-wizard-step {
	display: none;
}
.wps-wizard-step.active {
	display: block;
	animation: fadeIn 0.4s ease;
}
@keyframes fadeIn {
	from { opacity: 0; transform: translateY(10px); }
	to { opacity: 1; transform: translateY(0); }
}
.wps-wizard-step h2 {
	color: #fff;
	font-size: 32px;
	margin: 0 0 16px;
	font-weight: 600;
}
.wps-wizard-step-number {
	font-size: 14px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 1px;
	opacity: 0.8;
	margin-bottom: 12px;
}
.wps-wizard-badge {
	display: inline-block;
	padding: 6px 16px;
	border-radius: 20px;
	font-size: 13px;
	font-weight: 600;
	margin: 8px 0 16px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}
.wps-wizard-badge.wps-recommended {
	background: #46b450;
	color: #fff;
}
.wps-wizard-toggle-group {
	margin: 40px auto;
	display: inline-block;
}
.wps-wizard-toggle-label {
	display: flex;
	align-items: center;
	gap: 16px;
	cursor: pointer;
	background: rgba(255,255,255,0.15);
	padding: 20px 32px;
	border-radius: 50px;
	transition: background 0.2s;
	border: 2px solid rgba(255,255,255,0.2);
}
.wps-wizard-toggle-label:hover {
	background: rgba(255,255,255,0.25);
	border-color: rgba(255,255,255,0.4);
}
.wps-wizard-feature-toggle {
	display: none !important;
	visibility: hidden !important;
	opacity: 0 !important;
	position: absolute !important;
	width: 0 !important;
	height: 0 !important;
	margin: 0 !important;
	padding: 0 !important;
}
.wps-toggle-switch {
	position: relative;
	width: 56px;
	height: 32px;
	background: rgba(0,0,0,0.3);
	border-radius: 16px;
	transition: background 0.3s;
	flex-shrink: 0;
}
.wps-toggle-switch::after {
	content: '';
	position: absolute;
	top: 4px;
	left: 4px;
	width: 24px;
	height: 24px;
	background: #fff;
	border-radius: 50%;
	transition: transform 0.3s;
	box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.wps-wizard-feature-toggle:checked + .wps-toggle-switch {
	background: #46b450;
}
.wps-wizard-feature-toggle:checked + .wps-toggle-switch::after {
	transform: translateX(24px);
}
.wps-toggle-text {
	font-size: 18px;
	font-weight: 500;
	color: #fff;
}
.wps-wizard-actions {
	display: flex;
	gap: 12px;
	justify-content: center;
	align-items: center;
	margin-top: 40px;
}
.wps-wizard-actions .button {
	min-width: 120px;
	height: 44px;
	font-size: 15px;
}
.wps-wizard-actions .button-primary {
	background: #fff;
	color: #667eea;
	border: none;
	box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.wps-wizard-actions .button-primary:hover {
	background: #f5f5f5;
	color: #667eea;
}
.wps-wizard-actions .button:not(.button-primary) {
	background: rgba(255,255,255,0.15);
	color: #fff;
	border: 1px solid rgba(255,255,255,0.3);
}
.wps-wizard-actions .button:not(.button-primary):hover {
	background: rgba(255,255,255,0.25);
}
.wps-wizard-welcome .button-hero {
	height: 54px;
	font-size: 17px;
	padding: 0 40px;
}
.wps-wizard-complete {
	padding: 40px 0;
}
@media (max-width: 782px) {
	.wps-wizard {
		padding: 30px 15px;
	}
	.wps-wizard-step h2 {
		font-size: 24px;
	}
	.wps-wizard-actions {
		flex-direction: column;
		width: 100%;
	}
	.wps-wizard-actions .button {
		width: 100%;
	}
}

.wps-widget-toggle {
	position: relative;
	display: inline-flex;
	align-items: center;
	cursor: pointer;
}
.wps-toggle-input {
	display: none !important;
}
.wps-toggle-slider {
	position: relative;
	display: inline-block;
	width: 44px;
	height: 24px;
	background: #ccc;
	border-radius: 24px;
	transition: background 0.3s ease;
	border: none;
	outline: none;
}
.wps-toggle-slider::after {
	content: '';
	position: absolute;
	width: 20px;
	height: 20px;
	background: white;
	border-radius: 50%;
	top: 2px;
	left: 2px;
	transition: left 0.3s ease;
	box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
.wps-toggle-input:checked + .wps-toggle-slider {
	background: #667eea;
}
.wps-toggle-input:checked + .wps-toggle-slider::after {
	left: 22px;
}
.wps-toggle-input:focus + .wps-toggle-slider {
	outline: 2px solid #667eea;
	outline-offset: 2px;
}
</style>

<script>
jQuery(document).ready(function($) {
	var wizard = $('#wps-setup-wizard');
	console.log('Wizard element found:', wizard.length);
	if (!wizard.length) return;

	var steps = wizard.find('.wps-wizard-step');
	console.log('Total steps found:', steps.length);
	var currentStep = 0;
	var totalSteps = steps.length;
	var progressBar = wizard.find('.wps-wizard-progress-bar');

	wizard.find('.wps-wizard-prev').hide();

	function updateProgress() {
		var progress = (currentStep / (totalSteps - 1)) * 100;
		progressBar.css('width', progress + '%');
	}

	function showStep(index) {
		steps.removeClass('active');
		steps.eq(index).addClass('active');
		currentStep = index;
		updateProgress();

		if (index <= 1) {
			wizard.find('.wps-wizard-prev').hide();
		} else {
			wizard.find('.wps-wizard-prev').show();
		}

		if (index === totalSteps - 2) {
			wizard.find('.wps-wizard-next').text('<?php esc_html_e( 'Finish', 'wpshadow' ); ?>');
		}
	}

	function saveFeatureState(featureId, enabled) {
		return $.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpshadow_wizard_save_feature',
				nonce: '<?php echo wp_create_nonce( 'wpshadow_wizard_save' ); ?>',
				feature_id: featureId,
				enabled: enabled ? 1 : 0
			}
		});
	}

	function completeWizard() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpshadow_wizard_complete',
				nonce: '<?php echo wp_create_nonce( 'wpshadow_wizard_complete' ); ?>'
			},
			success: function() {
				wizard.slideUp(400, function() {
					wizard.remove();
					location.reload();
				});
			}
		});
	}

	function dismissWizard() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpshadow_wizard_dismiss',
				nonce: '<?php echo wp_create_nonce( 'wpshadow_wizard_dismiss' ); ?>'
			},
			success: function() {
				wizard.slideUp(400, function() {
					wizard.remove();
				});
			}
		});
	}

	wizard.find('.wps-wizard-start').on('click', function() {
		console.log('Get Started clicked, showing step 1');
		showStep(1);
	});
	console.log('Get Started button handler attached to', wizard.find('.wps-wizard-start').length, 'elements');

	wizard.find('.wps-wizard-next').on('click', function() {
		var currentFeatureStep = steps.eq(currentStep);
		if (currentFeatureStep.hasClass('wps-wizard-feature')) {
			var featureId = currentFeatureStep.data('feature-id');
			var toggle = currentFeatureStep.find('.wps-wizard-feature-toggle');
			var enabled = toggle.is(':checked');

			saveFeatureState(featureId, enabled);
		}

		if (currentStep < totalSteps - 1) {
			showStep(currentStep + 1);
		}
	});

	wizard.find('.wps-wizard-prev').on('click', function() {
		if (currentStep > 1) {
			showStep(currentStep - 1);
		}
	});

	wizard.find('.wps-wizard-finish').on('click', function() {
		completeWizard();
	});

	wizard.find('.wps-wizard-dismiss').on('click', function() {
		if (confirm('<?php esc_html_e( 'Are you sure you want to dismiss the setup wizard?', 'wpshadow' ); ?>')) {
			dismissWizard();
		}
	});

	wizard.find('.wps-wizard-feature-toggle').on('change', function() {
		var featureId = $(this).data('feature-id');
		var enabled = $(this).is(':checked');
		saveFeatureState(featureId, enabled);
	});
});
</script>

<script>
jQuery(document).ready(function($) {

	$('.wps-rerun-wizard').on('click', function(e) {
		e.preventDefault();
		console.log('Rerun wizard clicked');
		var link = $(this);
		var originalText = link.text();
		link.css('opacity', '0.5').css('pointer-events', 'none');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpshadow_wizard_reset',
				nonce: '<?php echo wp_create_nonce( 'wpshadow_wizard_reset' ); ?>'
			},
			success: function(response) {
				console.log('Wizard reset response:', response);

				window.location.href = window.location.href.split('?')[0] + '?page=wpshadow&wpshadow_tab=features&_=' + Date.now();
			},
			error: function(xhr, status, error) {
				console.error('Wizard reset error:', error, xhr.responseText);
				link.css('opacity', '1').css('pointer-events', 'auto');
				alert('<?php esc_html_e( 'Failed to reset wizard. Please try again.', 'wpshadow' ); ?>');
			}
		});
	});
});
</script>
<script>
jQuery(document).ready(function($){

	if (typeof postboxes !== 'undefined') {
		postboxes.add_postbox_toggles('toplevel_page_wpshadow');
	}
});
</script>