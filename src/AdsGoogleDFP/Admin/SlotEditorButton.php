<?php
namespace AdsGoogleDFP\Admin;

class SlotEditorButton
{

    public function create()
    {
        if ($this->isUserAllowed()) {
            add_filter('mce_external_plugins', [$this, 'addPlugin']);
            add_filter('mce_buttons', [$this, 'registerBtn']);
        }
    }

    private function isUserAllowed()
    {
        return get_user_option('rich_editing') && current_user_can('edit_posts') && current_user_can('edit_pages');
    }
    
    public function addPlugin(array $plugin)
    {
        $plugin['gdfp_slot'] = GDFPURL . '/assets/admin/gdfpSlotShortcodeBtn.js';
        return $plugin;
    }

    public function registerBtn(array $buttons)
    {
        array_push($buttons, "|", "gdfp_slot");
        return $buttons;
    }
}