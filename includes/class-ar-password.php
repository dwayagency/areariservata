<?php
/**
 * Password reset handler for Area Riservata
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Password {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_ar_reset_password', array($this, 'ajax_reset_password'));
        add_action('wp_ajax_ar_send_reset_link', array($this, 'ajax_send_reset_link'));
        add_action('wp_ajax_nopriv_ar_send_reset_link', array($this, 'ajax_send_reset_link'));
    }
    
    /**
     * Send password reset link (admin or user)
     */
    public function ajax_send_reset_link() {
        $email = sanitize_email($_POST['email']);
        
        if (!is_email($email)) {
            wp_send_json_error(array('message' => __('Email non valida', 'area-riservata')));
        }
        
        $user = get_user_by('email', $email);
        
        if (!$user) {
            // Don't reveal if email exists or not
            wp_send_json_success(array('message' => __('Se l\'email esiste, riceverai un link per il reset della password.', 'area-riservata')));
        }
        
        // Check if user is portal user or portal admin
        if (!in_array('portal_user', $user->roles) && !in_array('portal_admin', $user->roles)) {
            wp_send_json_success(array('message' => __('Se l\'email esiste, riceverai un link per il reset della password.', 'area-riservata')));
        }
        
        // Generate reset key
        $reset_key = wp_generate_password(20, false);
        update_user_meta($user->ID, 'ar_password_reset_key', $reset_key);
        update_user_meta($user->ID, 'ar_password_reset_expires', time() + 3600); // 1 hour
        
        // Send email
        $reset_url = add_query_arg(array(
            'action' => 'ar_reset_password',
            'key' => $reset_key,
            'email' => urlencode($email)
        ), home_url('/'));
        
        $subject = __('Reset Password - Area Riservata', 'area-riservata');
        $message = sprintf(
            __('Hai richiesto il reset della password.\n\nClicca sul seguente link per reimpostare la password:\n%s\n\nQuesto link scadrà tra 1 ora.\n\nSe non hai richiesto il reset, ignora questa email.', 'area-riservata'),
            $reset_url
        );
        
        wp_mail($email, $subject, $message);
        
        // Log action
        AR_Audit::log_action($user->ID, 'password_reset_requested', null, array(
            'email' => $email
        ));
        
        wp_send_json_success(array('message' => __('Se l\'email esiste, riceverai un link per il reset della password.', 'area-riservata')));
    }
    
    /**
     * Reset password with key (admin action)
     */
    public function ajax_reset_password() {
        check_ajax_referer('ar_admin_nonce', 'nonce');
        
        if (!current_user_can('ar_manage_users')) {
            wp_send_json_error(array('message' => __('Permesso negato', 'area-riservata')));
        }
        
        $user_id = intval($_POST['user_id']);
        $new_password = wp_generate_password(12, true);
        
        wp_set_password($new_password, $user_id);
        
        $user = get_user_by('id', $user_id);
        
        // Send email with new password
        $subject = __('Nuova Password - Area Riservata', 'area-riservata');
        $message = sprintf(
            __('La tua password è stata reimpostata.\n\nNuova password: %s\n\nTi consigliamo di cambiarla dopo il primo accesso.', 'area-riservata'),
            $new_password
        );
        
        wp_mail($user->user_email, $subject, $message);
        
        // Log action
        AR_Audit::log_action(get_current_user_id(), 'password_reset', null, array(
            'target_user_id' => $user_id
        ));
        
        wp_send_json_success(array(
            'message' => __('Password reimpostata e inviata via email all\'utente', 'area-riservata')
        ));
    }
}
