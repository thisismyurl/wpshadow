<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: User Role Configuration Review
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_User_Role_Management extends Diagnostic_Base {
    protected static $slug = 'user-role-management';
    protected static $title = 'User Role Configuration Review';
    protected static $description = 'Audits custom roles and capabilities.';


    public static function check(): ?array {
        global $wp_roles;
        
        // Get all roles
        $all_roles = $wp_roles->roles;
        $default_roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
        
        // Find custom roles
        $custom_roles = array();
        foreach ($all_roles as $role_key => $role_info) {
            if (!in_array($role_key, $default_roles)) {
                $custom_roles[] = $role_key;
            }
        }
        
        // Check for users with administrator capability
        $admins = get_users(array('role' => 'administrator'));
        
        // Flag if too many admins or suspicious custom roles
        $issues = array();
        
        if (count($admins) > 5) {
            $issues[] = sprintf('%d administrator accounts (consider reducing)', count($admins));
        }
        
        if (count($custom_roles) > 0) {
            $issues[] = sprintf('%d custom role(s): %s', count($custom_roles), implode(', ', array_slice($custom_roles, 0, 3)));
        }
        
        if (empty($issues)) {
            return null;
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'User role review recommended: ' . implode('; ', $issues),
            'severity'      => 'low',
            'category'      => 'security',
            'kb_link'       => 'https://wpshadow.com/kb/user-role-management/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=user-role-management',
            'training_link' => 'https://wpshadow.com/training/user-role-management/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Security',
            'priority'      => 1,
        );
    }
}
