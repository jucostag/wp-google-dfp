<?php
namespace AdsGoogleDFP\Admin;

use AdsGoogleDFP\TwigViewer;

class ExtraScripts
{
    public function registerSettings()
    {
        add_action('admin_init', function (){
            register_setting('gdfp-settings-group', 'extrascripts');
        });
    }

    public function renderPage()
    {
        $content = [
            'settings_fields' => $this->getHTML('settings_fields', 'gdfp-settings-group'),
            'do_sections' => $this->getHTML('do_settings_sections', 'gdfp-settings-group'),
            'extrascripts_field' => esc_attr(get_option('extrascripts')),
            'submit_button' => $this->getHTML('submit_button', ''),
        ];

        echo TwigViewer::render("admin/gdfp_extra_scripts_panel.html", $content);        
    }

    private function getHTML($function, $param)
    {
        ob_start();

        if (!empty($param)) {
            call_user_func($function, $param);
        } else {
            call_user_func($function);
        }

        $html = ob_get_contents();
        ob_end_clean();
        return $html;    
    }
}