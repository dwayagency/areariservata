<?php
/**
 * Frontend interface handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Frontend {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Register shortcodes
        add_shortcode('ar_register', array($this, 'register_form_shortcode'));
        add_shortcode('ar_login', array($this, 'login_form_shortcode'));
        add_shortcode('ar_dashboard', array($this, 'user_dashboard_shortcode'));
        add_shortcode('ar_admin', array($this, 'admin_dashboard_shortcode'));
        add_shortcode('ar_password_reset', array($this, 'password_reset_shortcode'));
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // Handle login
        add_action('wp_ajax_nopriv_ar_login', array($this, 'ajax_login'));
        add_action('wp_ajax_ar_logout', array($this, 'ajax_logout'));
    }
    
    /**
     * Enqueue CSS and JS
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'area-riservata',
            AR_PLUGIN_URL . 'assets/css/area-riservata.css',
            array(),
            AR_VERSION
        );
        
        wp_enqueue_script(
            'area-riservata',
            AR_PLUGIN_URL . 'assets/js/area-riservata.js',
            array('jquery'),
            AR_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('area-riservata', 'arData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'registerNonce' => wp_create_nonce('ar_register_nonce'),
            'adminNonce' => wp_create_nonce('ar_admin_nonce'),
            'frontendNonce' => wp_create_nonce('ar_frontend_nonce'),
            'strings' => array(
                'confirm_delete' => __('Sei sicuro di voler eliminare questo documento?', 'area-riservata'),
                'confirm_reject' => __('Sei sicuro di voler rifiutare questo utente?', 'area-riservata'),
                'loading' => __('Caricamento...', 'area-riservata'),
                'error' => __('Si è verificato un errore', 'area-riservata'),
            )
        ));
    }
    
    /**
     * Registration form shortcode
     */
    public function register_form_shortcode($atts) {
        if (is_user_logged_in()) {
            return '<p>' . __('Sei già registrato e loggato.', 'area-riservata') . '</p>';
        }
        
        ob_start();
        include AR_PLUGIN_DIR . 'templates/registration-form.php';
        return ob_get_clean();
    }
    
    /**
     * Login form shortcode
     */
    public function login_form_shortcode($atts) {
        if (is_user_logged_in()) {
            return '<p>' . __('Sei già loggato.', 'area-riservata') . ' <a href="#" class="ar-logout-btn">' . __('Logout', 'area-riservata') . '</a></p>';
        }
        
        ob_start();
        include AR_PLUGIN_DIR . 'templates/login-form.php';
        return ob_get_clean();
    }
    
    /**
     * User dashboard shortcode
     */
    public function user_dashboard_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Devi effettuare il login per accedere a questa area.', 'area-riservata') . '</p>';
        }
        
        $user_id = get_current_user_id();
        
        // Check if user is approved
        if (!AR_Users::is_user_approved($user_id)) {
            return '<div class="ar-pending-message"><p>' . __('Il tuo account è in attesa di approvazione. Riceverai una email quando sarà attivato.', 'area-riservata') . '</p></div>';
        }
        
        ob_start();
        include AR_PLUGIN_DIR . 'templates/user-dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * Admin dashboard shortcode
     */
    public function admin_dashboard_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Devi effettuare il login per accedere a questa area.', 'area-riservata') . '</p>';
        }
        
        // Allow both Portal Admins and WordPress Administrators
        if (!current_user_can('ar_manage_users') && !current_user_can('manage_options')) {
            return '<p>' . __('Non hai i permessi per accedere a questa area.', 'area-riservata') . '</p>';
        }
        
        ob_start();
        include AR_PLUGIN_DIR . 'templates/admin-dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * Handle login via AJAX
     */
    public function ajax_login() {
        check_ajax_referer('ar_register_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'true';
        
        $creds = array(
            'user_login' => $email,
            'user_password' => $password,
            'remember' => $remember
        );
        
        $user = wp_signon($creds, is_ssl());
        
        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => __('Credenziali non valide', 'area-riservata')));
        }
        
        wp_send_json_success(array(
            'message' => __('Login effettuato con successo', 'area-riservata'),
            'redirect' => home_url('/area-riservata/')
        ));
    }
    
    /**
     * Handle logout
     */
    public function ajax_logout() {
        wp_logout();
        wp_send_json_success(array(
            'message' => __('Logout effettuato', 'area-riservata'),
            'redirect' => home_url()
        ));
    }
    
    /**
     * Password reset form shortcode
     */
    public function password_reset_shortcode($atts) {
        ob_start();
        include AR_PLUGIN_DIR . 'templates/password-reset-form.php';
        return ob_get_clean();
    }
}
