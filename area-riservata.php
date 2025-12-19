<?php
/**
 * Plugin Name: Area Riservata
 * Plugin URI: https://example.com/area-riservata
 * Description: Plugin per gestione area riservata con documenti sensibili e approvazione utenti
 * Version: 1.0.0
 * Author: Alessandro Molinari
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: area-riservata
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AR_VERSION', '1.0.0');
define('AR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AR_PLUGIN_FILE', __FILE__);
define('AR_UPLOAD_DIR', WP_CONTENT_DIR . '/uploads/area-riservata-secure');

/**
 * Main plugin class
 */
class Area_Riservata {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once AR_PLUGIN_DIR . 'includes/class-ar-roles.php';
        require_once AR_PLUGIN_DIR . 'includes/class-ar-users.php';
        require_once AR_PLUGIN_DIR . 'includes/class-ar-documents.php';
        require_once AR_PLUGIN_DIR . 'includes/class-ar-download.php';
        require_once AR_PLUGIN_DIR . 'includes/class-ar-security.php';
        require_once AR_PLUGIN_DIR . 'includes/class-ar-audit.php';
        require_once AR_PLUGIN_DIR . 'includes/class-ar-frontend.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(AR_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(AR_PLUGIN_FILE, array($this, 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Initialize plugin components
     */
    public function init() {
        // Initialize classes
        AR_Roles::get_instance();
        AR_Users::get_instance();
        AR_Documents::get_instance();
        AR_Download::get_instance();
        AR_Security::get_instance();
        AR_Audit::get_instance();
        AR_Frontend::get_instance();
        
        // Load text domain
        load_plugin_textdomain('area-riservata', false, dirname(plugin_basename(AR_PLUGIN_FILE)) . '/languages');
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        global $wpdb;
        
        // Create custom roles
        AR_Roles::create_roles();
        
        // Create database tables
        $charset_collate = $wpdb->get_charset_collate();
        
        // Documents table
        $table_documents = $wpdb->prefix . 'ar_documents';
        $sql_documents = "CREATE TABLE IF NOT EXISTS $table_documents (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            filename varchar(255) NOT NULL,
            original_filename varchar(255) NOT NULL,
            filepath varchar(500) NOT NULL,
            filesize bigint(20) UNSIGNED NOT NULL,
            mime_type varchar(100) NOT NULL,
            uploaded_by bigint(20) UNSIGNED NOT NULL,
            upload_date datetime NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY uploaded_by (uploaded_by),
            KEY status (status)
        ) $charset_collate;";
        
        // Audit log table
        $table_audit = $wpdb->prefix . 'ar_audit_log';
        $sql_audit = "CREATE TABLE IF NOT EXISTS $table_audit (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            action varchar(50) NOT NULL,
            document_id bigint(20) UNSIGNED DEFAULT NULL,
            ip_address varchar(45) NOT NULL,
            timestamp datetime NOT NULL,
            details longtext DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY timestamp (timestamp)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_documents);
        dbDelta($sql_audit);
        
        // Create secure upload directory
        if (!file_exists(AR_UPLOAD_DIR)) {
            wp_mkdir_p(AR_UPLOAD_DIR);
        }
        
        // Create .htaccess to block direct access
        $htaccess_file = AR_UPLOAD_DIR . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "# Block all direct access to files\n";
            $htaccess_content .= "Order Deny,Allow\n";
            $htaccess_content .= "Deny from all\n";
            file_put_contents($htaccess_file, $htaccess_content);
        }
        
        // Create index.php to prevent directory listing
        $index_file = AR_UPLOAD_DIR . '/index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, '<?php // Silence is golden');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

/**
 * Initialize plugin
 */
function area_riservata_init() {
    return Area_Riservata::get_instance();
}

// Start the plugin
area_riservata_init();
