<?php
/**
 * Elementor Dashboard Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class AR_Elementor_Dashboard_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'ar_dashboard';
    }
    
    public function get_title() {
        return __('Area Riservata - Dashboard Utente', 'area-riservata');
    }
    
    public function get_icon() {
        return 'eicon-dashboard';
    }
    
    public function get_categories() {
        return array('area-riservata');
    }
    
    public function get_keywords() {
        return array('dashboard', 'documenti', 'area riservata', 'user');
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
                'raw' => __('Questo widget mostra la dashboard dell\'utente con i suoi documenti. Visibile solo agli utenti loggati e approvati.', 'area-riservata'),
            )
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        echo do_shortcode('[ar_dashboard]');
    }
    
    protected function content_template() {
        ?>
        <div class="ar-elementor-preview">
            <div style="padding: 40px; background: #f0f0f0; border: 2px dashed #999; border-radius: 8px; text-align: center;">
                <span class="dashicons dashicons-portfolio" style="font-size: 48px; color: #666;"></span>
                <h3 style="margin: 16px 0 8px; color: #333;"><?php _e('Dashboard Utente', 'area-riservata'); ?></h3>
                <p style="margin: 0; color: #666;"><?php _e('La dashboard con i documenti dell\'utente apparirà qui', 'area-riservata'); ?></p>
            </div>
        </div>
        <?php
    }
}
