<?php
/**
 * Protected download handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Download {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'handle_download'));
    }
    
    /**
     * Handle download request
     */
    public function handle_download() {
        if (!isset($_GET['ar_download']) || !isset($_GET['token'])) {
            return;
        }
        
        $document_id = intval($_GET['ar_download']);
        $token = sanitize_text_field($_GET['token']);
        
        // Verify nonce
        if (!wp_verify_nonce($token, 'ar_download_' . $document_id)) {
            $this->download_error(__('Link non valido o scaduto', 'area-riservata'));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            $this->download_error(__('Devi effettuare il login', 'area-riservata'));
        }
        
        $user_id = get_current_user_id();
        
        // Check if user is approved (skip for WordPress Administrators)
        if (!AR_Users::is_user_approved($user_id) && !current_user_can('ar_manage_documents') && !current_user_can('manage_options')) {
            $this->download_error(__('Account non approvato', 'area-riservata'));
        }
        
        // Get document
        $documents = AR_Documents::get_instance();
        $document = $documents->get_document($document_id);
        
        if (!$document) {
            $this->download_error(__('Documento non trovato', 'area-riservata'));
        }
        
        // Check if user has access to this document
        // Portal admins and WordPress admins can download any file, users only their assigned files
        if (!current_user_can('ar_manage_documents') && !current_user_can('manage_options') && $document->user_id != $user_id) {
            // Log unauthorized access attempt
            AR_Audit::log_action($user_id, 'access_denied', $document_id, array(
                'reason' => 'not_assigned',
                'document_owner' => $document->user_id
            ));
            
            $this->download_error(__('Non hai i permessi per scaricare questo documento', 'area-riservata'));
        }
        
        // Check if file exists
        if (!file_exists($document->filepath)) {
            $this->download_error(__('File non trovato sul server', 'area-riservata'));
        }
        
        // Log download
        AR_Audit::log_action($user_id, 'document_downloaded', $document_id, array(
            'filename' => $document->original_filename
        ));
        
        // Serve file
        $this->serve_file($document);
    }
    
    /**
     * Serve file via PHP
     */
    private function serve_file($document) {
        // Clear output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers
        header('Content-Type: ' . $document->mime_type);
        header('Content-Disposition: attachment; filename="' . $document->original_filename . '"');
        header('Content-Length: ' . $document->filesize);
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        
        // Prevent script execution
        header('X-Content-Type-Options: nosniff');
        
        // Read and output file
        readfile($document->filepath);
        exit;
    }
    
    /**
     * Display download error
     */
    private function download_error($message) {
        wp_die(
            esc_html($message),
            __('Errore Download', 'area-riservata'),
            array('response' => 403)
        );
    }
    
    /**
     * Generate download link
     */
    public static function generate_download_link($document_id) {
        $token = wp_create_nonce('ar_download_' . $document_id);
        
        return add_query_arg(array(
            'ar_download' => $document_id,
            'token' => $token
        ), home_url('/'));
    }
}
