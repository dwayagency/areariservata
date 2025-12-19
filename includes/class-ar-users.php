<?php
/**
 * User management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Users {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_ar_register_user', array($this, 'ajax_register_user'));
        add_action('wp_ajax_nopriv_ar_register_user', array($this, 'ajax_register_user'));
        add_action('wp_ajax_ar_approve_user', array($this, 'ajax_approve_user'));
        add_action('wp_ajax_ar_reject_user', array($this, 'ajax_reject_user'));
        add_action('wp_ajax_ar_disable_user', array($this, 'ajax_disable_user'));
        add_action('wp_ajax_ar_create_user', array($this, 'ajax_create_user'));
    }
    
    /**
     * Register new user (frontend)
     */
    public function ajax_register_user() {
        check_ajax_referer('ar_register_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        
        // Validate email
        if (!is_email($email)) {
            wp_send_json_error(array('message' => __('Email non valida', 'area-riservata')));
        }
        
        // Check if email exists
        if (email_exists($email)) {
            wp_send_json_error(array('message' => __('Email già registrata', 'area-riservata')));
        }
        
        // Create user
        $user_id = wp_create_user($email, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => $user_id->get_error_message()));
        }
        
        // Set user meta
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => 'portal_user'
        ));
        
        // Set user status to pending
        update_user_meta($user_id, 'ar_user_status', 'pending');
        
        // Log registration
        AR_Audit::log_action($user_id, 'user_registered', null, array(
            'email' => $email,
            'name' => $first_name . ' ' . $last_name
        ));
        
        // Send notification to admins
        $this->notify_admins_new_user($user_id);
        
        wp_send_json_success(array(
            'message' => __('Registrazione completata! Il tuo account è in attesa di approvazione.', 'area-riservata')
        ));
    }
    
    /**
     * Create user manually (admin)
     */
    public function ajax_create_user() {
        check_ajax_referer('ar_admin_nonce', 'nonce');
        
        if (!current_user_can('ar_manage_users')) {
            wp_send_json_error(array('message' => __('Permesso negato', 'area-riservata')));
        }
        
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $auto_approve = isset($_POST['auto_approve']) && $_POST['auto_approve'] === 'true';
        
        if (!is_email($email)) {
            wp_send_json_error(array('message' => __('Email non valida', 'area-riservata')));
        }
        
        if (email_exists($email)) {
            wp_send_json_error(array('message' => __('Email già registrata', 'area-riservata')));
        }
        
        $user_id = wp_create_user($email, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => $user_id->get_error_message()));
        }
        
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => 'portal_user'
        ));
        
        $status = $auto_approve ? 'approved' : 'pending';
        update_user_meta($user_id, 'ar_user_status', $status);
        
        AR_Audit::log_action(get_current_user_id(), 'user_created', null, array(
            'created_user_id' => $user_id,
            'email' => $email,
            'status' => $status
        ));
        
        wp_send_json_success(array(
            'message' => __('Utente creato con successo', 'area-riservata'),
            'user_id' => $user_id
        ));
    }
    
    /**
     * Approve user
     */
    public function ajax_approve_user() {
        check_ajax_referer('ar_admin_nonce', 'nonce');
        
        if (!current_user_can('ar_manage_users')) {
            wp_send_json_error(array('message' => __('Permesso negato', 'area-riservata')));
        }
        
        $user_id = intval($_POST['user_id']);
        
        update_user_meta($user_id, 'ar_user_status', 'approved');
        
        AR_Audit::log_action(get_current_user_id(), 'user_approved', null, array(
            'approved_user_id' => $user_id
        ));
        
        // Send email to user
        $this->notify_user_approved($user_id);
        
        wp_send_json_success(array('message' => __('Utente approvato', 'area-riservata')));
    }
    
    /**
     * Reject user
     */
    public function ajax_reject_user() {
        check_ajax_referer('ar_admin_nonce', 'nonce');
        
        if (!current_user_can('ar_manage_users')) {
            wp_send_json_error(array('message' => __('Permesso negato', 'area-riservata')));
        }
        
        $user_id = intval($_POST['user_id']);
        
        update_user_meta($user_id, 'ar_user_status', 'rejected');
        
        AR_Audit::log_action(get_current_user_id(), 'user_rejected', null, array(
            'rejected_user_id' => $user_id
        ));
        
        wp_send_json_success(array('message' => __('Utente rifiutato', 'area-riservata')));
    }
    
    /**
     * Disable/Enable user
     */
    public function ajax_disable_user() {
        check_ajax_referer('ar_admin_nonce', 'nonce');
        
        if (!current_user_can('ar_manage_users')) {
            wp_send_json_error(array('message' => __('Permesso negato', 'area-riservata')));
        }
        
        $user_id = intval($_POST['user_id']);
        $disable = $_POST['disable'] === 'true';
        
        $status = $disable ? 'disabled' : 'approved';
        update_user_meta($user_id, 'ar_user_status', $status);
        
        AR_Audit::log_action(get_current_user_id(), $disable ? 'user_disabled' : 'user_enabled', null, array(
            'user_id' => $user_id
        ));
        
        $message = $disable ? __('Utente disabilitato', 'area-riservata') : __('Utente abilitato', 'area-riservata');
        wp_send_json_success(array('message' => $message));
    }
    
    /**
     * Get user status
     */
    public static function get_user_status($user_id) {
        $status = get_user_meta($user_id, 'ar_user_status', true);
        return $status ? $status : 'pending';
    }
    
    /**
     * Check if user is approved
     */
    public static function is_user_approved($user_id) {
        return self::get_user_status($user_id) === 'approved';
    }
    
    /**
     * Get all portal users
     */
    public static function get_portal_users($status = null) {
        $args = array(
            'role' => 'portal_user',
            'orderby' => 'registered',
            'order' => 'DESC'
        );
        
        if ($status) {
            $args['meta_key'] = 'ar_user_status';
            $args['meta_value'] = $status;
        }
        
        return get_users($args);
    }
    
    /**
     * Notify admins of new user registration
     */
    private function notify_admins_new_user($user_id) {
        $user = get_user_by('id', $user_id);
        $admins = get_users(array('role' => 'portal_admin'));
        
        foreach ($admins as $admin) {
            $subject = __('Nuova registrazione in attesa di approvazione', 'area-riservata');
            $message = sprintf(
                __('Un nuovo utente si è registrato e richiede approvazione:\n\nNome: %s\nEmail: %s\n\nAccedi all\'area admin per gestire la richiesta.', 'area-riservata'),
                $user->first_name . ' ' . $user->last_name,
                $user->user_email
            );
            
            wp_mail($admin->user_email, $subject, $message);
        }
    }
    
    /**
     * Notify user of approval
     */
    private function notify_user_approved($user_id) {
        $user = get_user_by('id', $user_id);
        
        $subject = __('Account approvato', 'area-riservata');
        $message = sprintf(
            __('Il tuo account è stato approvato!\n\nPuoi ora accedere all\'area riservata con le tue credenziali.', 'area-riservata')
        );
        
        wp_mail($user->user_email, $subject, $message);
    }
}
