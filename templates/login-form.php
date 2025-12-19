<div class="ar-login-form">
    <h2><?php _e('Accedi', 'area-riservata'); ?></h2>
    
    <form id="ar-login-form" method="post">
        <div class="ar-form-group">
            <label for="ar-login-email"><?php _e('Email', 'area-riservata'); ?></label>
            <input type="email" id="ar-login-email" name="email" required>
        </div>
        
        <div class="ar-form-group">
            <label for="ar-login-password"><?php _e('Password', 'area-riservata'); ?></label>
            <input type="password" id="ar-login-password" name="password" required>
        </div>
        
        <div class="ar-form-group">
            <label>
                <input type="checkbox" name="remember" value="1">
                <?php _e('Ricordami', 'area-riservata'); ?>
            </label>
        </div>
        
        <div class="ar-form-messages"></div>
        
        <button type="submit" class="ar-btn ar-btn-primary">
            <?php _e('Accedi', 'area-riservata'); ?>
        </button>
    </form>
    
    <p class="ar-form-footer">
        <?php _e('Non hai un account?', 'area-riservata'); ?>
        <a href="<?php echo home_url('/registrazione/'); ?>"><?php _e('Registrati', 'area-riservata'); ?></a>
    </p>
</div>
