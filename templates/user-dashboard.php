<?php
$user_id = get_current_user_id();
$user = wp_get_current_user();
$documents_instance = AR_Documents::get_instance();
$documents = $documents_instance->get_user_documents($user_id);
?>

<div class="ar-user-dashboard">
    <div class="ar-dashboard-header">
        <h2><?php printf(__('Benvenuto, %s', 'area-riservata'), $user->first_name); ?></h2>
        <a href="#" class="ar-logout-btn ar-btn ar-btn-secondary"><?php _e('Logout', 'area-riservata'); ?></a>
    </div>
    
    <div class="ar-dashboard-content">
        <h3><?php _e('I Miei Documenti', 'area-riservata'); ?></h3>
        
        <?php if (empty($documents)): ?>
            <p class="ar-no-documents"><?php _e('Nessun documento disponibile al momento.', 'area-riservata'); ?></p>
        <?php else: ?>
            <table class="ar-documents-table">
                <thead>
                    <tr>
                        <th><?php _e('Nome File', 'area-riservata'); ?></th>
                        <th><?php _e('Data Caricamento', 'area-riservata'); ?></th>
                        <th><?php _e('Dimensione', 'area-riservata'); ?></th>
                        <th><?php _e('Azioni', 'area-riservata'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td>
                                <span class="ar-file-icon">📄</span>
                                <?php echo esc_html($doc->original_filename); ?>
                            </td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($doc->upload_date)); ?></td>
                            <td><?php echo size_format($doc->filesize); ?></td>
                            <td>
                                <a href="<?php echo esc_url(AR_Download::generate_download_link($doc->id)); ?>" 
                                   class="ar-btn ar-btn-small ar-btn-primary">
                                    <?php _e('Scarica', 'area-riservata'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
