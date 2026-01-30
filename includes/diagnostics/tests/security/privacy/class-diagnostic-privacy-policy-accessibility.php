<?php
/**
 * Privacy Policy Accessibility and Content Diagnostic
 *
 * Ensure privacy policy is accessible, comprehensive, and GDPR-compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since      1.6028.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Privacy Policy Accessibility and Content Diagnostic Class
 *
 * GDPR compliance diagnostic for ensure privacy policy is accessible, comprehensive, and gdpr-compliant.
 *
 * @since 1.6028.1430
 */
class Diagnostic_PrivacyPolicyAccessibility extends Diagnostic_Base {

/**
 * The diagnostic slug
 *
 * @var string
 */
protected static $slug = 'privacy-policy-accessibility';

/**
 * The diagnostic title
 *
 * @var string
 */
protected static $title = 'Privacy Policy Accessibility and Content';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Ensure privacy policy is accessible, comprehensive, and GDPR-compliant';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'privacy-gdpr';

/**
 * Run the diagnostic check.
 *
 * @since  1.6028.1430
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {

(int) get_option( 'wp_page_for_privacy_policy' );
$privacy_page_id ) {
 self::create_finding( 'no_privacy_page' );
get_post( $privacy_page_id );
$privacy_page || 'publish' !== $privacy_page->post_status ) {
 self::create_finding( 'privacy_page_not_published' );
policy is linked in footer.
u_locations = get_nav_menu_locations();
ked = false;
u_locations as $location => $menu_id ) {
u_items = wp_get_nav_menu_items( $menu_id );
u_items ) {
u_items as $item ) {
t) $item->object_id === $privacy_page_id ) {
ked = true;
Check content length (should be comprehensive).
tent_length = str_word_count( wp_strip_all_tags( $privacy_page->post_content ) );
tent_length < 500 ) {
 self::create_finding( 'privacy_policy_too_short', array( 'word_count' => $content_length ) );
$privacy_linked ) {

 array(
        => self::$slug,
     => self::$title,
'  => __( 'Ensure privacy policy is accessible, comprehensive, and GDPR-compliant', 'wpshadow' ),
   => 'critical',
=> false,
k'      => 'https://wpshadow.com/kb/privacy-policy-accessibility',
   => array(
ding'        => __( 'GDPR compliance issue detected', 'wpshadow' ),
      => __( 'GDPR Article 13 requires transparent privacy information. Missing/inadequate policy = €20M fine.', 'wpshadow' ),
dation' => __( 'Implement GDPR compliance measures', 'wpshadow' ),
_free'  => array(
'Free Solution', 'wpshadow' ),
', ', __('Use WordPress privacy policy template, configure Privacy page, link in footer menu', 'wpshadow' ) ),
_premium' => array(
'Premium Solution', 'wpshadow' ),
', ', __( 'Termly.io privacy policy generator, automatic updates, multi-language support', 'wpshadow' ) ),
_advanced' => array(
'Advanced Solution', 'wpshadow' ),
', ', __( 'Legal review by GDPR lawyer, custom privacy portal, real-time compliance monitoring', 'wpshadow' ) ),
 null;
}
}
