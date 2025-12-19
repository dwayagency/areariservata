<?php
/**
 * Elementor Login Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Elementor_Login_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'ar_login';
    }
    
    public function get_title() {
        return __('Area Riservata - Login', 'area-riservata');
    }
    
    public function get_icon() {
        return 'eicon-lock-user';
    }
    
    public function get_categories() {
        return array('area-riservata');
    }
    
    public function get_keywords() {
        return array('login', 'accesso', 'area riservata', 'auth');
    }
    
    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Contenuto', 'area-riservata'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );
        
        $this->add_control(
            'widget_description',
            array(
                'label' => __('Descrizione', 'area-riservata'),
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => __('Questo widget mostra il form di login per l\'Area Riservata.', 'area-riservata'),
                'content_classes' => 'elementor-descriptor',
            )
        );
        
        $this->end_controls_section();
        
        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Stile', 'area-riservata'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_control(
            'style_note',
            array(
                'label' => __('Nota', 'area-riservata'),
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => __('Gli stili sono controllati dalle impostazioni del plugin. Puoi personalizzare i colori da Impostazioni > Area Riservata Colori.', 'area-riservata'),
            )
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        echo do_shortcode('[ar_login]');
    }
    
    protected function content_template() {
        ?>
        <div class="ar-elementor-preview">
            <div style="padding: 40px; background: #f0f0f0; border: 2px dashed #999; border-radius: 8px; text-align: center;">
                <span class="dashicons dashicons-lock-user" style="font-size: 48px; color: #666;"></span>
                <h3 style="margin: 16px 0 8px; color: #333;"><?php _e('Form Login Area Riservata', 'area-riservata'); ?></h3>
                <p style="margin: 0; color: #666;"><?php _e('Il form di login apparirà qui nel frontend', 'area-riservata'); ?></p>
            </div>
        </div>
        <?php
    }
}
