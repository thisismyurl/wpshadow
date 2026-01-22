<?php
declare(strict_types=1);
/**
 * SSL Certificate Expiration Warning Diagnostic
 *
 * @package WPShadow
 * @subpackage DiagnosticsFuture
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * SSL Certificate Expiration Warning
 * 
 * Philosophy Compliance:
 * - ✅ Free to run (Commandment #2: Free as possible)
 * - ✅ Monetize fixes via Free + Guardian module
 * - ✅ Links to KB/Training (Commandments #5, #6)
 * - ✅ Shows KPI value (Commandment #9)
 * - ✅ Talk-worthy (Commandment #11): "Your SSL certificate expires in 7 days"
 * 
 * @priority 1
 */
class Diagnostic_SSL_Expiration extends Diagnostic_Base {
    
    /**
     * The diagnostic slug/ID
     *
     * @var string
     */
    protected static $slug = 'ssl-expiration';
    
    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'SSL Certificate Expiration Warning';
    
    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Shows countdown to SSL certificate expiration with urgency levels.';
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Finding data or null if no issue
     */
    public static function check(): ?array {
        // Only check if site uses SSL
        if (!is_ssl()) {
            return null;
        }
        
        $site_url = get_site_url();
        $host = parse_url($site_url, PHP_URL_HOST);
        
        if (empty($host)) {
            return null;
        }
        
        // Check cached SSL expiration
        $cache_key = 'wpshadow_ssl_expiry_' . md5($host);
        $expiry_data = get_transient($cache_key);
        
        if ($expiry_data === false) {
            // Try to get SSL certificate expiration
            $context = stream_context_create(array(
                'ssl' => array(
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ),
            ));
            
            $stream = @stream_socket_client(
                'ssl://' . $host . ':443',
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );
            
            if ($stream) {
                $params = stream_context_get_params($stream);
                $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
                
                if (isset($cert['validTo_time_t'])) {
                    $expiry_data = $cert['validTo_time_t'];
                    set_transient($cache_key, $expiry_data, 12 * HOUR_IN_SECONDS);
                }
                
                fclose($stream);
            }
            
            if (!$expiry_data) {
                return null;
            }
        }
        
        // Check if expiring within 30 days
        $days_until_expiry = floor(($expiry_data - time()) / DAY_IN_SECONDS);
        
        if ($days_until_expiry > 30) {
            return null;
        }
        
        return array(
            'id'           => static::$slug,
            'title'        => static::$title,
            'description'  => sprintf(
                'Your SSL certificate expires in %d days!',
                $days_until_expiry
            ),
            'severity'     => $days_until_expiry < 7 ? 'critical' : 'high',
            'category'     => 'security',
            'kb_link'      => 'https://wpshadow.com/kb/ssl-expiration/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=ssl-expiration',
            'training_link' => 'https://wpshadow.com/training/ssl-expiration/',
            'auto_fixable' => false,
            'threat_level' => 85,
            'module'       => 'Free + Guardian',
            'priority'     => 1,
        );
    }

}
