<?php
/**
 * Admin backend management
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Add admin menu (only for WordPress Administrators)
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add admin bar link
        add_action('admin_bar_menu', array($this, 'add_admin_bar_link'), 100);
        
        // AJAX handler for auto-setup
        add_action('wp_ajax_ar_auto_setup_pages', array($this, 'ajax_auto_setup_pages'));
    }
    
    /**
     * Add admin menu to WordPress backend
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Area Riservata', 'area-riservata'),
            __('Area Riservata', 'area-riservata'),
            'manage_options',
            'area-riservata',
            array($this, 'render_dashboard_page'),
            'dashicons-lock',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'area-riservata',
            __('Dashboard', 'area-riservata'),
            __('Dashboard', 'area-riservata'),
            'manage_options',
            'area-riservata',
            array($this, 'render_dashboard_page')
        );
        
        // Users submenu
        add_submenu_page(
            'area-riservata',
            __('Gestione Utenti', 'area-riservata'),
            __('Utenti Portale', 'area-riservata'),
            'manage_options',
            'ar-users',
            array($this, 'render_users_page')
        );
        
        // Documents submenu
        add_submenu_page(
            'area-riservata',
            __('Gestione Documenti', 'area-riservata'),
            __('Documenti', 'area-riservata'),
            'manage_options',
            'ar-documents',
            array($this, 'render_documents_page')
        );
        
        // Audit Log submenu
        add_submenu_page(
            'area-riservata',
            __('Log Attività', 'area-riservata'),
            __('Log Attività', 'area-riservata'),
            'manage_options',
            'ar-audit',
            array($this, 'render_audit_page')
        );
        
        // Settings submenu (already added by AR_Colors class)
        // We just re-parent it
        add_submenu_page(
            'area-riservata',
            __('Personalizzazione Colori', 'area-riservata'),
            __('Colori', 'area-riservata'),
            'manage_options',
            'ar-colors',
            '__return_null' // Already rendered by AR_Colors
        );
    }
    
    /**
     * Add link to admin bar
     */
    public function add_admin_bar_link($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $wp_admin_bar->add_node(array(
            'id' => 'area-riservata',
            'title' => '<span class="ab-icon dashicons-lock"></span> Area Riservata',
            'href' => admin_url('admin.php?page=area-riservata'),
            'meta' => array(
                'title' => __('Gestisci Area Riservata', 'area-riservata')
            )
        ));
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        $pending_users = AR_Users::get_portal_users('pending');
        $approved_users = AR_Users::get_portal_users('approved');
        $documents = AR_Documents::get_instance();
        $all_documents = $documents->get_all_documents();
        $audit = AR_Audit::get_instance();
        $recent_activity = $audit->get_recent_activity(10);
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-lock" style="font-size: 28px; vertical-align: middle;"></span>
                <?php _e('Area Riservata - Dashboard', 'area-riservata'); ?>
            </h1>
            
            <hr class="wp-header-end">
            
            <div class="ar-admin-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
                <!-- Pending Users Card -->
                <div class="ar-stat-card" style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #d63638;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="margin: 0; color: #646970; font-size: 13px; text-transform: uppercase; font-weight: 600;">Richieste Pendenti</p>
                            <h2 style="margin: 8px 0 0; font-size: 36px; font-weight: 700;"><?php echo count($pending_users); ?></h2>
                        </div>
                        <span class="dashicons dashicons-clock" style="font-size: 48px; color: #d63638; opacity: 0.2;"></span>
                    </div>
                    <a href="<?php echo admin_url('admin.php?page=ar-users&status=pending'); ?>" class="button button-primary" style="margin-top: 16px; width: 100%;">
                        <?php _e('Gestisci Richieste', 'area-riservata'); ?>
                    </a>
                </div>
                
                <!-- Approved Users Card -->
                <div class="ar-stat-card" style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #00a32a;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="margin: 0; color: #646970; font-size: 13px; text-transform: uppercase; font-weight: 600;">Utenti Attivi</p>
                            <h2 style="margin: 8px 0 0; font-size: 36px; font-weight: 700;"><?php echo count($approved_users); ?></h2>
                        </div>
                        <span class="dashicons dashicons-groups" style="font-size: 48px; color: #00a32a; opacity: 0.2;"></span>
                    </div>
                    <a href="<?php echo admin_url('admin.php?page=ar-users&status=approved'); ?>" class="button" style="margin-top: 16px; width: 100%;">
                        <?php _e('Vedi Utenti', 'area-riservata'); ?>
                    </a>
                </div>
                
                <!-- Documents Card -->
                <div class="ar-stat-card" style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #2271b1;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="margin: 0; color: #646970; font-size: 13px; text-transform: uppercase; font-weight: 600;">Documenti Totali</p>
                            <h2 style="margin: 8px 0 0; font-size: 36px; font-weight: 700;"><?php echo count($all_documents); ?></h2>
                        </div>
                        <span class="dashicons dashicons-media-document" style="font-size: 48px; color: #2271b1; opacity: 0.2;"></span>
                    </div>
                    <a href="<?php echo admin_url('admin.php?page=ar-documents'); ?>" class="button" style="margin-top: 16px; width: 100%;">
                        <?php _e('Gestisci Documenti', 'area-riservata'); ?>
                    </a>
                </div>
                
                <!-- Admin Users Card -->
                <div class="ar-stat-card" style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #f0b849;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="margin: 0; color: #646970; font-size: 13px; text-transform: uppercase; font-weight: 600;">Portal Admin</p>
                            <h2 style="margin: 8px 0 0; font-size: 36px; font-weight: 700;"><?php echo count(get_users(array('role' => 'portal_admin'))); ?></h2>
                        </div>
                        <span class="dashicons dashicons-admin-users" style="font-size: 48px; color: #f0b849; opacity: 0.2;"></span>
                    </div>
                    <a href="<?php echo admin_url('users.php?role=portal_admin'); ?>" class="button" style="margin-top: 16px; width: 100%;">
                        <?php _e('Gestisci Admin', 'area-riservata'); ?>
                    </a>
                </div>
            </div>
            
            <!-- Setup Wizard -->
            <?php
            // Check if pages are already created
            $pages_to_check = array(
                'registrazione' => '[ar_register]',
                'login' => '[ar_login]',
                'area-riservata' => '[ar_dashboard]',
                'area-admin' => '[ar_admin]',
                'password-reset' => '[ar_password_reset]'
            );
            
            $missing_pages = array();
            foreach ($pages_to_check as $slug => $shortcode) {
                $page = get_page_by_path($slug);
                if (!$page || strpos($page->post_content, $shortcode) === false) {
                    $missing_pages[] = $slug;
                }
            }
            
            if (!empty($missing_pages)):
            ?>
            <div class="ar-setup-wizard" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 32px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin: 20px 0; color: white;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="font-size: 64px;">🚀</div>
                    <div style="flex: 1;">
                        <h2 style="margin: 0 0 12px; color: white; font-size: 24px;">
                            <?php _e('Setup Rapido - Crea Tutte le Pagine!', 'area-riservata'); ?>
                        </h2>
                        <p style="margin: 0; opacity: 0.9; font-size: 15px;">
                            <?php _e('Clicca il pulsante per creare automaticamente tutte le pagine necessarie per il plugin (Login, Registrazione, Dashboard, Area Admin, Password Reset)', 'area-riservata'); ?>
                        </p>
                        <div id="ar-setup-message" style="margin-top: 12px; padding: 12px; background: rgba(255,255,255,0.2); border-radius: 8px; display: none;"></div>
                    </div>
                    <div>
                        <button id="ar-auto-setup-btn" class="button button-hero" style="background: white; color: #667eea; border: none; padding: 16px 32px; font-size: 16px; font-weight: 600; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                            <span class="dashicons dashicons-admin-page" style="vertical-align: middle; margin-right: 8px;"></span>
                            <?php _e('Crea Pagine Automaticamente', 'area-riservata'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                $('#ar-auto-setup-btn').on('click', function() {
                    var $btn = $(this);
                    var $message = $('#ar-setup-message');
                    
                    $btn.prop('disabled', true).html('<span class="dashicons dashicons-update dashicons-spin" style="vertical-align: middle;"></span> <?php _e('Creazione in corso...', 'area-riservata'); ?>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'ar_auto_setup_pages',
                            nonce: '<?php echo wp_create_nonce('ar_auto_setup'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $message.html('<strong>✅ ' + response.data.message + '</strong><br><small>' + response.data.details + '</small>').css('background', 'rgba(16, 185, 129, 0.3)').fadeIn();
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                $message.html('❌ ' + response.data.message).css('background', 'rgba(239, 68, 68, 0.3)').fadeIn();
                                $btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-page" style="vertical-align: middle; margin-right: 8px;"></span> <?php _e('Riprova', 'area-riservata'); ?>');
                            }
                        },
                        error: function() {
                            $message.html('❌ <?php _e('Errore di connessione', 'area-riservata'); ?>').css('background', 'rgba(239, 68, 68, 0.3)').fadeIn();
                            $btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-page" style="vertical-align: middle; margin-right: 8px;"></span> <?php _e('Riprova', 'area-riservata'); ?>');
                        }
                    });
                });
            });
            </script>
            <?php endif; ?>
            
            <!-- Quick Actions -->
            <div class="ar-quick-actions" style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 20px 0;">
                <h2><?php _e('Azioni Rapide', 'area-riservata'); ?></h2>
                <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 16px;">
                    <a href="<?php echo home_url('/area-admin/'); ?>" class="button button-primary button-large" target="_blank">
                        <span class="dashicons dashicons-admin-generic" style="vertical-align: middle;"></span>
                        <?php _e('Apri Area Admin Frontend', 'area-riservata'); ?>
                    </a>
                    <a href="<?php echo admin_url('user-new.php'); ?>" class="button button-large">
                        <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span>
                        <?php _e('Crea Nuovo Utente', 'area-riservata'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=ar-colors'); ?>" class="button button-large">
                        <span class="dashicons dashicons-art" style="vertical-align: middle;"></span>
                        <?php _e('Personalizza Colori', 'area-riservata'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=ar-audit'); ?>" class="button button-large">
                        <span class="dashicons dashicons-list-view" style="vertical-align: middle;"></span>
                        <?php _e('Vedi Log Attività', 'area-riservata'); ?>
                    </a>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <?php if (!empty($recent_activity)): ?>
            <div class="ar-recent-activity" style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2><?php _e('Attività Recente', 'area-riservata'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Data/Ora', 'area-riservata'); ?></th>
                            <th><?php _e('Utente', 'area-riservata'); ?></th>
                            <th><?php _e('Azione', 'area-riservata'); ?></th>
                            <th><?php _e('IP', 'area-riservata'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_activity as $log): ?>
                        <tr>
                            <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->timestamp)); ?></td>
                            <td><?php echo esc_html($log->display_name ? $log->display_name : $log->user_email); ?></td>
                            <td><code><?php echo esc_html($log->action); ?></code></td>
                            <td><?php echo esc_html($log->ip_address); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <!-- Help Section -->
            <div class="ar-help" style="background: #f0f6fc; padding: 24px; border-radius: 8px; border-left: 4px solid #2271b1; margin: 20px 0;">
                <h3 style="margin-top: 0;">
                    <span class="dashicons dashicons-info" style="vertical-align: middle;"></span>
                    <?php _e('Come Usare il Plugin', 'area-riservata'); ?>
                </h3>
                <ol style="line-height: 1.8;">
                    <li><strong><?php _e('Crea le pagine WordPress:', 'area-riservata'); ?></strong> <?php _e('Registrazione, Login, Area Riservata, Area Admin con gli shortcode appropriati', 'area-riservata'); ?></li>
                    <li><strong><?php _e('Crea un Portal Admin:', 'area-riservata'); ?></strong> <?php _e('Vai su Utenti > Aggiungi nuovo e seleziona il ruolo "Admin Area Riservata"', 'area-riservata'); ?></li>
                    <li><strong><?php _e('Gestisci dal frontend:', 'area-riservata'); ?></strong> <?php _e('I Portal Admin gestiscono tutto dalla pagina /area-admin/ (non hanno accesso a wp-admin)', 'area-riservata'); ?></li>
                    <li><strong><?php _e('Tu come Administrator:', 'area-riservata'); ?></strong> <?php _e('Hai accesso completo sia al backend che al frontend per configurazione e supervisione', 'area-riservata'); ?></li>
                </ol>
                <p>
                    <a href="<?php echo AR_PLUGIN_URL . 'INSTALL.md'; ?>" class="button" target="_blank">
                        <?php _e('📖 Leggi la Guida Completa', 'area-riservata'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render users page
     */
    public function render_users_page() {
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        
        if ($status === 'all') {
            $users = get_users(array('role' => 'portal_user'));
        } else {
            $users = AR_Users::get_portal_users($status);
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Gestione Utenti Portale', 'area-riservata'); ?></h1>
            
            <ul class="subsubsub">
                <li><a href="?page=ar-users&status=all" <?php echo $status === 'all' ? 'class="current"' : ''; ?>><?php _e('Tutti', 'area-riservata'); ?></a> |</li>
                <li><a href="?page=ar-users&status=pending" <?php echo $status === 'pending' ? 'class="current"' : ''; ?>><?php _e('Pendenti', 'area-riservata'); ?></a> |</li>
                <li><a href="?page=ar-users&status=approved" <?php echo $status === 'approved' ? 'class="current"' : ''; ?>><?php _e('Approvati', 'area-riservata'); ?></a> |</li>
                <li><a href="?page=ar-users&status=rejected" <?php echo $status === 'rejected' ? 'class="current"' : ''; ?>><?php _e('Rifiutati', 'area-riservata'); ?></a></li>
            </ul>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Utente', 'area-riservata'); ?></th>
                        <th><?php _e('Email', 'area-riservata'); ?></th>
                        <th><?php _e('Stato', 'area-riservata'); ?></th>
                        <th><?php _e('Data Registrazione', 'area-riservata'); ?></th>
                        <th><?php _e('Azioni', 'area-riservata'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): 
                        $user_status = AR_Users::get_user_status($user->ID);
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($user->first_name . ' ' . $user->last_name); ?></strong></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td>
                            <?php
                            $badge_color = array(
                                'pending' => '#f0b849',
                                'approved' => '#00a32a',
                                'rejected' => '#d63638',
                                'disabled' => '#646970'
                            );
                            ?>
                            <span style="padding: 4px 12px; border-radius: 12px; background: <?php echo $badge_color[$user_status]; ?>; color: white; font-size: 12px; font-weight: 600;">
                                <?php echo esc_html(ucfirst($user_status)); ?>
                            </span>
                        </td>
                        <td><?php echo date_i18n(get_option('date_format'), strtotime($user->user_registered)); ?></td>
                        <td>
                            <a href="<?php echo get_edit_user_link($user->ID); ?>" class="button button-small"><?php _e('Modifica', 'area-riservata'); ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p style="margin-top: 20px;">
                <strong><?php _e('Nota:', 'area-riservata'); ?></strong> 
                <?php _e('Per approvare/rifiutare utenti, usa l\'Area Admin dal frontend oppure modifica manualmente lo user meta "ar_user_status".', 'area-riservata'); ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render documents page
     */
    public function render_documents_page() {
        $documents_instance = AR_Documents::get_instance();
        $all_documents = $documents_instance->get_all_documents();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Gestione Documenti', 'area-riservata'); ?></h1>
            
            <p><?php _e('Tutti i documenti caricati dagli admin del portale.', 'area-riservata'); ?></p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Nome File', 'area-riservata'); ?></th>
                        <th><?php _e('Assegnato a', 'area-riservata'); ?></th>
                        <th><?php _e('Dimensione', 'area-riservata'); ?></th>
                        <th><?php _e('Data Caricamento', 'area-riservata'); ?></th>
                        <th><?php _e('Caricato da', 'area-riservata'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_documents as $doc): 
                        $uploader = get_user_by('id', $doc->uploaded_by);
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($doc->original_filename); ?></strong></td>
                        <td><?php echo esc_html($doc->display_name ? $doc->display_name : $doc->user_email); ?></td>
                        <td><?php echo size_format($doc->filesize); ?></td>
                        <td><?php echo date_i18n(get_option('date_format'), strtotime($doc->upload_date)); ?></td>
                        <td><?php echo $uploader ? esc_html($uploader->display_name) : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render audit page
     */
    public function render_audit_page() {
        $audit = AR_Audit::get_instance();
        $logs = $audit->get_recent_activity(100);
        
        ?>
        <div class="wrap">
            <h1><?php _e('Log Attività', 'area-riservata'); ?></h1>
            
            <p><?php _e('Ultimi 100 eventi registrati dal sistema.', 'area-riservata'); ?></p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Data/Ora', 'area-riservata'); ?></th>
                        <th><?php _e('Utente', 'area-riservata'); ?></th>
                        <th><?php _e('Azione', 'area-riservata'); ?></th>
                        <th><?php _e('Documento ID', 'area-riservata'); ?></th>
                        <th><?php _e('IP Address', 'area-riservata'); ?></th>
                        <th><?php _e('Dettagli', 'area-riservata'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->timestamp)); ?></td>
                        <td><?php echo esc_html($log->display_name ? $log->display_name : $log->user_email); ?></td>
                        <td><code><?php echo esc_html($log->action); ?></code></td>
                        <td><?php echo $log->document_id ? '#' . $log->document_id : '-'; ?></td>
                        <td><?php echo esc_html($log->ip_address); ?></td>
                        <td>
                            <?php if ($log->details): ?>
                            <details>
                                <summary style="cursor: pointer;">Vedi dettagli</summary>
                                <pre style="margin-top: 8px; padding: 8px; background: #f6f7f7; border-radius: 4px; font-size: 11px;"><?php echo esc_html($log->details); ?></pre>
                            </details>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for auto-setup pages
     */
    public function ajax_auto_setup_pages() {
        // Verify nonce
        check_ajax_referer('ar_auto_setup', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permesso negato', 'area-riservata')));
        }
        
        // Pages to create
        $pages = array(
            array(
                'title' => __('Registrazione', 'area-riservata'),
                'slug' => 'registrazione',
                'content' => '[ar_register]',
                'template' => ''
            ),
            array(
                'title' => __('Login', 'area-riservata'),
                'slug' => 'login',
                'content' => '[ar_login]',
                'template' => ''
            ),
            array(
                'title' => __('Area Riservata', 'area-riservata'),
                'slug' => 'area-riservata',
                'content' => '[ar_dashboard]',
                'template' => ''
            ),
            array(
                'title' => __('Area Admin', 'area-riservata'),
                'slug' => 'area-admin',
                'content' => '[ar_admin]',
                'template' => ''
            ),
            array(
                'title' => __('Reset Password', 'area-riservata'),
                'slug' => 'password-reset',
                'content' => '[ar_password_reset]',
                'template' => ''
            )
        );
        
        $created = array();
        $skipped = array();
        
        foreach ($pages as $page_data) {
            // Check if page already exists
            $existing_page = get_page_by_path($page_data['slug']);
            
            if ($existing_page && strpos($existing_page->post_content, $page_data['content']) !== false) {
                $skipped[] = $page_data['title'];
                continue;
            }
            
            // Create the page
            $page_id = wp_insert_post(array(
                'post_title' => $page_data['title'],
                'post_name' => $page_data['slug'],
                'post_content' => $page_data['content'],
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => get_current_user_id(),
                'comment_status' => 'closed',
                'ping_status' => 'closed'
            ));
            
            if ($page_id && !is_wp_error($page_id)) {
                $created[] = $page_data['title'];
                
                // Set template if specified
                if (!empty($page_data['template'])) {
                    update_post_meta($page_id, '_wp_page_template', $page_data['template']);
                }
            }
        }
        
        // Prepare response
        $message = '';
        $details = '';
        
        if (!empty($created)) {
            $message = sprintf(
                __('Creazione completata! %d pagine create con successo.', 'area-riservata'),
                count($created)
            );
            $details = __('Pagine create: ', 'area-riservata') . implode(', ', $created);
        } else {
            $message = __('Nessuna pagina creata. Tutte le pagine esistono già.', 'area-riservata');
            $details = __('Pagine già esistenti: ', 'area-riservata') . implode(', ', $skipped);
        }
        
        wp_send_json_success(array(
            'message' => $message,
            'details' => $details,
            'created' => $created,
            'skipped' => $skipped
        ));
    }
}
