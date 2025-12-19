<?php
/**
 * Elementor Admin Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Elementor_Admin_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'ar_admin';
    }
    
    public function get_title() {
        return __('Area Riservata - Dashboard Admin', 'area-riservata');
    }
    
    public function get_icon() {
        return 'eicon-lock';
    }
    
    public function get_categories() {
        return array('area-riservata');
    }
    
    public function get_keywords() {
        return array('admin', 'gestione', 'area riservata', 'portal admin');
    }
    
    protected function register_controls() {
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
                'raw' => __('Questo widget mostra la dashboard di amministrazione per i Portal Admin. Visibile solo agli utenti con ruolo "Admin Area Riservata".', 'area-riservata'),
            )
        );
        
        $this->add_control(
            'important_note',
            array(
                'label' => __('Importante', 'area-riservata'),
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div style="background: #fff3cd; padding: 12px; border-left: 4px solid #ffc107; color: #856404;">' .
                         __('<strong>Nota:</strong> Questa è l\'area admin FRONTEND. I Portal Admin NON hanno accesso a /wp-admin.', 'area-riservata') .
                         '</div>',
            )
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        echo do_shortcode('[ar_admin]');
    }
    
    protected function content_template() {
        ?>
        <div class="ar-elementor-preview">
            <div style="padding: 40px; background: #f0f0f0; border: 2px dashed #999; border-radius: 8px; text-align: center;">
                <span class="dashicons dashicons-admin-generic" style="font-size: 48px; color: #666;"></span>
                <h3 style="margin: 16px 0 8px; color: #333;"><?php _e('Dashboard Admin (Frontend)', 'area-riservata'); ?></h3>
                <p style="margin: 0; color: #666;"><?php _e('L\'area admin per Portal Admin apparirà qui', 'area-riservata'); ?></p>
                <p style="margin: 8px 0 0; color: #999; font-size: 12px;"><?php _e('Solo per utenti con ruolo "Admin Area Riservata"', 'area-riservata'); ?></p>
            </div>
        </div>
        <?php
    }
}
