<?php
/**
 * Diagnostic: Email Configuration
 *
 * @since 1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Diagnostic_EmailConfiguration Class
 */
class Diagnostic_EmailConfiguration extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'email-configuration';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Email Configuration';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Detect if WordPress email is properly configured via SMTP or local mail';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'email';

    /**
     * Run the diagnostic check
     *
     * @since 1.2601.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        $smtp_plugins = array(
            'wp-mail-smtp/wp_mail_smtp.php'               => 'WP Mail SMTP',
            'post-smtp/postman-smtp.php'                  => 'Post SMTP',
            'easy-wp-smtp/easy-wp-smtp.php'               => 'Easy WP SMTP',
            'wp-ses/wp-ses.php'                           => 'WP SES',
            'mailgun/mailgun.php'                         => 'Mailgun',
            'sendgrid-email-delivery-simplified/wpsendgrid.php' => 'SendGrid',
        );

        $active_smtp_plugin = null;
        
        // Check for active SMTP plugins
        foreach ( $smtp_plugins as $plugin_path => $plugin_name ) {
            if ( is_plugin_active( $plugin_path ) ) {
                $active_smtp_plugin = $plugin_name;
                break;
            }
        }

        // Check for custom SMTP configuration via hooks
        $has_phpmailer_init = has_action( 'phpmailer_init' );
        $has_wp_mail_from = has_filter( 'wp_mail_from' );

        // If SMTP is configured, no issue
        if ( $active_smtp_plugin || $has_phpmailer_init ) {
            return null;
        }

        // Check if email sending might be problematic
        $is_localhost = in_array( $_SERVER['SERVER_ADDR'] ?? '', array( '127.0.0.1', '::1' ), true );
        $admin_email = get_option( 'admin_email' );
        $site_domain = wp_parse_url( home_url(), PHP_URL_HOST );

        // Try to detect if mail() is likely to fail
        $mail_function_exists = function_exists( 'mail' );
        
        $threat_level = 45; // Default medium
        
        if ( ! $mail_function_exists ) {
            $threat_level = 60; // Higher if mail() doesn't exist
        } elseif ( $is_localhost ) {
            $threat_level = 50; // Higher on localhost
        }

        return array(
            'id'            => self::$slug,
            'title'         => self::$title,
            'description'   => __( 'WordPress is using the default mail() function which may not work reliably on your server. Emails for password resets, notifications, and contact forms may fail to send.', 'wpshadow' ),
            'severity'      => $threat_level >= 50 ? 'high' : 'medium',
            'threat_level'  => $threat_level,
            'auto_fixable'  => false,
            'kb_link'       => 'https://wpshadow.com/kb/email-configuration',
            'manual_steps'  => array(
                __( 'Install and configure WP Mail SMTP plugin (free)', 'wpshadow' ),
                __( 'Use a free SMTP service like Gmail, SendGrid, or Mailgun', 'wpshadow' ),
                __( 'Test email delivery after configuration', 'wpshadow' ),
                __( 'Check spam folder if emails still don\'t arrive', 'wpshadow' ),
            ),
            'impact'        => array(
                'functionality' => __( 'Password reset emails may not send', 'wpshadow' ),
                'security'      => __( 'Users cannot recover their accounts', 'wpshadow' ),
                'communication' => __( 'Contact forms and notifications will fail', 'wpshadow' ),
            ),
            'evidence'      => array(
                'active_smtp_plugin'   => $active_smtp_plugin,
                'has_phpmailer_init'   => $has_phpmailer_init,
                'has_wp_mail_from'     => $has_wp_mail_from,
                'mail_function_exists' => $mail_function_exists,
                'is_localhost'         => $is_localhost,
                'admin_email'          => $admin_email,
                'site_domain'          => $site_domain,
            ),
        );
    }
}
