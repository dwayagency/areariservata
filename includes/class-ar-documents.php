<?php
/**
 * Document management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Documents {
    
    private static $instance = null;
    
    // Allowed file types
    private $allowed_types = array(
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'zip' => 'application/zip',
    );
    
    // Max file size: 10MB
    private $max_file_size = 10485760;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_ar_upload_document', array($this, 'ajax_upload_document'));
        add_action('wp_ajax_ar_delete_document', array($this, 'ajax_delete_document'));
        add_action('wp_ajax_ar_get_user_documents', array($this, 'ajax_get_user_documents'));
    }
    
    /**
     * Upload document
     */
    public function ajax_upload_document() {
        check_ajax_referer('ar_admin_nonce', 'nonce');
        
        // Allow Portal Admins and WordPress Administrators
        if (!current_user_can('ar_manage_documents') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permesso negato', 'area-riservata')));
        }
        
        if (!isset($_FILES['document']) || !isset($_POST['user_id'])) {
            wp_send_json_error(array('message' => __('Dati mancanti', 'area-riservata')));
        }
        
        $user_id = intval($_POST['user_id']);
        $file = $_FILES['document'];
        
        // Validate file
        $validation = $this->validate_file($file);
        if (is_wp_error($validation)) {
            wp_send_json_error(array('message' => $validation->get_error_message()));
        }
        
        // Upload file
        $result = $this->upload_file($file, $user_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array(
            'message' => __('Documento caricato con successo', 'area-riservata'),
            'document_id' => $result
        ));
    }
    
    /**
     * Validate uploaded file
     */
    private function validate_file($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', __('Errore durante il caricamento', 'area-riservata'));
        }
        
        // Check file size
        if ($file['size'] > $this->max_file_size) {
            return new WP_Error('file_too_large', sprintf(
                __('Il file è troppo grande. Dimensione massima: %s MB', 'area-riservata'),
                ($this->max_file_size / 1048576)
            ));
        }
        
        // Check file extension
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!array_key_exists($file_ext, $this->allowed_types)) {
            return new WP_Error('invalid_type', __('Tipo di file non consentito', 'area-riservata'));
        }
        
        // Verify MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if ($mime_type !== $this->allowed_types[$file_ext]) {
            return new WP_Error('invalid_mime', __('Tipo MIME non valido', 'area-riservata'));
        }
        
        return true;
    }
    
    /**
     * Upload and save file
     */
    private function upload_file($file, $user_id) {
        global $wpdb;
        
        // Sanitize filename
        $original_filename = sanitize_file_name($file['name']);
        $file_ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        
        // Generate unique filename
        $unique_filename = wp_unique_filename(AR_UPLOAD_DIR, time() . '_' . $original_filename);
        
        // Create user directory if needed
        $user_dir = AR_UPLOAD_DIR . '/user_' . $user_id;
        if (!file_exists($user_dir)) {
            wp_mkdir_p($user_dir);
        }
        
        $filepath = $user_dir . '/' . $unique_filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return new WP_Error('move_failed', __('Impossibile salvare il file', 'area-riservata'));
        }
        
        // Set file permissions
        chmod($filepath, 0644);
        
        // Save to database
        $table = $wpdb->prefix . 'ar_documents';
        $inserted = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'filename' => $unique_filename,
                'original_filename' => $original_filename,
                'filepath' => $filepath,
                'filesize' => $file['size'],
                'mime_type' => $file['type'],
                'uploaded_by' => get_current_user_id(),
                'upload_date' => current_time('mysql'),
                'status' => 'active'
            ),
            array('%d', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s')
        );
        
        if (!$inserted) {
            // Delete file if database insert failed
            unlink($filepath);
            return new WP_Error('db_error', __('Errore nel salvataggio dei dati', 'area-riservata'));
        }
        
        $document_id = $wpdb->insert_id;
        
        // Log upload
        AR_Audit::log_action(get_current_user_id(), 'document_uploaded', $document_id, array(
            'filename' => $original_filename,
            'assigned_to' => $user_id
        ));
        
        return $document_id;
    }
    
    /**
     * Delete document
     */
    public function ajax_delete_document() {
        check_ajax_referer('ar_admin_nonce', 'nonce');
        
        if (!current_user_can('ar_manage_documents') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permesso negato', 'area-riservata')));
        }
        
        $document_id = intval($_POST['document_id']);
        
        $result = $this->delete_document($document_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array('message' => __('Documento eliminato', 'area-riservata')));
    }
    
    /**
     * Delete document from database and filesystem
     */
    public function delete_document($document_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'ar_documents';
        $document = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $document_id
        ));
        
        if (!$document) {
            return new WP_Error('not_found', __('Documento non trovato', 'area-riservata'));
        }
        
        // Delete physical file
        if (file_exists($document->filepath)) {
            unlink($document->filepath);
        }
        
        // Update database (soft delete)
        $wpdb->update(
            $table,
            array('status' => 'deleted'),
            array('id' => $document_id),
            array('%s'),
            array('%d')
        );
        
        // Log deletion
        AR_Audit::log_action(get_current_user_id(), 'document_deleted', $document_id, array(
            'filename' => $document->original_filename
        ));
        
        return true;
    }
    
    /**
     * Get user documents (AJAX)
     */
    public function ajax_get_user_documents() {
        check_ajax_referer('ar_frontend_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Non autenticato', 'area-riservata')));
        }
        
        $user_id = get_current_user_id();
        
        // Check if user is approved
        if (!AR_Users::is_user_approved($user_id)) {
            wp_send_json_error(array('message' => __('Account non approvato', 'area-riservata')));
        }
        
        $documents = $this->get_user_documents($user_id);
        
        wp_send_json_success(array('documents' => $documents));
    }
    
    /**
     * Get documents for a specific user
     */
    public function get_user_documents($user_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'ar_documents';
        $documents = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND status = 'active' ORDER BY upload_date DESC",
            $user_id
        ));
        
        return $documents;
    }
    
    /**
     * Get document by ID
     */
    public function get_document($document_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'ar_documents';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d AND status = 'active'",
            $document_id
        ));
    }
    
    /**
     * Get all documents (admin)
     */
    public function get_all_documents() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'ar_documents';
        return $wpdb->get_results(
            "SELECT d.*, u.user_email, u.display_name 
             FROM $table d 
             LEFT JOIN {$wpdb->users} u ON d.user_id = u.ID 
             WHERE d.status = 'active' 
             ORDER BY d.upload_date DESC"
        );
    }
}
