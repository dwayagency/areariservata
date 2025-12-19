<?php
/**
 * Security layer
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Security {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Block wp-admin for portal admin
        add_action('admin_init', array($this, 'block_wp_admin_access'));
        
        // Block pending users from accessing documents
        add_action('template_redirect', array($this, 'check_user_access'));
    }
    
    /**
     * Block wp-admin access for portal admin
     */
    public function block_wp_admin_access() {
        $user = wp_get_current_user();
        
        // Allow AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        
        // Check if user is portal admin (but not WordPress admin)
        if (in_array('portal_admin', $user->roles) && !in_array('administrator', $user->roles)) {
            // Redirect to frontend admin page
            $admin_page_url = home_url('/area-admin/'); // This should be the page with [ar_admin] shortcode
            wp_redirect($admin_page_url);
            exit;
        }
    }
    
    /**
     * Check user access to protected pages
     */
    public function check_user_access() {
        if (!is_user_logged_in()) {
            return;
        }
        
        $user_id = get_current_user_id();
        $user = wp_get_current_user();
        
        // Only check for portal users
        if (!in_array('portal_user', $user->roles)) {
            return;
        }
        
        // Get user status
        $status = AR_Users::get_user_status($user_id);
        
        // If user is pending and trying to access restricted content
        if ($status === 'pending' && $this->is_restricted_page()) {
            // You can either:
            // 1. Show a message and block access
            // 2. Redirect to a "pending" page
            
            wp_die(
                __('Il tuo account è in attesa di approvazione. Riceverai una email quando sarà attivato.', 'area-riservata'),
                __('Approvazione Pendente', 'area-riservata'),
                array('response' => 403)
            );
        }
        
        // If user is rejected or disabled
        if (in_array($status, array('rejected', 'disabled'))) {
            wp_logout();
            wp_redirect(home_url());
            exit;
        }
    }
    
    /**
     * Check if current page is restricted
     */
    private function is_restricted_page() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Check if page contains restricted shortcodes
        $restricted_shortcodes = array('[ar_dashboard]', '[ar_documents]');
        
        foreach ($restricted_shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, str_replace(array('[', ']'), '', $shortcode))) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitize filename
     */
    public static function sanitize_filename($filename) {
        // Remove special characters
        $filename = sanitize_file_name($filename);
        
        // Remove accents
        $filename = remove_accents($filename);
        
        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);
        
        return $filename;
    }
    
    /**
     * Validate user capability
     */
    public static function validate_capability($capability) {
        if (!current_user_can($capability)) {
            return new WP_Error('forbidden', __('Non hai i permessi necessari', 'area-riservata'));
        }
        
        return true;
    }
    
    /**
     * Get client IP address
     */
    public static function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
}
