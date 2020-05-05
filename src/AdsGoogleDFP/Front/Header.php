<?php
namespace AdsGoogleDFP\Front;

use AdsGoogleDFP\TwigViewer;
use AdsGoogleDFP\Models\WpGoogleDFP;

class Header extends WpGoogleDFP
{
    public function printExtraScripts()
    {
        $extra = get_option("extrascripts");
    
        if (!empty($extra)) {
            echo $extra;
        }
    }

    public function printGoogleDFPScript()
    {
        $content = $this->createScriptContent();
        echo TwigViewer::render("gdfp_header.html", $content);
    }

    private function createScriptContent()
    {
        $prefix = $this->config->getPrefix();
        $autoPermalink = false;
        $inContentFormat = false;
        $url = $this->getFullUrl();

        if ($this->isMappedAsAutoPermalink()) {
            $autoPermalink = true;
            $url = $this->getSlotByHierarchy();
            $inContentFormat = $this->isInContentEnabled();
        }

        $formatos = $this->getAllowedFormats($url, $autoPermalink);
        $content = [
            'prefixo' => $prefix,
            'url' => $url,
            'formatos' => $formatos,
        ];

        if (false === $formatos) {
            $content['url'] = 'default';
            $content['formatos'] = [
                'middle',
                'top',
            ];
        }

        if ($inContentFormat) {
            array_push($content['formatos'], 'in-content'); 
        }
        
        return $content;
    }

    private function isMappedAsAutoPermalink() {
        $urlType = $this->getUrlType();
        
        if ('taxonomy' === $urlType) {
            return false;
        }

        $postTypesMapping = $this->config->getPostTypesMapping();

        return in_array($urlType, $postTypesMapping['map_as_auto_permalink']);
    }

    private function getSlotByHierarchy()
    {
        $currentUrl = $this->getUrlParts();
        
        if( empty(reset($currentUrl)) ){
            return "home";
        }

        $permalinkStructure = get_option('permalink_structure');
        $dateMask = '/%year%/%monthnum%/%day%/%postname%/';
        $urlType = $this->getUrlType();

        if( ($permalinkStructure === $dateMask) && ($urlType === 'post') ){
            array_splice($currentUrl, count($currentUrl) - 4, 4);
            return $this->getSlotByContentMapping($currentUrl);
        }

        array_pop($currentUrl);
        return $this->getSlotByContentMapping($currentUrl);
    }

    private function getSlotByContentMapping($currentUrl)
    {
        $parentUrl = implode("//", $currentUrl);
        $contentMappingUrl = "{$parentUrl}//*";

        if( $this->urlExists($contentMappingUrl) ) {
            return $contentMappingUrl;
        }

        return $parentUrl;
    }

    private function getUrlType()
    {
        $queriedObj = get_queried_object();

        if (!isset($queriedObj->post_type)) {
            return 'taxonomy';
        }

        return $queriedObj->post_type;
    }

    private function getAllowedFormats($url, $auto)
    {
        $url = str_replace('//', '/', $url);        
        
        if ($auto) {
            $hierarchyUrl = "{$url}/*";
            $hierarchyFormats = $this->getFormatGroupByUrl($hierarchyUrl);
                        
            if(!empty($hierarchyFormats)) {
                return $hierarchyFormats;
            }
        }

        return $this->getFormatGroupByUrl($url);
    }

    private function getFullUrl()
    {
        $currentUrl = $this->getUrlParts();

        if( empty(reset($currentUrl)) ){
            return 'home';
        }

        return implode('//', $currentUrl);
    }

    private function getUrlParts()
    {
        $currentUrl = strtok($_SERVER["REQUEST_URI"], '?');

        if (false !== strpos($currentUrl, '/page/')) {
            $currentUrl = preg_replace('/page.*/', '', $currentUrl);
        }

        return explode('/', trim($currentUrl, "/") );
    }
}