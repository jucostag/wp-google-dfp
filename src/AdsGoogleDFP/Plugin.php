<?php
namespace AdsGoogleDFP;

use AdsGoogleDFP\Config\Config;
use AdsGoogleDFP\Admin\Panel as AdminPanel;
use AdsGoogleDFP\Admin\SlotEditorButton;
use AdsGoogleDFP\Config\Database as DatabaseConfig;
use AdsGoogleDFP\Front\SlotShortcode;
use AdsGoogleDFP\Front\Header;
use AdsGoogleDFP\TwigViewer;

class Plugin extends Config
{
    public $slotShortcode;
    public $dbConfig;
    public $editorBtn;
    public $googleDFPUrl;
    public $header;

    public function __construct()
    {
        $this->adminPanel = new AdminPanel();
        $this->slotShortcode = new SlotShortcode();
        $this->dbConfig = new DatabaseConfig();
        $this->editorBtn = new SlotEditorButton();
        $this->header = new Header();
    }

    public function init()
    {
        $this->configDatabase();
        $this->configAdminPanel();
        $this->enqueueDeps();
        $this->registerHeader();
        $this->registerShortcodes();
        $this->registerWidget();
        $this->registerEditorBtn();
    }

    public function enqueueDeps()
    {
        add_action('wp_enqueue_scripts', function (){
            if (is_admin()) {
                return;
            }

            $this->registerDeps();
            
            wp_enqueue_style('google-dfp');
            wp_enqueue_script('google-dfp');
        });
    }

    public function registerDeps()
    {
        wp_register_style('google-dfp', GDFPURL . '/assets/dist/googleDFP.min.css');
        wp_register_script('google-dfp', GDFPURL . '/assets/dist/googleDFP.min.js', ['jquery'], '', true);
    }

    private function isGallery()
    {
        $postId = get_queried_object_id();

        return get_post_format($postId) == 'gallery';
    }

    public function configDatabase()
    {
        register_activation_hook(GDFPINDEX, [$this->dbConfig, 'init']);
    }

    public function registerHeader()
    {
        add_action("wp_head", [$this->header, 'injectGoogleDFPScript']);
    }

    public function registerWidget()
    {	
        add_action('widgets_init', function(){
            register_widget('AdsGoogleDFP\Admin\SlotWidget');
        });       
    }

    public function registerShortcodes()
    {
        add_shortcode('gdfp_slot', [$this->slotShortcode, 'create']);
    }

    public function registerEditorBtn()
    {
        add_action('admin_init', [$this->editorBtn, 'create']);
    }

    public function configAdminPanel()
    {
        add_action('admin_menu', [$this->adminPanel, 'init']);
    }

    private function hasCMB2()
    {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        return is_plugin_active('CMB2/init.php') || is_plugin_active('cmb2/init.php');
    }
}
