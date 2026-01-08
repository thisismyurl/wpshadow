<?php
/**
 * Guided Setup Wizard View
 *
 * Shows on first plugin activation to guide installation of recommended module stack.
 *
 * @package TIMU_CORE_SUPPORT
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$recommended_modules = array(
	'media-support-thisismyurl' => array(
		'name'        => 'Media Support',
		'description' => 'Provides shared media optimization and processing infrastructure. Required by other modules.',
		'icon'        => '📦',
	),
	'vault-support-thisismyurl' => array(
		'name'        => 'Vault Support',
		'description' => 'Secure original storage with encryption, journaling, and cloud offload. Enables The Vault features.',
		'icon'        => '🔒',
	),
	'image-support-thisismyurl' => array(
		'name'        => 'Image Support',
		'description' => 'Hub for image format support and advanced processing. Enables format conversion and optimization.',
		'icon'        => '🖼️',
	),
);

$nonce       = wp_create_nonce( 'timu_setup_wizard' );
$install_url = add_query_arg(
	array(
		'action'  => 'timu_setup_install_all',
		'nonce'   => $nonce,
		'redirect' => rawurlencode( admin_url( 'admin.php?page=timu-core-dashboard' ) ),
	),
	admin_url( 'admin-post.php' )
);
?>
<div class="timu-setup-wizard" style="max-width: 800px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" role="main" aria-label="<?php esc_attr_e( 'Core Support Setup Wizard', 'core-support-thisismyurl' ); ?>">

	<h1><?php esc_html_e( 'Welcome to Core Support', 'core-support-thisismyurl' ); ?></h1>
	<p><?php esc_html_e( 'The Hub of the thisismyurl.com Support Suite. Let\'s set up the recommended modules to get you started.', 'core-support-thisismyurl' ); ?></p>

	<h2><?php esc_html_e( 'Recommended Module Stack', 'core-support-thisismyurl' ); ?></h2>

	<div style="display: grid; gap: 20px; margin: 20px 0;" role="region" aria-label="<?php esc_attr_e( 'Recommended modules list', 'core-support-thisismyurl' ); ?>">
		<?php foreach ( $recommended_modules as $slug => $module ) : ?>
			<div style="padding: 15px; background: #f5f5f5; border-left: 4px solid #2271b1; border-radius: 4px;">
				<p style="margin: 0 0 10px 0; font-size: 18px; font-weight: bold;">
					<?php echo esc_html( $module['icon'] ); ?> <?php echo esc_html( $module['name'] ); ?>
				</p>
				<p style="margin: 0; color: #666; font-size: 14px;">
					<?php echo esc_html( $module['description'] ); ?>
				</p>
			</div>
		<?php endforeach; ?>
	</div>

	<div style="margin: 30px 0; padding: 15px; background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 4px;" role="complementary" aria-label="<?php esc_attr_e( 'About this stack', 'core-support-thisismyurl' ); ?>">
		<p style="margin: 0;">
			<strong><?php esc_html_e( 'About this stack:', 'core-support-thisismyurl' ); ?></strong><br>
			<?php esc_html_e( 'These modules work together to provide media optimization, secure vault storage, and advanced format support. You can install them now or skip and configure later from the settings.', 'core-support-thisismyurl' ); ?>
		</p>
	</div>

	<div style="display: flex; gap: 10px; margin-top: 30px;" role="group" aria-label="<?php esc_attr_e( 'Setup wizard actions', 'core-support-thisismyurl' ); ?>">
		<a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary" style="padding: 10px 20px; font-size: 16px;" aria-label="<?php esc_attr_e( 'Install and activate all recommended modules', 'core-support-thisismyurl' ); ?>">
			<?php esc_html_e( 'Install & Activate All', 'core-support-thisismyurl' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=timu-core-dashboard' ) ); ?>" class="button" style="padding: 10px 20px; font-size: 16px;" aria-label="<?php esc_attr_e( 'Skip setup wizard and go to dashboard', 'core-support-thisismyurl' ); ?>">
			<?php esc_html_e( 'Skip for Now', 'core-support-thisismyurl' ); ?>
		</a>
	</div>

	<p style="margin-top: 30px; color: #999; font-size: 12px;">
		<?php esc_html_e( 'You can install additional modules or change settings at any time from the Core Support dashboard.', 'core-support-thisismyurl' ); ?>
	</p>

</div>
