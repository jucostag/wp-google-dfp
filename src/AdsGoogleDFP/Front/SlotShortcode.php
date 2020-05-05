<?php
namespace AdsGoogleDFP\Front;

use AdsGoogleDFP\TwigViewer;
use AdsGoogleDFP\Models\WpGoogleDFP;

class SlotShortcode extends WpGoogleDFP
{
    public $attributes;

    public function create($attributes)
    {
        ob_start();

        $this->setAttributes($attributes);        
        $this->render();

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function setAttributes($attributes)
    {
        $this->attributes = shortcode_atts([
            'area' => '',
            'hide' => 'no',
            'format' => '',
            'before' => '',
            'ad_class' => '',
            'title_class' => '',
        ], $attributes);

        $this->attributes['area'] = strtolower($this->attributes['area']);
    }

    public function render()
    {
        if(false === $this->isInContentEnabled() && $this->attributes->format == 'in-content') {
            return;
        }

        echo TwigViewer::render("gdfp_slot.html", $this->attributes);
    }
}