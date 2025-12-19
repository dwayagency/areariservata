<div class="ar-registration-form">
    <div class="ar-form-container">
        <h2><?php _e('Registrazione', 'area-riservata'); ?></h2>
        
        <form id="ar-register-form" method="post">
            <div class="ar-form-group">
                <label for="ar-first-name"><?php _e('Nome', 'area-riservata'); ?> *</label>
                <input type="text" id="ar-first-name" name="first_name" required>
            </div>
            
            <div class="ar-form-group">
                <label for="ar-last-name"><?php _e('Cognome', 'area-riservata'); ?> *</label>
                <input type="text" id="ar-last-name" name="last_name" required>
            </div>
            
            <div class="ar-form-group">
                <label for="ar-email"><?php _e('Email', 'area-riservata'); ?> *</label>
                <input type="email" id="ar-email" name="email" required>
            </div>
            
            <div class="ar-form-group">
                <label for="ar-password"><?php _e('Password', 'area-riservata'); ?> *</label>
                <input type="password" id="ar-password" name="password" required minlength="8">
                <small><?php _e('Minimo 8 caratteri', 'area-riservata'); ?></small>
            </div>
            
            <div class="ar-form-group">
                <label for="ar-password-confirm"><?php _e('Conferma Password', 'area-riservata'); ?> *</label>
                <input type="password" id="ar-password-confirm" name="password_confirm" required>
            </div>
            
            <div class="ar-form-group">
                <label>
                    <input type="checkbox" name="privacy" required>
                    <?php _e('Accetto la privacy policy', 'area-riservata'); ?> *
                </label>
            </div>
            
            <div class="ar-form-messages"></div>
            
            <button type="submit" class="ar-btn ar-btn-primary" style="width: 100%;">
                <?php _e('Registrati', 'area-riservata'); ?>
            </button>
        </form>
        
        <p class="ar-form-footer">
            <?php _e('Hai già un account?', 'area-riservata'); ?>
            <a href="<?php echo home_url('/login/'); ?>"><?php _e('Accedi', 'area-riservata'); ?></a>
        </p>
    </div>
</div>
