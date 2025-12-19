<?php
$pending_users = AR_Users::get_portal_users('pending');
$approved_users = AR_Users::get_portal_users('approved');
$documents_instance = AR_Documents::get_instance();
$all_documents = $documents_instance->get_all_documents();
$audit = AR_Audit::get_instance();
$recent_activity = $audit->get_recent_activity(10);
?>

<div class="area-riservata">
<div class="ar-admin-dashboard">
    <div class="ar-dashboard-header">
        <h2><?php _e('Area Admin - Gestione Portale', 'area-riservata'); ?></h2>
        <a href="#" class="ar-logout-btn ar-btn ar-btn-secondary"><?php _e('Logout', 'area-riservata'); ?></a>
    </div>
    
    <div class="ar-admin-tabs">
        <button class="ar-tab-btn active" data-tab="pending-users"><?php _e('Richieste Pendenti', 'area-riservata'); ?> <span class="ar-badge"><?php echo count($pending_users); ?></span></button>
        <button class="ar-tab-btn" data-tab="users"><?php _e('Utenti', 'area-riservata'); ?></button>
        <button class="ar-tab-btn" data-tab="documents"><?php _e('Documenti', 'area-riservata'); ?></button>
        <button class="ar-tab-btn" data-tab="upload"><?php _e('Carica Documento', 'area-riservata'); ?></button>
        <button class="ar-tab-btn" data-tab="create-user"><?php _e('Crea Utente', 'area-riservata'); ?></button>
        <button class="ar-tab-btn" data-tab="audit"><?php _e('Log Attività', 'area-riservata'); ?></button>
    </div>
    
    <!-- Tab: Pending Users -->
    <div class="ar-tab-content active" id="tab-pending-users">
        <h3><?php _e('Richieste di Registrazione Pendenti', 'area-riservata'); ?></h3>
        
        <?php if (empty($pending_users)): ?>
            <p><?php _e('Nessuna richiesta pendente.', 'area-riservata'); ?></p>
        <?php else: ?>
            <table class="ar-admin-table">
                <thead>
                    <tr>
                        <th><?php _e('Nome', 'area-riservata'); ?></th>
                        <th><?php _e('Email', 'area-riservata'); ?></th>
                        <th><?php _e('Data Registrazione', 'area-riservata'); ?></th>
                        <th><?php _e('Azioni', 'area-riservata'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_users as $user): ?>
                        <tr data-user-id="<?php echo $user->ID; ?>">
                            <td><?php echo esc_html($user->first_name . ' ' . $user->last_name); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($user->user_registered)); ?></td>
                            <td>
                                <button class="ar-btn ar-btn-small ar-btn-success ar-approve-user" data-user-id="<?php echo $user->ID; ?>">
                                    <?php _e('Approva', 'area-riservata'); ?>
                                </button>
                                <button class="ar-btn ar-btn-small ar-btn-danger ar-reject-user" data-user-id="<?php echo $user->ID; ?>">
                                    <?php _e('Rifiuta', 'area-riservata'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Tab: Users -->
    <div class="ar-tab-content" id="tab-users">
        <h3><?php _e('Utenti Approvati', 'area-riservata'); ?></h3>
        
        <?php if (empty($approved_users)): ?>
            <p><?php _e('Nessun utente approvato.', 'area-riservata'); ?></p>
        <?php else: ?>
            <table class="ar-admin-table">
                <thead>
                    <tr>
                        <th><?php _e('Nome', 'area-riservata'); ?></th>
                        <th><?php _e('Email', 'area-riservata'); ?></th>
                        <th><?php _e('Documenti', 'area-riservata'); ?></th>
                        <th><?php _e('Azioni', 'area-riservata'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approved_users as $user): 
                        $user_docs = $documents_instance->get_user_documents($user->ID);
                    ?>
                        <tr>
                            <td><?php echo esc_html($user->first_name . ' ' . $user->last_name); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo count($user_docs); ?> documenti</td>
                            <td>
                                <button class="ar-btn ar-btn-small ar-btn-warning ar-disable-user" data-user-id="<?php echo $user->ID; ?>">
                                    <?php _e('Disabilita', 'area-riservata'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Tab: Documents -->
    <div class="ar-tab-content" id="tab-documents">
        <h3><?php _e('Tutti i Documenti', 'area-riservata'); ?></h3>
        
        <?php if (empty($all_documents)): ?>
            <p><?php _e('Nessun documento caricato.', 'area-riservata'); ?></p>
        <?php else: ?>
            <table class="ar-admin-table">
                <thead>
                    <tr>
                        <th><?php _e('Nome File', 'area-riservata'); ?></th>
                        <th><?php _e('Utente', 'area-riservata'); ?></th>
                        <th><?php _e('Data Caricamento', 'area-riservata'); ?></th>
                        <th><?php _e('Dimensione', 'area-riservata'); ?></th>
                        <th><?php _e('Azioni', 'area-riservata'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_documents as $doc): ?>
                        <tr>
                            <td><?php echo esc_html($doc->original_filename); ?></td>
                            <td><?php echo esc_html($doc->display_name ? $doc->display_name : $doc->user_email); ?></td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($doc->upload_date)); ?></td>
                            <td><?php echo size_format($doc->filesize); ?></td>
                            <td>
                                <a href="<?php echo esc_url(AR_Download::generate_download_link($doc->id)); ?>" 
                                   class="ar-btn ar-btn-small ar-btn-primary">
                                    <?php _e('Scarica', 'area-riservata'); ?>
                                </a>
                                <button class="ar-btn ar-btn-small ar-btn-danger ar-delete-document" data-doc-id="<?php echo $doc->id; ?>">
                                    <?php _e('Elimina', 'area-riservata'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Tab: Upload Document -->
    <div class="ar-tab-content" id="tab-upload">
        <h3><?php _e('Carica Nuovo Documento', 'area-riservata'); ?></h3>
        
        <form id="ar-upload-document-form" enctype="multipart/form-data">
            <div class="ar-form-group">
                <label for="ar-upload-user"><?php _e('Assegna a utente', 'area-riservata'); ?> *</label>
                <select id="ar-upload-user" name="user_id" required>
                    <option value=""><?php _e('-- Seleziona utente --', 'area-riservata'); ?></option>
                    <?php foreach ($approved_users as $user): ?>
                        <option value="<?php echo $user->ID; ?>">
                            <?php echo esc_html($user->first_name . ' ' . $user->last_name . ' (' . $user->user_email . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="ar-form-group">
                <label for="ar-upload-file"><?php _e('Seleziona file', 'area-riservata'); ?> *</label>
                <input type="file" id="ar-upload-file" name="document" required>
                <small><?php _e('Formati consentiti: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, ZIP. Max 10MB', 'area-riservata'); ?></small>
            </div>
            
            <div class="ar-form-messages"></div>
            
            <button type="submit" class="ar-btn ar-btn-primary">
                <?php _e('Carica Documento', 'area-riservata'); ?>
            </button>
        </form>
    </div>
    
    <!-- Tab: Create User -->
    <div class="ar-tab-content" id="tab-create-user">
        <h3><?php _e('Crea Nuovo Utente', 'area-riservata'); ?></h3>
        
        <form id="ar-create-user-form">
            <div class="ar-form-group">
                <label for="ar-new-first-name"><?php _e('Nome', 'area-riservata'); ?> *</label>
                <input type="text" id="ar-new-first-name" name="first_name" required>
            </div>
            
            <div class="ar-form-group">
                <label for="ar-new-last-name"><?php _e('Cognome', 'area-riservata'); ?> *</label>
                <input type="text" id="ar-new-last-name" name="last_name" required>
            </div>
            
            <div class="ar-form-group">
                <label for="ar-new-email"><?php _e('Email', 'area-riservata'); ?> *</label>
                <input type="email" id="ar-new-email" name="email" required>
            </div>
            
            <div class="ar-form-group">
                <label for="ar-new-password"><?php _e('Password', 'area-riservata'); ?> *</label>
                <input type="password" id="ar-new-password" name="password" required minlength="8">
            </div>
            
            <div class="ar-form-group">
                <label>
                    <input type="checkbox" name="auto_approve" value="1" checked>
                    <?php _e('Approva automaticamente', 'area-riservata'); ?>
                </label>
            </div>
            
            <div class="ar-form-messages"></div>
            
            <button type="submit" class="ar-btn ar-btn-primary">
                <?php _e('Crea Utente', 'area-riservata'); ?>
            </button>
        </form>
    </div>
    
    <!-- Tab: Audit Log -->
    <div class="ar-tab-content" id="tab-audit">
        <h3><?php _e('Log delle Attività Recenti', 'area-riservata'); ?></h3>
        
        <?php if (empty($recent_activity)): ?>
            <p><?php _e('Nessuna attività registrata.', 'area-riservata'); ?></p>
        <?php else: ?>
            <table class="ar-admin-table">
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
                            <td><?php echo esc_html($log->action); ?></td>
                            <td><?php echo esc_html($log->ip_address); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</div>
