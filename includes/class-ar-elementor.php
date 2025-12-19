<?php
/**
 * Elementor Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Elementor {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Register Elementor widgets
        add_action('elementor/widgets/register', array($this, 'register_widgets'));
        
        // Register widget category
        add_action('elementor/elements/categories_registered', array($this, 'register_category'));
        
        // Enqueue editor styles
        add_action('elementor/editor/after_enqueue_styles', array($this, 'editor_styles'));
    }
    
    /**
     * Register custom widget category
     */
    public function register_category($elements_manager) {
        $elements_manager->add_category(
            'area-riservata',
            array(
                'title' => __('Area Riservata', 'area-riservata'),
                'icon' => 'fa fa-lock',
            )
        );
    }
    
    /**
     * Register widgets
     */
    public function register_widgets($widgets_manager) {
        // Check if Elementor is active
        if (!did_action('elementor/loaded')) {
            return;
        }
        
        // Register each widget
        require_once AR_PLUGIN_DIR . 'elementor/widgets/login-widget.php';
        require_once AR_PLUGIN_DIR . 'elementor/widgets/register-widget.php';
        require_once AR_PLUGIN_DIR . 'elementor/widgets/dashboard-widget.php';
        require_once AR_PLUGIN_DIR . 'elementor/widgets/admin-widget.php';
        require_once AR_PLUGIN_DIR . 'elementor/widgets/password-reset-widget.php';
        
        $widgets_manager->register(new \AR_Elementor_Login_Widget());
        $widgets_manager->register(new \AR_Elementor_Register_Widget());
        $widgets_manager->register(new \AR_Elementor_Dashboard_Widget());
        $widgets_manager->register(new \AR_Elementor_Admin_Widget());
        $widgets_manager->register(new \AR_Elementor_Password_Reset_Widget());
    }
    
    /**
     * Enqueue editor styles
     */
    public function editor_styles() {
        wp_enqueue_style(
            'ar-elementor-editor',
            AR_PLUGIN_URL . 'assets/css/elementor-editor.css',
            array(),
            AR_VERSION
        );
    }
}
