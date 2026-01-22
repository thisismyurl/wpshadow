<?php
/**
 * Onboarding Wizard View
 * 
 * Friendly, non-judgmental wizard to help users get started with WordPress.
 * 
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$platforms = \WPShadow\Onboarding\Platform_Translator::get_platforms();
?>

<div class="wpshadow-onboarding-wizard">
	<div class="wpshadow-onboarding-container">
		
		<!-- Step 1: Welcome & Platform Selection -->
		<div class="onboarding-step active" id="step-platform">
			<div class="onboarding-welcome">
				<h1><?php esc_html_e( 'Welcome! Let\'s Get You Started', 'wpshadow' ); ?></h1>
				<p class="lead">
					<?php esc_html_e( 'We want to make WordPress feel comfortable for you. To help us do that, which of these are you most familiar with?', 'wpshadow' ); ?>
				</p>
			</div>
			
			<div class="platform-cards">
				<?php foreach ( $platforms as $platform ) : ?>
					<div class="platform-card" data-platform="<?php echo esc_attr( $platform['id'] ); ?>">
						<div class="platform-icon">
							<span class="dashicons dashicons-<?php echo esc_attr( $platform['icon'] ); ?>"></span>
						</div>
						<h3><?php echo esc_html( $platform['label'] ); ?></h3>
						<p><?php echo esc_html( $platform['description'] ); ?></p>
						<button type="button" class="button button-primary select-platform">
							<?php esc_html_e( 'This One', 'wpshadow' ); ?>
						</button>
					</div>
				<?php endforeach; ?>
			</div>
			
			<p class="onboarding-skip">
				<a href="#" id="skip-onboarding"><?php esc_html_e( 'Skip for now', 'wpshadow' ); ?></a>
			</p>
		</div>
		
		<!-- Step 2: Technical Comfort Level -->
		<div class="onboarding-step" id="step-comfort">
			<div class="onboarding-welcome">
				<h2><?php esc_html_e( 'One More Thing...', 'wpshadow' ); ?></h2>
				<p class="lead">
					<?php esc_html_e( 'How would you describe your comfort level with learning new tools?', 'wpshadow' ); ?>
				</p>
				<p class="note">
					<?php esc_html_e( 'This just helps us show you the right amount of detail. No wrong answer!', 'wpshadow' ); ?>
				</p>
			</div>
			
			<div class="comfort-options">
				<div class="comfort-card" data-comfort="learning">
					<div class="comfort-icon">
						<span class="dashicons dashicons-welcome-learn-more"></span>
					</div>
					<h3><?php esc_html_e( 'I like to take my time', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'I prefer step-by-step guidance and clear explanations', 'wpshadow' ); ?></p>
					<button type="button" class="button button-primary select-comfort">
						<?php esc_html_e( 'That\'s Me', 'wpshadow' ); ?>
					</button>
				</div>
				
				<div class="comfort-card" data-comfort="comfortable">
					<div class="comfort-icon">
						<span class="dashicons dashicons-yes"></span>
					</div>
					<h3><?php esc_html_e( 'I can figure things out', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'I like some guidance but I\'m comfortable exploring', 'wpshadow' ); ?></p>
					<button type="button" class="button button-primary select-comfort">
						<?php esc_html_e( 'That\'s Me', 'wpshadow' ); ?>
					</button>
				</div>
				
				<div class="comfort-card" data-comfort="expert">
					<div class="comfort-icon">
						<span class="dashicons dashicons-superhero"></span>
					</div>
					<h3><?php esc_html_e( 'I dive right in', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'I learn by doing and prefer minimal hand-holding', 'wpshadow' ); ?></p>
					<button type="button" class="button button-primary select-comfort">
						<?php esc_html_e( 'That\'s Me', 'wpshadow' ); ?>
					</button>
				</div>
			</div>
			
			<p class="onboarding-nav">
				<a href="#" class="back-step"><?php esc_html_e( '← Back', 'wpshadow' ); ?></a>
			</p>
		</div>
		
		<!-- Step 3: Confirmation -->
		<div class="onboarding-step" id="step-confirm">
			<div class="onboarding-welcome">
				<h2><?php esc_html_e( 'Perfect! You\'re All Set', 'wpshadow' ); ?></h2>
				<p class="lead" id="confirm-message"></p>
			</div>
			
			<div class="onboarding-actions">
				<button type="button" class="button button-primary button-hero" id="finish-onboarding">
					<?php esc_html_e( 'Let\'s Go!', 'wpshadow' ); ?>
				</button>
			</div>
			
			<p class="onboarding-resources">
				<strong><?php esc_html_e( 'Helpful Resources:', 'wpshadow' ); ?></strong><br>
				<a href="https://wpshadow.com/kb/getting-started/" target="_blank"><?php esc_html_e( 'Getting Started Guide', 'wpshadow' ); ?></a> |
				<a href="https://wpshadow.com/training/" target="_blank"><?php esc_html_e( 'Video Tutorials', 'wpshadow' ); ?></a> |
				<a href="https://wpshadow.com/kb/" target="_blank"><?php esc_html_e( 'Knowledge Base', 'wpshadow' ); ?></a>
			</p>
		</div>
		
	</div>
</div>

<style>
.wpshadow-onboarding-wizard {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: 999999;
	display: flex;
	align-items: center;
	justify-content: center;
	overflow-y: auto;
}

.wpshadow-onboarding-container {
	background: white;
	border-radius: 12px;
	box-shadow: 0 20px 60px rgba(0,0,0,0.3);
	max-width: 900px;
	width: 90%;
	padding: 50px;
	margin: 20px;
}

.onboarding-step {
	display: none;
}

.onboarding-step.active {
	display: block;
	animation: fadeIn 0.4s ease-in;
}

@keyframes fadeIn {
	from { opacity: 0; transform: translateY(10px); }
	to { opacity: 1; transform: translateY(0); }
}

.onboarding-welcome h1,
.onboarding-welcome h2 {
	color: #1d2327;
	margin: 0 0 15px 0;
	text-align: center;
}

.onboarding-welcome .lead {
	font-size: 18px;
	line-height: 1.6;
	color: #3c434a;
	text-align: center;
	margin: 0 0 30px 0;
}

.onboarding-welcome .note {
	font-size: 14px;
	color: #646970;
	text-align: center;
	font-style: italic;
	margin: 10px 0 30px 0;
}

.platform-cards,
.comfort-options {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 20px;
	margin: 40px 0;
}

.platform-card,
.comfort-card {
	border: 2px solid #dcdcde;
	border-radius: 8px;
	padding: 30px 20px;
	text-align: center;
	transition: all 0.3s ease;
	cursor: pointer;
}

.platform-card:hover,
.comfort-card:hover {
	border-color: #2271b1;
	box-shadow: 0 4px 12px rgba(34, 113, 177, 0.15);
	transform: translateY(-2px);
}

.platform-icon,
.comfort-icon {
	width: 80px;
	height: 80px;
	margin: 0 auto 20px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
}

.platform-icon .dashicons,
.comfort-icon .dashicons {
	font-size: 40px;
	color: white;
	width: 40px;
	height: 40px;
}

.platform-card h3,
.comfort-card h3 {
	margin: 0 0 10px 0;
	font-size: 18px;
	color: #1d2327;
}

.platform-card p,
.comfort-card p {
	margin: 0 0 20px 0;
	color: #646970;
	font-size: 14px;
	line-height: 1.5;
}

.onboarding-skip,
.onboarding-nav {
	text-align: center;
	margin-top: 30px;
}

.onboarding-skip a,
.onboarding-nav a {
	color: #646970;
	text-decoration: none;
	font-size: 14px;
}

.onboarding-skip a:hover,
.onboarding-nav a:hover {
	color: #2271b1;
}

.onboarding-actions {
	text-align: center;
	margin: 40px 0;
}

.onboarding-resources {
	text-align: center;
	margin-top: 30px;
	padding-top: 30px;
	border-top: 1px solid #dcdcde;
	color: #646970;
	font-size: 14px;
}

.onboarding-resources a {
	color: #2271b1;
	text-decoration: none;
}

.onboarding-resources a:hover {
	text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
	.wpshadow-onboarding-container {
		padding: 30px 20px;
	}
	
	.platform-cards,
	.comfort-options {
		grid-template-columns: 1fr;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	let selectedPlatform = '';
	let selectedComfort = '';
	
	// Platform selection
	$('.select-platform').on('click', function() {
		selectedPlatform = $(this).closest('.platform-card').data('platform');
		
		// Show next step
		$('#step-platform').removeClass('active');
		$('#step-comfort').addClass('active');
	});
	
	// Comfort level selection
	$('.select-comfort').on('click', function() {
		selectedComfort = $(this).closest('.comfort-card').data('comfort');
		
		// Update confirmation message
		const messages = {
			'wordpress': '<?php echo esc_js( __( 'Since you know WordPress, we\'ll keep the standard interface but add helpful tips along the way.', 'wpshadow' ) ); ?>',
			'word': '<?php echo esc_js( __( 'We\'ll use terms familiar from Word and guide you to the WordPress way at your own pace.', 'wpshadow' ) ); ?>',
			'wix': '<?php echo esc_js( __( 'We\'ll use terms you know from Wix and gradually introduce WordPress concepts.', 'wpshadow' ) ); ?>',
			'none': '<?php echo esc_js( __( 'We\'ll start with the basics and build up your confidence step by step.', 'wpshadow' ) ); ?>'
		};
		
		$('#confirm-message').text(messages[selectedPlatform] || messages['none']);
		
		// Show confirmation step
		$('#step-comfort').removeClass('active');
		$('#step-confirm').addClass('active');
	});
	
	// Back button
	$('.back-step').on('click', function(e) {
		e.preventDefault();
		$('#step-comfort').removeClass('active');
		$('#step-platform').addClass('active');
	});
	
	// Skip onboarding
	$('#skip-onboarding').on('click', function(e) {
		e.preventDefault();
		
		if (!confirm('<?php echo esc_js( __( 'Are you sure? The onboarding helps us customize your experience.', 'wpshadow' ) ); ?>')) {
			return;
		}
		
		$.post(ajaxurl, {
			action: 'wpshadow_skip_onboarding',
			nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_onboarding' ) ); ?>'
		}, function() {
			window.location.reload();
		});
	});
	
	// Finish onboarding
	$('#finish-onboarding').on('click', function() {
		const $btn = $(this);
		$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Setting up...', 'wpshadow' ) ); ?>');
		
		$.post(ajaxurl, {
			action: 'wpshadow_save_onboarding',
			platform: selectedPlatform,
			comfort_level: selectedComfort,
			nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_onboarding' ) ); ?>'
		}, function(response) {
			if (response.success) {
				window.location.reload();
			} else {
				alert(response.data || '<?php echo esc_js( __( 'Something went wrong. Please try again.', 'wpshadow' ) ); ?>');
				$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Let\'s Go!', 'wpshadow' ) ); ?>');
			}
		});
	});
});
</script>
