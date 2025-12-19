<?php
/**
 * Color customization settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Colors {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_head', array($this, 'output_custom_colors'), 1);
        add_action('wp_ajax_ar_reset_colors', array($this, 'ajax_reset_colors'));
    }
    
    /**
     * Add settings page to WordPress admin
     */
    public function add_settings_page() {
        add_options_page(
            __('Area Riservata - Colori', 'area-riservata'),
            __('Area Riservata Colori', 'area-riservata'),
            'manage_options',
            'ar-colors',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('ar_colors_group', 'ar_primary_hue', array(
            'type' => 'integer',
            'default' => 210,
            'sanitize_callback' => array($this, 'sanitize_hue')
        ));
        
        register_setting('ar_colors_group', 'ar_primary_saturation', array(
            'type' => 'integer',
            'default' => 100,
            'sanitize_callback' => array($this, 'sanitize_percentage')
        ));
        
        register_setting('ar_colors_group', 'ar_primary_lightness', array(
            'type' => 'integer',
            'default' => 50,
            'sanitize_callback' => array($this, 'sanitize_percentage')
        ));
    }
    
    /**
     * Sanitize hue value (0-360)
     */
    public function sanitize_hue($value) {
        $value = intval($value);
        return max(0, min(360, $value));
    }
    
    /**
     * Sanitize percentage value (0-100)
     */
    public function sanitize_percentage($value) {
        $value = intval($value);
        return max(0, min(100, $value));
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        $hue = get_option('ar_primary_hue', 210);
        $saturation = get_option('ar_primary_saturation', 100);
        $lightness = get_option('ar_primary_lightness', 50);
        
        $preview_color = "hsl($hue, $saturation%, $lightness%)";
        
        ?>
        <div class="wrap">
            <h1><?php _e('Personalizzazione Colori - Area Riservata', 'area-riservata'); ?></h1>
            <p><?php _e('Personalizza il colore primario del plugin modificando i valori HSL (Hue, Saturation, Lightness)', 'area-riservata'); ?></p>
            
            <form method="post" action="options.php" id="ar-colors-form">
                <?php settings_fields('ar_colors_group'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ar_primary_hue"><?php _e('Tonalità (Hue)', 'area-riservata'); ?></label>
                        </th>
                        <td>
                            <input type="range" id="ar_primary_hue" name="ar_primary_hue" 
                                   value="<?php echo esc_attr($hue); ?>" 
                                   min="0" max="360" step="1" 
                                   style="width: 300px;">
                            <span id="hue-value" style="margin-left: 10px; font-weight: bold;"><?php echo $hue; ?>°</span>
                            <p class="description">
                                <?php _e('0-360 (Rosso=0, Verde=120, Blu=240)', 'area-riservata'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ar_primary_saturation"><?php _e('Saturazione (Saturation)', 'area-riservata'); ?></label>
                        </th>
                        <td>
                            <input type="range" id="ar_primary_saturation" name="ar_primary_saturation" 
                                   value="<?php echo esc_attr($saturation); ?>" 
                                   min="0" max="100" step="1" 
                                   style="width: 300px;">
                            <span id="saturation-value" style="margin-left: 10px; font-weight: bold;"><?php echo $saturation; ?>%</span>
                            <p class="description">
                                <?php _e('0-100 (0=grigio, 100=colore pieno)', 'area-riservata'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ar_primary_lightness"><?php _e('Luminosità (Lightness)', 'area-riservata'); ?></label>
                        </th>
                        <td>
                            <input type="range" id="ar_primary_lightness" name="ar_primary_lightness" 
                                   value="<?php echo esc_attr($lightness); ?>" 
                                   min="0" max="100" step="1" 
                                   style="width: 300px;">
                            <span id="lightness-value" style="margin-left: 10px; font-weight: bold;"><?php echo $lightness; ?>%</span>
                            <p class="description">
                                <?php _e('0-100 (0=nero, 50=normale, 100=bianco)', 'area-riservata'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <?php _e('Anteprima Colore', 'area-riservata'); ?>
                        </th>
                        <td>
                            <div id="color-preview" style="
                                width: 200px;
                                height: 100px;
                                border-radius: 12px;
                                background: <?php echo $preview_color; ?>;
                                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                                transition: all 0.3s ease;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: white;
                                font-weight: bold;
                                font-size: 16px;
                            ">
                                Area Riservata
                            </div>
                            <p class="description">
                                <strong><?php _e('Valore HSL:', 'area-riservata'); ?></strong> 
                                <span id="hsl-value">hsl(<?php echo "$hue, $saturation%, $lightness%"; ?>)</span>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Salva Modifiche', 'area-riservata')); ?>
                
                <button type="button" id="ar-reset-colors" class="button button-secondary">
                    <?php _e('Ripristina Colori Predefiniti', 'area-riservata'); ?>
                </button>
            </form>
            
            <hr>
            
            <h2><?php _e('Preset Colori', 'area-riservata'); ?></h2>
            <p><?php _e('Clicca su un preset per applicarlo rapidamente:', 'area-riservata'); ?></p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 16px; margin: 20px 0;">
                <?php
                $presets = array(
                    array('name' => 'Blu Classico', 'hue' => 210, 'sat' => 100, 'light' => 50),
                    array('name' => 'Verde Smeraldo', 'hue' => 150, 'sat' => 80, 'light' => 45),
                    array('name' => 'Viola Elegante', 'hue' => 280, 'sat' => 70, 'light' => 55),
                    array('name' => 'Rosso Rubino', 'hue' => 0, 'sat' => 85, 'light' => 50),
                    array('name' => 'Arancio Vibrante', 'hue' => 30, 'sat' => 95, 'light' => 55),
                    array('name' => 'Ciano Moderno', 'hue' => 180, 'sat' => 90, 'light' => 50),
                    array('name' => 'Rosa Delicato', 'hue' => 330, 'sat' => 75, 'light' => 60),
                    array('name' => 'Turchese', 'hue' => 170, 'sat' => 60, 'light' => 50),
                );
                
                foreach ($presets as $preset) {
                    $preset_color = "hsl({$preset['hue']}, {$preset['sat']}%, {$preset['light']}%)";
                    ?>
                    <button type="button" class="ar-color-preset" 
                            data-hue="<?php echo $preset['hue']; ?>"
                            data-sat="<?php echo $preset['sat']; ?>"
                            data-light="<?php echo $preset['light']; ?>"
                            style="
                                padding: 20px;
                                border: 2px solid #ddd;
                                border-radius: 12px;
                                background: <?php echo $preset_color; ?>;
                                color: white;
                                font-weight: bold;
                                cursor: pointer;
                                transition: all 0.3s;
                                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                            ">
                        <?php echo esc_html($preset['name']); ?>
                    </button>
                    <?php
                }
                ?>
            </div>
        </div>
        
        <style>
            .ar-color-preset:hover {
                transform: translateY(-4px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.2) !important;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Live preview
            function updatePreview() {
                var hue = $('#ar_primary_hue').val();
                var sat = $('#ar_primary_saturation').val();
                var light = $('#ar_primary_lightness').val();
                
                var color = 'hsl(' + hue + ', ' + sat + '%, ' + light + '%)';
                
                $('#color-preview').css('background', color);
                $('#hsl-value').text('hsl(' + hue + ', ' + sat + '%, ' + light + '%)');
                $('#hue-value').text(hue + '°');
                $('#saturation-value').text(sat + '%');
                $('#lightness-value').text(light + '%');
            }
            
            $('#ar_primary_hue, #ar_primary_saturation, #ar_primary_lightness').on('input', updatePreview);
            
            // Preset buttons
            $('.ar-color-preset').on('click', function() {
                var hue = $(this).data('hue');
                var sat = $(this).data('sat');
                var light = $(this).data('light');
                
                $('#ar_primary_hue').val(hue);
                $('#ar_primary_saturation').val(sat);
                $('#ar_primary_lightness').val(light);
                
                updatePreview();
            });
            
            // Reset button
            $('#ar-reset-colors').on('click', function() {
                if (confirm('<?php _e('Sei sicuro di voler ripristinare i colori predefiniti?', 'area-riservata'); ?>')) {
                    $('#ar_primary_hue').val(210);
                    $('#ar_primary_saturation').val(100);
                    $('#ar_primary_lightness').val(50);
                    updatePreview();
                    
                    // Submit form
                    $('#ar-colors-form').submit();
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Output custom colors in frontend
     */
    public function output_custom_colors() {
        $hue = get_option('ar_primary_hue', 210);
        $saturation = get_option('ar_primary_saturation', 100);
        $lightness = get_option('ar_primary_lightness', 50);
        
        ?>
        <style id="ar-custom-colors">
            :root {
                --ar-primary-h: <?php echo $hue; ?>;
                --ar-primary-s: <?php echo $saturation; ?>%;
                --ar-primary-l: <?php echo $lightness; ?>%;
            }
        </style>
        <?php
    }
    
    /**
     * AJAX reset colors
     */
    public function ajax_reset_colors() {
        check_ajax_referer('ar_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permesso negato', 'area-riservata')));
        }
        
        update_option('ar_primary_hue', 210);
        update_option('ar_primary_saturation', 100);
        update_option('ar_primary_lightness', 50);
        
        wp_send_json_success(array('message' => __('Colori ripristinati', 'area-riservata')));
    }
}
