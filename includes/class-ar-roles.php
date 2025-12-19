<?php
/**
 * Role management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Roles {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // No hooks needed here, roles are created on activation
    }
    
    /**
     * Create custom roles
     */
    public static function create_roles() {
        
        // Portal Admin role
        add_role(
            'portal_admin',
            __('Admin Area Riservata', 'area-riservata'),
            array(
                'read' => true,
                'ar_manage_users' => true,
                'ar_manage_documents' => true,
                'ar_upload_documents' => true,
                'ar_view_audit_log' => true,
            )
        );
        
        // Portal User role
        add_role(
            'portal_user',
            __('Utente Area Riservata', 'area-riservata'),
            array(
                'read' => true,
                'ar_access_documents' => true,
            )
        );
    }
    
    /**
     * Remove custom roles
     */
    public static function remove_roles() {
        remove_role('portal_admin');
        remove_role('portal_user');
    }
    
    /**
     * Check if user is portal admin
     */
    public static function is_portal_admin($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return in_array('portal_admin', $user->roles);
    }
    
    /**
     * Check if user is portal user
     */
    public static function is_portal_user($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        
        return in_array('portal_user', $user->roles);
    }
}
