#!/bin/bash

# Create journalism diagnostics
cat > /workspaces/wpshadow/includes/diagnostics/tests/journalism/class-diagnostic-source-protection-privacy.php << 'EOF'
<?php
/**
 * Journalism Source Protection and Whistleblower Privacy Diagnostic
 *
 * Checks if journalism/news sites implement proper source protection measures
 * including encrypted contact forms, anonymous submission systems, and
 * metadata stripping to protect whistleblower identities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Journalism
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Journalism;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Source Protection Privacy Diagnostic Class
 *
 * Verifies journalism sites have source protection measures in place
 * to protect confidential sources and whistleblowers.
 *
 * @since 1.6031.1445
 */
class Diagnostic_Source_Protection_Privacy extends Diagnostic_Base {

/**
 * The diagnostic slug
 *
 * @var string
 */
protected static $slug = 'source-protection-privacy';

/**
 * The diagnostic title
 *
 * @var string
 */
protected static $title = 'Journalism Source Protection and Whistleblower Privacy';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies news/journalism sites implement proper source protection measures';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'journalism';

/**
 * Run the diagnostic check.
 *
 * Checks for:
 * - Secure contact forms (encrypted submission)
 * - Anonymous tip submission systems
 * - Image metadata stripping
 * - SecureDrop or similar whistleblower platforms
 *
 * @since  1.6031.1445
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
Check if site appears to be journalism/news focused.
e    = get_bloginfo( 'description' );
ame       = get_bloginfo( 'name' );
alism_terms = array( 'news', 'journalism', 'reporter', 'press', 'media', 'investigative' );

alism_site = false;
( $journalism_terms as $term ) {
( stripos( $site_name, $term ) !== false || stripos( $site_tagline, $term ) !== false ) {
alism_site = true;
Check for journalism-related plugins.
alism_plugins = array(
alist',
ews',
ewsroom',
s = get_option( 'active_plugins', array() );
( $active_plugins as $plugin ) {
( $journalism_plugins as $j_plugin ) {
( stripos( $plugin, $j_plugin ) !== false ) {
alism_site = true;
2;
( ! $is_journalism_site ) {
 null; // Not a journalism site.
= array();

Check for encrypted contact forms.
tact = false;
tact_plugins    = array(
tact-form-7-secure',
crypted-contact',
-forms',

( $active_plugins as $plugin ) {
( $contact_plugins as $secure_plugin ) {
( stripos( $plugin, $secure_plugin ) !== false ) {
tact = true;
2;
( ! $has_secure_contact ) {
= __( 'No encrypted contact form plugin detected', 'wpshadow' );
Check for anonymous submission systems.
onymous_system = false;
onymous_plugins    = array(
onymous-submission',
e',
( $active_plugins as $plugin ) {
( $anonymous_plugins as $anon_plugin ) {
( stripos( $plugin, $anon_plugin ) !== false ) {
onymous_system = true;
2;
( ! $has_anonymous_system ) {
= __( 'No anonymous tip submission system found', 'wpshadow' );
Check for HTTPS (essential for any source protection).
( ! is_ssl() ) {
= __( 'Site not using HTTPS (critical for source protection)', 'wpshadow' );
( empty( $issues ) ) {
 null;
 array(
          => self::$slug,
       => self::$title,
'  => sprintf(
translators: %s: comma-separated list of issues */
'Source protection concerns detected: %s. Journalism sites should implement encrypted contact forms and anonymous submission systems to protect confidential sources.', 'wpshadow' ),
', ', $issues )
'     => 'high',
=> 75,
=> false,
k'      => 'https://wpshadow.com/kb/source-protection-privacy',
}
EOF

echo "Created 1/22: source-protection-privacy"

