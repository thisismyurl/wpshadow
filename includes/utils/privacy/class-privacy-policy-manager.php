<?php
/**
 * Privacy Policy Manager
 *
 * Manages privacy policy storage, versioning, and display.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Privacy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Manager
 */
class Privacy_Policy_Manager {
	/**
	 * Get current privacy policy.
	 *
	 * @return array Privacy policy data.
	 */
	public static function get_policy() {
		return array(
			'version'        => '1.0',
			'updated_date'   => current_time( 'mysql' ),
			'effective_date' => current_time( 'mysql' ),
			'sections'       => self::get_policy_sections(),
		);
	}

	/**
	 * Get privacy policy sections.
	 *
	 * @return array Sections.
	 */
	private static function get_policy_sections() {
		return array(
			'overview'        => array(
				'title'   => __( 'Privacy Overview', 'wpshadow' ),
				'content' => __( 'WPShadow is committed to protecting your privacy. This privacy policy explains our data practices.', 'wpshadow' ),
			),
			'what-we-collect' => array(
				'title'   => __( 'What Information We Collect', 'wpshadow' ),
				'content' => self::get_what_we_collect_section(),
			),
			'how-we-use'      => array(
				'title'   => __( 'How We Use Your Information', 'wpshadow' ),
				'content' => self::get_how_we_use_section(),
			),
			'data-retention'  => array(
				'title'   => __( 'Data Retention', 'wpshadow' ),
				'content' => __( 'We retain data only as long as necessary. You can delete your data at any time from settings.', 'wpshadow' ),
			),
			'your-rights'     => array(
				'title'   => __( 'Your Rights', 'wpshadow' ),
				'content' => self::get_your_rights_section(),
			),
			'contact'         => array(
				'title'   => __( 'Contact Us', 'wpshadow' ),
				'content' => __( 'Questions about privacy? Please contact your site administrator.', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get "what we collect" section content.
	 *
	 * @return string HTML content.
	 */
	private static function get_what_we_collect_section() {
		return '
			<p>' . __( 'WPShadow collects the following information:', 'wpshadow' ) . '</p>
			<ul>
				<li><strong>' . __( 'Diagnostic Results', 'wpshadow' ) . ':</strong> ' . __( 'Which diagnostics were run and when.', 'wpshadow' ) . '</li>
				<li><strong>' . __( 'Treatment Applications', 'wpshadow' ) . ':</strong> ' . __( 'Which automatic fixes were applied.', 'wpshadow' ) . '</li>
				<li><strong>' . __( 'User Preferences', 'wpshadow' ) . ':</strong> ' . __( 'Dashboard settings, hidden items, theme preference.', 'wpshadow' ) . '</li>
				<li><strong>' . __( 'Training Progress', 'wpshadow' ) . ':</strong> ' . __( 'Completed courses and earned badges.', 'wpshadow' ) . '</li>
			</ul>
			<p><strong>' . __( 'What We Do NOT Collect:', 'wpshadow' ) . '</strong></p>
			<ul>
				<li>' . __( 'Your IP address or geographic location', 'wpshadow' ) . '</li>
				<li>' . __( 'Your actual site content or data', 'wpshadow' ) . '</li>
				<li>' . __( 'User information from your WordPress site', 'wpshadow' ) . '</li>
				<li>' . __( 'Payment or financial information', 'wpshadow' ) . '</li>
			</ul>
		';
	}

	/**
	 * Get "how we use" section content.
	 *
	 * @return string HTML content.
	 */
	private static function get_how_we_use_section() {
		return '
			<p>' . __( 'We use your information for:', 'wpshadow' ) . '</p>
			<ul>
				<li>' . __( 'Tracking improvements to your site health', 'wpshadow' ) . '</li>
				<li>' . __( 'Providing personalized recommendations', 'wpshadow' ) . '</li>
				<li>' . __( 'Improving the WPShadow plugin itself', 'wpshadow' ) . '</li>
				<li>' . __( 'Personalizing your dashboard experience', 'wpshadow' ) . '</li>
				<li>' . __( 'Showing you your training progress', 'wpshadow' ) . '</li>
			</ul>
			<p>' . __( 'We do NOT:', 'wpshadow' ) . '</p>
			<ul>
				<li>' . __( 'Share your data with third parties', 'wpshadow' ) . '</li>
				<li>' . __( 'Sell your data', 'wpshadow' ) . '</li>
				<li>' . __( 'Use your data for marketing purposes', 'wpshadow' ) . '</li>
				<li>' . __( 'Track you across other websites', 'wpshadow' ) . '</li>
			</ul>
		';
	}

	/**
	 * Get "your rights" section content.
	 *
	 * @return string HTML content.
	 */
	private static function get_your_rights_section() {
		return '
			<p>' . __( 'You have the right to:', 'wpshadow' ) . '</p>
			<ul>
				<li><strong>' . __( 'Access Your Data', 'wpshadow' ) . ':</strong> ' . __( 'View all data we have about you.', 'wpshadow' ) . '</li>
				<li><strong>' . __( 'Export Your Data', 'wpshadow' ) . ':</strong> ' . __( 'Download your data in a standard format (JSON).', 'wpshadow' ) . '</li>
				<li><strong>' . __( 'Delete Your Data', 'wpshadow' ) . ':</strong> ' . __( 'Request deletion of all your personal data.', 'wpshadow' ) . '</li>
				<li><strong>' . __( 'Change Your Preferences', 'wpshadow' ) . ':</strong> ' . __( 'Opt out of data collection at any time.', 'wpshadow' ) . '</li>
				<li><strong>' . __( 'Right to Complaint', 'wpshadow' ) . ':</strong> ' . __( 'File a complaint with a data protection authority.', 'wpshadow' ) . '</li>
			</ul>
		';
	}

	/**
	 * Get privacy policy as HTML.
	 *
	 * @return string HTML.
	 */
	public static function get_policy_html() {
		$policy = self::get_policy();
		$html   = '<div class="wpshadow-privacy-policy">';
		$html  .= '<h1>' . __( 'WPShadow Privacy Policy', 'wpshadow' ) . '</h1>';
		$html  .= '<p class="meta">' . sprintf( __( 'Last updated: %s', 'wpshadow' ), wp_date( 'F j, Y', strtotime( $policy['updated_date'] ) ) ) . '</p>';

		foreach ( $policy['sections'] as $section_id => $section ) {
			$html .= '<h2 id="' . esc_attr( $section_id ) . '">' . esc_html( $section['title'] ) . '</h2>';
			$html .= '<div class="section-content">' . wp_kses_post( $section['content'] ) . '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Store policy version.
	 *
	 * @param string $version Version number.
	 * @param string $content Policy content.
	 * @return bool Success.
	 */
	public static function store_version( $version, $content ) {
		$versions = get_option( 'wpshadow_privacy_policy_versions', array() );

		$versions[ $version ] = array(
			'content'     => $content,
			'stored_date' => current_time( 'mysql' ),
		);

		return update_option( 'wpshadow_privacy_policy_versions', $versions );
	}

	/**
	 * Get policy change history.
	 *
	 * @return array Version history.
	 */
	public static function get_version_history() {
		return get_option( 'wpshadow_privacy_policy_versions', array() );
	}

	/**
	 * Notify admins of policy change.
	 *
	 * @param string $message Notification message.
	 * @return void
	 */
	public static function notify_policy_change( $message ) {
		$admins = get_users( array( 'role' => 'administrator' ) );

		foreach ( $admins as $admin ) {
			add_user_meta( $admin->ID, 'wpshadow_privacy_policy_notification', $message );
		}
	}
}
