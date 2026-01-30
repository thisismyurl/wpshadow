<?php
/**
 * GDPR Cookie Consent Implementation Diagnostic
 *
 * Verify proper cookie consent banner before any non-essential cookies.
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
 * GDPR Cookie Consent Implementation Diagnostic Class
 *
 * GDPR compliance diagnostic for verify proper cookie consent banner before any non-essential cookies.
 *
 * @since 1.6028.1430
 */
class Diagnostic_GdprCookieConsentImplementation extends Diagnostic_Base {

/**
 * The diagnostic slug
 *
 * @var string
 */
protected static $slug = 'gdpr-cookie-consent-implementation';

/**
 * The diagnostic title
 *
 * @var string
 */
protected static $title = 'GDPR Cookie Consent Implementation';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verify proper cookie consent banner before any non-essential cookies';

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

cookie consent plugins.
sent_plugins = array(
z-gdpr/complianz-gdpr.php',
fo/cookie-law-info.php',
sent/gdpr-cookie-consent.php',
sent_plugin = false;
sent_plugins as $plugin ) {
_active( $plugin ) ) {
sent_plugin = true;
for common cookie consent scripts in header.
se     = wp_remote_get( $homepage_url );
$response ) ) {
 null;
wp_remote_retrieve_body( $response );
sent_banner = (
'cookie consent' ) !== false ||
'cookie-consent' ) !== false ||
'gdpr-cookie' ) !== false
$has_consent_plugin && ! $has_consent_banner ) {

 array(
        => self::$slug,
     => self::$title,
'  => __( 'Verify proper cookie consent banner before any non-essential cookies', 'wpshadow' ),
   => 'critical',
=> false,
k'      => 'https://wpshadow.com/kb/gdpr-cookie-consent-implementation',
   => array(
ding'        => __( 'GDPR compliance issue detected', 'wpshadow' ),
      => __( 'GDPR Article 7 requires explicit consent. Fines up to €20M or 4% revenue. Pre-consent cookies = immediate violation.', 'wpshadow' ),
dation' => __( 'Implement GDPR compliance measures', 'wpshadow' ),
_free'  => array(
'Free Solution', 'wpshadow' ),
', ', __('Install Complianz GDPR plugin, configure cookie categories, enable granular consent', 'wpshadow' ) ),
_premium' => array(
'Premium Solution', 'wpshadow' ),
', ', __( 'Cookiebot Pro subscription, advanced cookie scanning, automatic blocking', 'wpshadow' ) ),
_advanced' => array(
'Advanced Solution', 'wpshadow' ),
', ', __( 'Custom consent management platform, server-side consent API, consent refresh automation', 'wpshadow' ) ),
 null;
}
}
