<?php
/**
 * Profile Page Simplifier Tool
 *
 * Lets administrators choose which user profile sections remain visible
 * for non-admin users.
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Form_Param_Helper;
use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access.
Tool_View_Base::verify_access( 'manage_options' );

// Enqueue assets.
Tool_View_Base::enqueue_assets( 'profile-sections' );

$defaults = array(
	'visual_preferences'    => true,
	'toolbar'               => true,
	'language'              => true,
	'contact_info'          => true,
	'about'                 => true,
	'profile_picture'       => true,
	'application_passwords' => true,
	'sessions'              => true,
);

$sections = array(
	'visual_preferences'    => array(
		'label'       => __( 'Visual Preferences', 'wpshadow' ),
		'description' => __( 'Controls like editor mode, syntax highlighting, admin color, and keyboard shortcuts.', 'wpshadow' ),
	),
	'toolbar'               => array(
		'label'       => __( 'Toolbar Setting', 'wpshadow' ),
		'description' => __( 'The option that shows the admin toolbar while browsing your site.', 'wpshadow' ),
	),
	'language'              => array(
		'label'       => __( 'Language Setting', 'wpshadow' ),
		'description' => __( 'The profile-level language chooser for WordPress admin.', 'wpshadow' ),
	),
	'contact_info'          => array(
		'label'       => __( 'Contact Information', 'wpshadow' ),
		'description' => __( 'Profile email and contact URL fields.', 'wpshadow' ),
	),
	'about'                 => array(
		'label'       => __( 'About / Bio', 'wpshadow' ),
		'description' => __( 'The biographical info box on the profile page.', 'wpshadow' ),
	),
	'profile_picture'       => array(
		'label'       => __( 'Profile Picture', 'wpshadow' ),
		'description' => __( 'The profile picture section (avatar source information).', 'wpshadow' ),
	),
	'application_passwords' => array(
		'label'       => __( 'Application Passwords', 'wpshadow' ),
		'description' => __( 'The section for creating app-specific login passwords.', 'wpshadow' ),
	),
	'sessions'              => array(
		'label'       => __( 'Sessions', 'wpshadow' ),
		'description' => __( 'The section for signing out other active sessions.', 'wpshadow' ),
	),
);

$visibility = get_option( 'wpshadow_profile_sections_visibility', $defaults );
if ( ! is_array( $visibility ) ) {
	$visibility = $defaults;
}

$saved_message = '';
if ( Form_Param_Helper::has_post( 'save_profile_sections' ) && check_admin_referer( 'wpshadow_profile_sections_save', 'wpshadow_profile_sections_nonce' ) ) {
	$new_visibility = array();

	foreach ( array_keys( $sections ) as $section_key ) {
		$new_visibility[ $section_key ] = '1' === Form_Param_Helper::post( 'wpshadow_profile_section_' . $section_key, 'key', '0' );
	}

	update_option( 'wpshadow_profile_sections_visibility', $new_visibility );
	$visibility    = wp_parse_args( $new_visibility, $defaults );
	$saved_message = '<div class="notice notice-success"><p>' . esc_html__( 'Profile section visibility saved.', 'wpshadow' ) . '</p></div>';
}

Tool_View_Base::render_header(
	__( 'Profile Page Simplifier', 'wpshadow' ),
	__( 'Choose which profile sections non-admin users see, so the profile page feels simpler and easier to use.', 'wpshadow' )
);
?>

	<?php echo wp_kses_post( $saved_message ); ?>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Section Visibility', 'wpshadow' ); ?></h3>
		<p><?php esc_html_e( 'Turn sections on or off for non-admin users. Administrators will always keep full access.', 'wpshadow' ); ?></p>

		<form method="post" action="">
			<?php wp_nonce_field( 'wpshadow_profile_sections_save', 'wpshadow_profile_sections_nonce' ); ?>

			<div class="wps-form-stack">
				<?php foreach ( $sections as $key => $section ) : ?>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
					echo \WPShadow\Helpers\Form_Controls::toggle_switch(
						array(
							'id'          => 'wpshadow_profile_section_' . $key,
							'name'        => 'wpshadow_profile_section_' . $key,
							'label'       => $section['label'],
							'helper_text' => $section['description'],
							'checked'     => (bool) ( $visibility[ $key ] ?? true ),
						)
					);
					?>
				<?php endforeach; ?>
			</div>

			<p class="submit">
				<button type="submit" class="wps-btn wps-btn-primary">
					<?php esc_html_e( 'Save Profile Visibility', 'wpshadow' ); ?>
				</button>
			</p>
			<input type="hidden" name="save_profile_sections" value="1" />
		</form>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'What This Changes', 'wpshadow' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'These settings apply on profile pages for non-admin users.', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Administrators still see everything so they can troubleshoot and support users.', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'You can switch any section back on at any time.', 'wpshadow' ); ?></li>
		</ul>
	</div>
</div>

<?php Tool_View_Base::render_footer(); ?>
