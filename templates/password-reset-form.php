<?php
/**
 * Template: Password Reset Form
 */
?>

<div class="ar-password-reset-form">
    <div class="area-riservata">
<div class="ar-form-container">
        <h2><?php _e('Reset Password', 'area-riservata'); ?></h2>
        
        <p style="text-align: center; color: var(--ar-gray); margin-bottom: 24px;">
            <?php _e('Inserisci la tua email per ricevere il link di reset della password.', 'area-riservata'); ?>
        </p>
        
        <form id="ar-reset-password-form" method="post">
            <div class="ar-form-group">
                <label for="ar-reset-email"><?php _e('Email', 'area-riservata'); ?></label>
                <input type="email" id="ar-reset-email" name="email" required>
            </div>
            
            <div class="ar-form-messages"></div>
            
            <button type="submit" class="ar-btn ar-btn-primary" style="width: 100%;">
                <?php _e('Invia Link Reset', 'area-riservata'); ?>
            </button>
        </form>
        
        <p class="ar-form-footer">
            <a href="<?php echo home_url('/login/'); ?>"><?php _e('Torna al login', 'area-riservata'); ?></a>
        </p>
    </div>
</div>
