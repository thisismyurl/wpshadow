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
						<button type="button" class="wps-btn wps-btn-primary select-platform">
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
				<h2><?php esc_html_e( 'Help Us Help You', 'wpshadow' ); ?></h2>
				<p class="lead">
					<?php esc_html_e( 'How would you describe your comfort level with learning new tools?', 'wpshadow' ); ?>
				</p>
				<p class="note">
					<?php esc_html_e( 'This helps us show you the right amount of detail. No wrong answer!', 'wpshadow' ); ?>
				</p>
			</div>

			<div class="comfort-options">
				<div class="comfort-card" data-comfort="learning">
					<div class="comfort-icon">
						<span class="dashicons dashicons-welcome-learn-more"></span>
					</div>
					<h3><?php esc_html_e( 'I like to take my time', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'I prefer step-by-step guidance and clear explanations', 'wpshadow' ); ?></p>
					<button type="button" class="wps-btn wps-btn-primary select-comfort">
						<?php esc_html_e( 'That\'s Me', 'wpshadow' ); ?>
					</button>
				</div>

				<div class="comfort-card" data-comfort="comfortable">
					<div class="comfort-icon">
						<span class="dashicons dashicons-yes"></span>
					</div>
					<h3><?php esc_html_e( 'I can figure things out', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'I like some guidance but I\'m comfortable exploring', 'wpshadow' ); ?></p>
					<button type="button" class="wps-btn wps-btn-primary select-comfort">
						<?php esc_html_e( 'That\'s Me', 'wpshadow' ); ?>
					</button>
				</div>

				<div class="comfort-card" data-comfort="expert">
					<div class="comfort-icon">
						<span class="dashicons dashicons-superhero"></span>
					</div>
					<h3><?php esc_html_e( 'I dive right in', 'wpshadow' ); ?></h3>
					<p><?php esc_html_e( 'I learn by doing and prefer minimal hand-holding', 'wpshadow' ); ?></p>
					<button type="button" class="wps-btn wps-btn-primary select-comfort">
						<?php esc_html_e( 'That\'s Me', 'wpshadow' ); ?>
					</button>
				</div>
			</div>

			<p class="onboarding-nav">
				<a href="#" class="back-step"><?php esc_html_e( '← Back', 'wpshadow' ); ?></a>
			</p>
		</div>

		<!-- Step 3: Configuration Preferences -->
		<div class="onboarding-step" id="step-config">
			<div class="onboarding-welcome">
				<h2><?php esc_html_e( 'Let\'s Set Up Your Preferences', 'wpshadow' ); ?></h2>
				<p class="lead" id="config-intro"></p>
			</div>

			<div class="config-form">
				<div class="config-option">
					<label class="config-label">
						<input type="checkbox" name="auto_scan" value="1" checked />
						<span class="config-title" id="config-scan-title"><?php esc_html_e( 'Automatic Health Checks', 'wpshadow' ); ?></span>
					</label>
					<p class="config-description" id="config-scan-desc">
						<?php esc_html_e( 'Run quick health checks daily to catch issues early', 'wpshadow' ); ?>
					</p>
				</div>

				<div class="config-option">
					<label class="config-label">
						<input type="checkbox" name="show_tips" value="1" checked />
						<span class="config-title"><?php esc_html_e( 'Helpful Tips & Guidance', 'wpshadow' ); ?></span>
					</label>
					<p class="config-description" id="config-tips-desc">
						<?php esc_html_e( 'Show contextual help tips as you work', 'wpshadow' ); ?>
					</p>
				</div>

				<div class="config-option">
					<label class="config-label">
						<input type="checkbox" name="track_improvements" value="1" checked />
						<span class="config-title"><?php esc_html_e( 'Track Your Progress', 'wpshadow' ); ?></span>
					</label>
					<p class="config-description">
						<?php esc_html_e( 'Show how much time you\'ve saved and issues you\'ve fixed', 'wpshadow' ); ?>
					</p>
				</div>
			</div>

			<div class="onboarding-actions">
				<button type="button" class="wps-btn wps-btn-primary" id="continue-config">
					<?php esc_html_e( 'Continue', 'wpshadow' ); ?>
				</button>
			</div>

			<p class="onboarding-nav">
				<a href="#" class="back-step"><?php esc_html_e( '← Back', 'wpshadow' ); ?></a>
			</p>
		</div>

		<!-- Step 4: Privacy & Communication -->
		<div class="onboarding-step" id="step-privacy">
			<div class="onboarding-welcome">
				<h2><?php esc_html_e( 'Your Privacy & Updates', 'wpshadow' ); ?></h2>
				<p class="lead">
					<?php esc_html_e( 'We respect your privacy. Here\'s what we\'d like your permission for:', 'wpshadow' ); ?>
				</p>
			</div>

			<div class="privacy-form">
				<div class="privacy-section">
					<h3><?php esc_html_e( '📧 Email Notifications', 'wpshadow' ); ?></h3>
					<div class="config-option">
						<label class="config-label">
							<input type="checkbox" name="email_critical" value="1" />
							<span class="config-title"><?php esc_html_e( 'Critical Security Issues', 'wpshadow' ); ?></span>
						</label>
						<p class="config-description">
							<?php esc_html_e( 'Only email me about urgent security problems (recommended)', 'wpshadow' ); ?>
						</p>
					</div>

					<div class="config-option">
						<label class="config-label">
							<input type="checkbox" name="email_weekly" value="1" />
							<span class="config-title"><?php esc_html_e( 'Weekly Summary', 'wpshadow' ); ?></span>
						</label>
						<p class="config-description">
							<?php esc_html_e( 'Optional weekly report of your site health and improvements', 'wpshadow' ); ?>
						</p>
					</div>
				</div>

				<div class="privacy-section">
					<h3><?php esc_html_e( '🔒 Anonymous Usage Data', 'wpshadow' ); ?></h3>
					<div class="config-option">
						<label class="config-label">
							<input type="checkbox" name="share_diagnostics" value="1" />
							<span class="config-title"><?php esc_html_e( 'Help Improve WPShadow', 'wpshadow' ); ?></span>
						</label>
						<p class="config-description">
							<?php esc_html_e( 'Share anonymous diagnostic results to help us improve (no personal data)', 'wpshadow' ); ?>
							<a href="https://wpshadow.com/privacy/" target="_blank" style="margin-left: 5px;">
								<?php esc_html_e( 'Privacy Policy', 'wpshadow' ); ?>
							</a>
						</p>
					</div>
				</div>

				<div class="privacy-section newsletter-section">
					<h3><?php esc_html_e( '💡 Stay Informed (Optional)', 'wpshadow' ); ?></h3>
					<p class="newsletter-intro">
						<?php esc_html_e( 'Get WordPress tips, security updates, and exclusive tutorials:', 'wpshadow' ); ?>
					</p>
					<div class="config-option">
						<label class="config-label">
							<input type="checkbox" name="newsletter" value="1" />
							<span class="config-title"><?php esc_html_e( 'Subscribe to WPShadow Newsletter', 'wpshadow' ); ?></span>
						</label>
						<p class="config-description">
							<?php esc_html_e( 'Monthly tips, free training, and WordPress best practices. Unsubscribe anytime.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="newsletter-email wps-none">
						<input type="email" name="newsletter_email" placeholder="<?php esc_attr_e( 'your.email@example.com', 'wpshadow' ); ?>"
							class="wps-p-8-rounded-4" />
					</div>
				</div>

				<p class="privacy-note">
					<?php esc_html_e( 'You can change any of these settings later in WPShadow → Settings → Privacy', 'wpshadow' ); ?>
				</p>
			</div>

			<div class="onboarding-actions">
				<button type="button" class="wps-btn wps-btn-primary" id="continue-privacy">
					<?php esc_html_e( 'Continue', 'wpshadow' ); ?>
				</button>
			</div>

			<p class="onboarding-nav">
				<a href="#" class="back-step"><?php esc_html_e( '← Back', 'wpshadow' ); ?></a>
			</p>
		</div>

		<!-- Step 5: Confirmation -->
		<div class="onboarding-step" id="step-confirm">
			<div class="onboarding-welcome">
				<h2><?php esc_html_e( '🎉 Perfect! You\'re All Set', 'wpshadow' ); ?></h2>
				<p class="lead" id="confirm-message"></p>
				<p class="confirm-summary" id="confirm-summary"></p>
			</div>

			<div class="onboarding-actions">
				<button type="button" class="wps-btn wps-btn-primary wps-btn-lg" id="finish-onboarding">
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
		box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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
		from {
			opacity: 0;
			transform: translateY(10px);
		}

		to {
			opacity: 1;
			transform: translateY(0);
		}
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

	.platform-cards {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
		gap: 15px;
		margin: 40px 0;
	}

	.comfort-options {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
		gap: 20px;
		margin: 40px 0;
	}

	.platform-card {
		border: 2px solid #dcdcde;
		border-radius: 8px;
		padding: 20px 15px;
		text-align: center;
		transition: all 0.3s ease;
		cursor: pointer;
	}

	.comfort-card {
		border: 2px solid #dcdcde;
		border-radius: 8px;
		padding: 30px 20px;
		text-align: center;
		transition: all 0.3s ease;
		cursor: pointer;
	}

	.platform-icon {
		width: 60px;
		height: 60px;
		margin: 0 auto 15px;
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
	}

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

	/* Config & Privacy styles */
	.config-form,
	.privacy-form {
		max-width: 600px;
		margin: 30px auto;
	}

	.config-option {
		margin-bottom: 25px;
		padding: 20px;
		background: #f9f9f9;
		border-radius: 8px;
		border: 1px solid #e0e0e0;
	}

	.privacy-section {
		margin-bottom: 30px;
		padding: 20px;
		background: #f9f9f9;
		border-radius: 8px;
		border: 1px solid #e0e0e0;
	}

	.privacy-section h3 {
		margin: 0 0 15px 0;
		color: #1d2327;
		font-size: 16px;
	}

	.config-label {
		display: flex;
		align-items: flex-start;
		cursor: pointer;
		gap: 12px;
	}

	.config-label input[type="checkbox"] {
		margin: 3px 0 0 0;
		width: 18px;
		height: 18px;
		cursor: pointer;
	}

	.config-title {
		font-weight: 600;
		color: #1d2327;
		font-size: 15px;
	}

	.config-description {
		margin: 8px 0 0 30px;
		color: #646970;
		font-size: 14px;
		line-height: 1.5;
	}

	.privacy-note {
		text-align: center;
		margin-top: 30px;
		padding-top: 20px;
		border-top: 1px solid #dcdcde;
		color: #646970;
		font-size: 13px;
		font-style: italic;
	}

	.newsletter-section {
		background: linear-gradient(135deg, #f0f7ff 0%, #e6f2ff 100%);
		border-color: #2271b1;
	}

	.newsletter-intro {
		color: #1d2327;
		font-size: 14px;
		margin-bottom: 15px;
	}

	.confirm-summary {
		background: #f0f7ff;
		padding: 20px;
		border-radius: 8px;
		margin-top: 20px;
		font-size: 14px;
		line-height: 1.6;
		color: #3c434a;
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

		// Ensure ajaxurl is defined
		const ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

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
			
			// Update config step based on selections
			updateConfigStep(selectedPlatform, selectedComfort);

			// Show next step
			$('#step-comfort').removeClass('active');
			$('#step-config').addClass('active');
		});
		
		// Continue from config step
		$('#continue-config').on('click', function() {
			// Collect config preferences
			const selectedConfig = {
				auto_scan: $('input[name="auto_scan"]').is(':checked'),
				show_tips: $('input[name="show_tips"]').is(':checked'),
				track_improvements: $('input[name="track_improvements"]').is(':checked')
			};
			
			// Store for later submission
			$('#continue-config').data('config', selectedConfig);
			
			// Show next step
			$('#step-config').removeClass('active');
			$('#step-privacy').addClass('active');
		});
		
		// Continue from privacy step
		$('#continue-privacy').on('click', function() {
			// Collect privacy preferences
			const selectedPrivacy = {
				email_critical: $('input[name="email_critical"]').is(':checked'),
				email_weekly: $('input[name="email_weekly"]').is(':checked'),
				share_diagnostics: $('input[name="share_diagnostics"]').is(':checked'),
				newsletter: $('input[name="newsletter"]').is(':checked'),
				newsletter_email: $('input[name="newsletter_email"]').val()
			};
			
			// Store for later submission
			$('#continue-privacy').data('privacy', selectedPrivacy);
			
			// Update confirmation message
			const messages = {
				'WordPress': '<?php echo esc_js( __( 'Since you know WordPress, we\'ll keep the standard interface but add helpful tips along the way.', 'wpshadow' ) ); ?>',
				'word': '<?php echo esc_js( __( 'We\'ll use terms familiar from Word and guide you to the WordPress way at your own pace.', 'wpshadow' ) ); ?>',
				'google-docs': '<?php echo esc_js( __( 'We\'ll use terms you know from Google Docs and make WordPress feel just as easy.', 'wpshadow' ) ); ?>',
				'wix': '<?php echo esc_js( __( 'We\'ll use terms you know from Wix and gradually introduce WordPress concepts.', 'wpshadow' ) ); ?>',
				'squarespace': '<?php echo esc_js( __( 'We\'ll use familiar Squarespace terms while you learn WordPress at your own pace.', 'wpshadow' ) ); ?>',
				'moodle': '<?php echo esc_js( __( 'We\'ll translate Moodle concepts to WordPress and help you build great learning experiences.', 'wpshadow' ) ); ?>',
				'notion': '<?php echo esc_js( __( 'We\'ll use Notion-style language and show you how WordPress can organize your content.', 'wpshadow' ) ); ?>',
				'none': '<?php echo esc_js( __( 'We\'ll start with the basics and build up your confidence step by step.', 'wpshadow' ) ); ?>'
			};
			
			$('#confirm-message').text(messages[selectedPlatform] || messages['none']);
			
			// Build summary
			const configData = $('#continue-config').data('config') || {};
			let summary = '<strong><?php echo esc_js( __( 'Your choices:', 'wpshadow' ) ); ?></strong><br>';
			if (configData.auto_scan) summary += '✓ <?php echo esc_js( __( 'Daily health checks', 'wpshadow' ) ); ?><br>';
			if (configData.show_tips) summary += '✓ <?php echo esc_js( __( 'Helpful tips enabled', 'wpshadow' ) ); ?><br>';
			if (selectedPrivacy.email_critical) summary += '✓ <?php echo esc_js( __( 'Critical alerts via email', 'wpshadow' ) ); ?><br>';
			if (selectedPrivacy.newsletter) summary += '✓ <?php echo esc_js( __( 'Newsletter subscription', 'wpshadow' ) ); ?><br>';
			$('#confirm-summary').html(summary);
			
			// Show confirmation step
			$('#step-privacy').removeClass('active');
			$('#step-confirm').addClass('active');
		});

		// Customize config step based on platform
		function updateConfigStep(platform, comfort) {
			const platformIntros = {
				'WordPress': '<?php echo esc_js( __( 'Since you know WordPress, let\'s quickly configure your monitoring preferences:', 'wpshadow' ) ); ?>',
				'word': '<?php echo esc_js( __( 'Let\'s set up some preferences to keep your content safe:', 'wpshadow' ) ); ?>',
				'google-docs': '<?php echo esc_js( __( 'Let\'s configure how we monitor your site:', 'wpshadow' ) ); ?>',
				'wix': '<?php echo esc_js( __( 'Let\'s set up automatic monitoring for your site:', 'wpshadow' ) ); ?>',
				'squarespace': '<?php echo esc_js( __( 'Configure your site monitoring preferences:', 'wpshadow' ) ); ?>',
				'moodle': '<?php echo esc_js( __( 'Set up monitoring for your learning platform:', 'wpshadow' ) ); ?>',
				'notion': '<?php echo esc_js( __( 'Configure how we help keep your content organized:', 'wpshadow' ) ); ?>',
				'none': '<?php echo esc_js( __( 'Let\'s set up some helpful monitoring to keep your site healthy:', 'wpshadow' ) ); ?>'
			};

			const scanTitles = {
				'word': '<?php echo esc_js( __( 'Automatic Document Checks', 'wpshadow' ) ); ?>',
				'google-docs': '<?php echo esc_js( __( 'Automatic File Checks', 'wpshadow' ) ); ?>',
				'moodle': '<?php echo esc_js( __( 'Course Health Checks', 'wpshadow' ) ); ?>',
				'default': '<?php echo esc_js( __( 'Automatic Health Checks', 'wpshadow' ) ); ?>'
			};

			const tipDescriptions = {
				'learning': '<?php echo esc_js( __( 'Show detailed explanations as you learn', 'wpshadow' ) ); ?>',
				'comfortable': '<?php echo esc_js( __( 'Show contextual help tips as you work', 'wpshadow' ) ); ?>',
				'expert': '<?php echo esc_js( __( 'Show quick tips only when needed', 'wpshadow' ) ); ?>'
			};

			$('#config-intro').text(platformIntros[platform] || platformIntros['none']);
			$('#config-scan-title').text(scanTitles[platform] || scanTitles['default']);
			$('#config-tips-desc').text(tipDescriptions[comfort] || tipDescriptions['comfortable']);
		}
		
		// Newsletter checkbox toggle
		$('input[name="newsletter"]').on('change', function() {
			if ($(this).is(':checked')) {
				$('.newsletter-email').removeClass('wps-none');
			} else {
				$('.newsletter-email').addClass('wps-none');
			}
		});

	// Back button
	$('.back-step').on('click', function(e) {
		e.preventDefault();
		
		const $current = $('.onboarding-step.active');
		const currentId = $current.attr('id');
		
		$current.removeClass('active');
		
		// Navigate to previous step
		if (currentId === 'step-comfort') {
			$('#step-platform').addClass('active');
		} else if (currentId === 'step-config') {
			$('#step-comfort').addClass('active');
		} else if (currentId === 'step-privacy') {
			$('#step-config').addClass('active');
		} else if (currentId === 'step-confirm') {
			$('#step-privacy').addClass('active');
		}
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
		
		// Collect all data
		const configData = $('#continue-config').data('config') || {};
		const privacyData = $('#continue-privacy').data('privacy') || {};

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpshadow_save_onboarding',
				platform: selectedPlatform,
				comfort_level: selectedComfort,
				config: configData,
				privacy: privacyData,
				nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_onboarding' ) ); ?>'
			},
			success: function(response) {
				if (response.success) {
					window.location.reload();
				} else {
					alert(response.data || '<?php echo esc_js( __( 'Something went wrong. Please try again.', 'wpshadow' ) ); ?>');
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Let\'s Go!', 'wpshadow' ) ); ?>');
				}
			},
			error: function(xhr, status, error) {
				console.error('AJAX Error:', status, error, xhr.responseText);
				alert('<?php echo esc_js( __( 'Connection error. Please try again.', 'wpshadow' ) ); ?>');
				$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Let\'s Go!', 'wpshadow' ) ); ?>');
			}
		});
	});
});
</script>
