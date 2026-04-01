<?php
/**
 * Site Health Knowledge Base
 *
 * Provides comprehensive guide about WordPress Site Health checks
 * with user-friendly explanations.
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'read' ) ) {
	wp_die( 'Insufficient permissions.' );
}
?>

<div class="wrap wps-help-container">
	<h1><?php esc_html_e( 'Site Health Explained', 'wpshadow' ); ?></h1>
	<?php do_action( 'wpshadow_after_page_header' ); ?>
	<p class="wps-help-subtitle">
		<?php esc_html_e( 'Your WordPress Site Health shows the overall status of your website\'s performance, security, and compatibility. This guide explains what each check means and why it matters.', 'wpshadow' ); ?>
	</p>

	<div class="wps-p-15-rounded-4">
		<p><strong><?php esc_html_e( 'Quick Tip:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Visit Tools → Site Health in your WordPress admin to check your current status.', 'wpshadow' ); ?></p>
	</div>

	<!-- REST API Section -->
	<div class="wps-p-20-rounded-4">
		<h2 id="rest-api"><?php esc_html_e( 'REST API', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'The REST API is how your site talks to the WordPress cloud and third-party services. Modern WordPress features rely on it.', 'wpshadow' ); ?></p>
		<h4><?php esc_html_e( 'What to look for:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Green status: REST API is working properly', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Yellow/Red status: Your server is blocking REST API requests', 'wpshadow' ); ?></li>
		</ul>
		<h4><?php esc_html_e( 'How to fix:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Contact your hosting provider to ensure /wp-json/ is not blocked', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Check your firewall or security plugin settings', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Temporarily disable security plugins to test', 'wpshadow' ); ?></li>
		</ul>
	</div>

	<!-- Loopback Requests Section -->
	<div class="wps-p-20-rounded-4">
		<h2 id="loopback-requests"><?php esc_html_e( 'Loopback Requests', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'Loopback requests allow your server to "talk to itself". WordPress uses this for scheduled tasks, updates, and background processing.', 'wpshadow' ); ?></p>
		<h4><?php esc_html_e( 'What to look for:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Green status: Your server can reach itself - scheduled tasks will run', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Red status: Your server cannot reach itself - updates and backups won\'t run automatically', 'wpshadow' ); ?></li>
		</ul>
		<h4><?php esc_html_e( 'How to fix:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Ask your hosting provider to whitelist your server\'s IP address for internal requests', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'If on shared hosting, ask them to disable request inspection for localhost', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Check that your site URL is accessible from the server itself', 'wpshadow' ); ?></li>
		</ul>
	</div>

	<!-- PHP Version Section -->
	<div class="wps-p-20-rounded-4">
		<h2 id="php-version"><?php esc_html_e( 'PHP Version', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'PHP is the language WordPress is built on. Newer versions are faster, more secure, and more reliable.', 'wpshadow' ); ?></p>
		<h4><?php esc_html_e( 'What to look for:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Recommended: PHP 8.0 or newer', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Minimum supported: PHP 7.4', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Below PHP 7.4: Security risk and compatibility issues', 'wpshadow' ); ?></li>
		</ul>
		<h4><?php esc_html_e( 'How to fix:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Ask your hosting provider to upgrade your PHP version', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Most hosting providers have a simple control panel option to change PHP version', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Test your site after upgrading to ensure plugins are compatible', 'wpshadow' ); ?></li>
		</ul>
	</div>

	<!-- SSL/HTTPS Section -->
	<div class="wps-p-20-rounded-4">
		<h2 id="ssl-https"><?php esc_html_e( 'SSL Certificate (HTTPS)', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'SSL encrypts the connection between your visitors and your site. It\'s required by modern browsers and helps with SEO.', 'wpshadow' ); ?></p>
		<h4><?php esc_html_e( 'What to look for:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Green status: Your site uses HTTPS - secure and trusted', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Red status: Your site is not using HTTPS - visitors will see security warnings', 'wpshadow' ); ?></li>
		</ul>
		<h4><?php esc_html_e( 'How to fix:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Most hosting providers offer free Let\'s Encrypt SSL certificates', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Enable HTTPS in your hosting control panel', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Update your WordPress Settings → General with https:// URLs', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Set up a redirect from HTTP to HTTPS (WordPress can do this automatically)', 'wpshadow' ); ?></li>
		</ul>
	</div>

	<!-- WordPress Updates Section -->
	<div class="wps-p-20-rounded-4">
		<h2 id="wordpress-updates"><?php esc_html_e( 'WordPress Updates', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'WordPress updates include security fixes, new features, and bug improvements. Staying current is critical for site security.', 'wpshadow' ); ?></p>
		<h4><?php esc_html_e( 'What to look for:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Green status: You\'re running the latest WordPress version', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Yellow status: An update is available', 'wpshadow' ); ?></li>
		</ul>
		<h4><?php esc_html_e( 'How to fix:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Visit Dashboard → Updates and click "Update Now"', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Create a backup before updating', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Test your site after updating to ensure nothing broke', 'wpshadow' ); ?></li>
		</ul>
	</div>

	<!-- Plugin Updates Section -->
	<div class="wps-p-20-rounded-4">
		<h2 id="plugin-updates"><?php esc_html_e( 'Plugin Updates', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'Outdated plugins are a common target for hackers. Keeping plugins updated protects your site and ensures compatibility.', 'wpshadow' ); ?></p>
		<h4><?php esc_html_e( 'What to look for:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Green status: All plugins are up to date', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Yellow status: Some plugins have updates available', 'wpshadow' ); ?></li>
		</ul>
		<h4><?php esc_html_e( 'How to fix:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Visit Plugins and click "Update" for each outdated plugin', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Or click "Update Plugins" to update all at once', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Test your site after updating plugins', 'wpshadow' ); ?></li>
		</ul>
	</div>

	<!-- Memory Limit Section -->
	<div class="wps-p-20-rounded-4">
		<h2 id="memory-limit"><?php esc_html_e( 'PHP Memory Limit', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'Memory is like your computer\'s RAM. WordPress needs enough to run plugins, process images, and handle requests. Too little causes timeouts and broken features.', 'wpshadow' ); ?></p>
		<h4><?php esc_html_e( 'What to look for:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Recommended: 256MB or more', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Minimum: 128MB', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Below 128MB: Problems with plugins and features', 'wpshadow' ); ?></li>
		</ul>
		<h4><?php esc_html_e( 'How to fix:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Ask your hosting provider to increase PHP memory limit', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'You can also add this to wp-config.php (ask your host first):', 'wpshadow' ); ?></li>
			<li class="wps-p-8">define( \'WP_MEMORY_LIMIT\', \'256M\' );</li>
		</ul>
	</div>

	<!-- Debug Mode Section -->
	<div class="wps-p-20-rounded-4">
		<h2 id="debug-mode"><?php esc_html_e( 'Debug Mode', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'Debug mode is useful for troubleshooting but should never be left on in production. It exposes sensitive information to potential attackers.', 'wpshadow' ); ?></p>
		<h4><?php esc_html_e( 'What to look for:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Green status: Debug mode is off (production mode)', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Red status: Debug mode is on - turn it off!', 'wpshadow' ); ?></li>
		</ul>
		<h4><?php esc_html_e( 'How to fix:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Open wp-config.php and change these lines to false:', 'wpshadow' ); ?></li>
			<li class="wps-p-8">
				<?php echo 'define( \'WP_DEBUG\', false );' . "\n" . 'define( \'WP_DEBUG_LOG\', false );'; ?>
			</li>
		</ul>
	</div>

	<!-- Object Cache Section -->
	<div class="wps-p-20-rounded-4">
		<h2 id="object-cache"><?php esc_html_e( 'Object Cache', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'Object caching stores frequently-used data in memory instead of the database. This dramatically speeds up your site.', 'wpshadow' ); ?></p>
		<h4><?php esc_html_e( 'What to look for:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Green status: Object cache is installed and working - your site is faster', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Yellow status: No object cache (not a problem, just slower)', 'wpshadow' ); ?></li>
		</ul>
		<h4><?php esc_html_e( 'How to fix:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Ask your hosting provider to enable Redis or Memcached', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Use a caching plugin like WP Super Cache or W3 Total Cache', 'wpshadow' ); ?></li>
		</ul>
	</div>

	<!-- Scheduled Events Section -->
	<div class="wps-p-20-rounded-4">
		<h2 id="scheduled-events"><?php esc_html_e( 'Scheduled Events (Cron)', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'WordPress uses scheduled tasks (called "cron") to power automatic updates, backups, email notifications, and other background work. If this breaks, these tasks stop running.', 'wpshadow' ); ?></p>
		<h4><?php esc_html_e( 'What to look for:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Green status: Scheduled tasks are working - updates and backups run automatically', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Red status: Scheduled tasks are broken - critical tasks won\'t run', 'wpshadow' ); ?></li>
		</ul>
		<h4><?php esc_html_e( 'How to fix:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Check that loopback requests are working (see above)', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Contact your hosting provider if loopback is working but cron still fails', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'As a workaround, ask your host to set up a real cron job that visits wp-cron.php', 'wpshadow' ); ?></li>
		</ul>
	</div>

	<!-- File Permissions Section -->
	<div class="wps-p-20-rounded-4">
		<h2 id="file-permissions"><?php esc_html_e( 'File Permissions & Integrity', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'File permissions control who can read and modify your WordPress files. Incorrect permissions allow hackers to modify your site or prevent WordPress updates.', 'wpshadow' ); ?></p>
		<h4><?php esc_html_e( 'What to look for:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Green status: Files have correct permissions - your site is secure', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Red status: File permissions are wrong - security risk', 'wpshadow' ); ?></li>
		</ul>
		<h4><?php esc_html_e( 'How to fix:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Contact your hosting provider to fix file permissions', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Typically directories should be 755 and files should be 644', 'wpshadow' ); ?></li>
		</ul>
	</div>

	<!-- Plugin Count Section -->
	<div class="wps-p-20-rounded-4">
		<h2 id="plugin-count"><?php esc_html_e( 'Number of Active Plugins', 'wpshadow' ); ?></h2>
		<p><?php esc_html_e( 'Each plugin adds code to your site. Too many plugins slow down your site, increase the risk of conflicts, and expand your security surface.', 'wpshadow' ); ?></p>
		<h4><?php esc_html_e( 'What to look for:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Best practice: Keep under 20 active plugins', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Warning: Over 40 plugins can cause noticeable slowness', 'wpshadow' ); ?></li>
		</ul>
		<h4><?php esc_html_e( 'How to fix:', 'wpshadow' ); ?></h4>
		<ul class="wps-help-list">
			<li><?php esc_html_e( 'Review your plugins and deactivate any you\'re not using', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Look for plugins that do similar things - pick the best one', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Look for duplicate functionality in your theme', 'wpshadow' ); ?></li>
		</ul>
	</div>

	<!-- Summary -->
	<div class="wps-p-15-rounded-4">
		<h3><?php esc_html_e( '✓ Quick Summary', 'wpshadow' ); ?></h3>
		<ul class="wps-m-10">
			<li><?php esc_html_e( 'Green checks = Your site is healthy', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Yellow checks = Recommended improvements', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Red checks = Security or functionality issues', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'When in doubt, contact your hosting provider', 'wpshadow' ); ?></li>
		</ul>
	</div>
</div>

<style>
	.wrap h2 {
		margin-top: 0;
		color: #1f2937;
	}

	.wrap h4 {
		margin-top: 12px;
		margin-bottom: 8px;
		color: #374151;
	}

	.wrap ul {
		margin-bottom: 0;
	}

	.wrap li {
		margin-bottom: 6px;
		color: #374151;
	}
</style>
