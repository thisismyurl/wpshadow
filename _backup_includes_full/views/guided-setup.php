<?php
/**
 * Guided Setup Wizard View
 *
 * Shows on first plugin activation to guide installation of recommended module stack.
 *
 * @package WPShadow
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$recommended_modules = array(
	'media-wpshadow' => array(
		'name'        => 'Media Support',
		'description' => 'Provides shared media optimization and processing infrastructure. Required by other modules.',
		'icon'        => '📦',
	),
	'vault-wpshadow' => array(
		'name'        => 'Vault Support',
		'description' => 'Secure original storage with encryption, journaling, and cloud offload. Enables The Vault features.',
		'icon'        => '🔒',
	),
	'image-wpshadow' => array(
		'name'        => 'Image Support',
		'description' => 'Hub for image format support and advanced processing. Enables format conversion and optimization.',
		'icon'        => '🖼️',
	),
);

$nonce       = wp_create_nonce( 'wpshadow_setup_wizard' );
$install_url = add_query_arg(
	array(
		'action'   => 'wpshadow_setup_install_all',
		'nonce'    => $nonce,
		'redirect' => rawurlencode( admin_url( 'admin.php?page=wps-core-dashboard' ) ),
	),
	admin_url( 'admin-post.php' )
);
?>
<div class="wps-setup-wizard" style="max-width: 800px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" role="main" aria-label="<?php esc_attr_e( 'Core Support Setup Wizard', 'wpshadow' ); ?>">

	<h1><?php esc_html_e( 'Welcome to Core Support', 'wpshadow' ); ?></h1>
	<p><?php esc_html_e( 'The Hub of the wpshadow.com Support Suite. Let\'s set up the recommended modules to get you started.', 'wpshadow' ); ?></p>

	<h2><?php esc_html_e( 'Recommended Module Stack', 'wpshadow' ); ?></h2>

	<div style="display: grid; gap: 20px; margin: 20px 0;" role="region" aria-label="<?php esc_attr_e( 'Recommended modules list', 'wpshadow' ); ?>">
		<?php foreach ( $recommended_modules as $slug => $module ) : ?>
			<div style="padding: 15px; background: #f5f5f5; border-left: 4px solid #123456; border-radius: 4px;">
				<p style="margin: 0 0 10px 0; font-size: 18px; font-weight: bold;">
					<?php echo esc_html( $module['icon'] ); ?> <?php echo esc_html( $module['name'] ); ?>
				</p>
				<p style="margin: 0; color: #666; font-size: 14px;">
					<?php echo esc_html( $module['description'] ); ?>
				</p>
			</div>
		<?php endforeach; ?>
	</div>

	<div style="margin: 30px 0; padding: 15px; background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 4px;" role="complementary" aria-label="<?php esc_attr_e( 'About this stack', 'wpshadow' ); ?>">
		<p style="margin: 0;">
			<strong><?php esc_html_e( 'About this stack:', 'wpshadow' ); ?></strong><br>
			<?php esc_html_e( 'These modules work together to provide media optimization, secure vault storage, and advanced format support. You can install them now or skip and configure later from the settings.', 'wpshadow' ); ?>
		</p>
	</div>

	<div style="display: flex; gap: 10px; margin-top: 30px;" role="group" aria-label="<?php esc_attr_e( 'Setup wizard actions', 'wpshadow' ); ?>">
		<a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary" style="padding: 10px 20px; font-size: 16px;" aria-label="<?php esc_attr_e( 'Install and activate all recommended modules', 'wpshadow' ); ?>">
			<?php esc_html_e( 'Install & Activate All', 'wpshadow' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-core-dashboard' ) ); ?>" class="button" style="padding: 10px 20px; font-size: 16px;" aria-label="<?php esc_attr_e( 'Skip setup wizard and go to dashboard', 'wpshadow' ); ?>">
			<?php esc_html_e( 'Skip for Now', 'wpshadow' ); ?>
		</a>
	</div>

	<p style="margin-top: 30px; color: #999; font-size: 12px;">
		<?php esc_html_e( 'You can install additional modules or change settings at any time from the Core Support dashboard.', 'wpshadow' ); ?>
	</p>

</div>


