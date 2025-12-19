<?php
/**
 * Audit log system
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Audit {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_ar_get_audit_logs', array($this, 'ajax_get_logs'));
    }
    
    /**
     * Log an action
     */
    public static function log_action($user_id, $action, $document_id = null, $details = array()) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'ar_audit_log';
        
        $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'action' => $action,
                'document_id' => $document_id,
                'ip_address' => AR_Security::get_client_ip(),
                'timestamp' => current_time('mysql'),
                'details' => json_encode($details)
            ),
            array('%d', '%s', '%d', '%s', '%s', '%s')
        );
    }
    
    /**
     * Get audit logs (AJAX)
     */
    public function ajax_get_logs() {
        check_ajax_referer('ar_admin_nonce', 'nonce');
        
        if (!current_user_can('ar_view_audit_log')) {
            wp_send_json_error(array('message' => __('Permesso negato', 'area-riservata')));
        }
        
        $filters = array();
        
        if (isset($_POST['user_id'])) {
            $filters['user_id'] = intval($_POST['user_id']);
        }
        
        if (isset($_POST['action'])) {
            $filters['action'] = sanitize_text_field($_POST['action']);
        }
        
        if (isset($_POST['document_id'])) {
            $filters['document_id'] = intval($_POST['document_id']);
        }
        
        $logs = $this->get_logs($filters, 100);
        
        wp_send_json_success(array('logs' => $logs));
    }
    
    /**
     * Get logs from database
     */
    public function get_logs($filters = array(), $limit = 100) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'ar_audit_log';
        $where = array('1=1');
        $values = array();
        
        if (isset($filters['user_id'])) {
            $where[] = 'user_id = %d';
            $values[] = $filters['user_id'];
        }
        
        if (isset($filters['action'])) {
            $where[] = 'action = %s';
            $values[] = $filters['action'];
        }
        
        if (isset($filters['document_id'])) {
            $where[] = 'document_id = %d';
            $values[] = $filters['document_id'];
        }
        
        $where_sql = implode(' AND ', $where);
        $limit = intval($limit);
        
        if (!empty($values)) {
            $query = $wpdb->prepare(
                "SELECT l.*, u.user_email, u.display_name 
                 FROM $table l 
                 LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID 
                 WHERE $where_sql 
                 ORDER BY l.timestamp DESC 
                 LIMIT %d",
                array_merge($values, array($limit))
            );
        } else {
            $query = "SELECT l.*, u.user_email, u.display_name 
                      FROM $table l 
                      LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID 
                      ORDER BY l.timestamp DESC 
                      LIMIT $limit";
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get logs for a specific user
     */
    public function get_user_logs($user_id, $limit = 50) {
        return $this->get_logs(array('user_id' => $user_id), $limit);
    }
    
    /**
     * Get logs for a specific document
     */
    public function get_document_logs($document_id, $limit = 50) {
        return $this->get_logs(array('document_id' => $document_id), $limit);
    }
    
    /**
     * Get recent activity
     */
    public function get_recent_activity($limit = 20) {
        return $this->get_logs(array(), $limit);
    }
    
    /**
     * Clean old logs (optional cleanup function)
     */
    public function cleanup_old_logs($days = 365) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'ar_audit_log';
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE timestamp < %s",
            $date
        ));
    }
}
