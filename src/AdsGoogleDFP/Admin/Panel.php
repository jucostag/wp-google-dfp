<?php
namespace AdsGoogleDFP\Admin;

use AdsGoogleDFP\TwigViewer;
use AdsGoogleDFP\Models\WpGoogleDFP;

class Panel extends WpGoogleDFP
{
    public $perPage = 5;
    public $panelUrl = '/wp-admin/admin.php?page=';
    public $panelSlug = 'google-dfp';
    public $icon = 'dashicons-megaphone';
    public $permission = 'administrator';
    public $actionStatus;

    public function init()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueuePanelDeps']);
        add_menu_page('Google DFP Publicidade', 'Google DFP Publicidade', $this->permission, $this->panelSlug, [$this, 'renderUrlsPage'], $this->icon, 31);
        add_submenu_page($this->panelSlug, 'URLs', 'URLs', $this->permission, 'google-dfp-urls', [$this, 'renderUrlsPage']);
        add_submenu_page($this->panelSlug, 'Grupos de formatos', 'Grupos de formatos', $this->permission, 'google-dfp-grupos-de-formatos', [$this, 'renderFormatGroupsPage']);
        add_submenu_page($this->panelSlug, 'Como funciona?', 'Como funciona?', $this->permission, 'google-dfp-docs', [$this, 'renderDocsPage']);
    }

    public function enqueuePanelDeps()
    {
        wp_register_style('google-dfp-admin-panel', GDFPURL . '/assets/admin/adminPanel.min.css');
        wp_register_script('google-dfp-admin-panel', GDFPURL . '/assets/admin/adminPanel.min.js');
        wp_enqueue_style('google-dfp-admin-panel');
        wp_enqueue_script('google-dfp-admin-panel');
    }

    public function renderUrlsPage()
    {
        $this->doFormAction('google-dfp-urls');

        $actionStatus = 'none';
        if(isset($_POST['action_status']) && !is_null($_POST['action_status'])) {
            $actionStatus = $_POST['action_status'];
        }

        $content = [
            'action_status' => $actionStatus,
            'prefixo' => $this->config->getPrefix(),
            'grupos_formatos' => $this->getFormatGroups(false),            
            'urls' => $this->getUrlFormats(),
            'paginacao' => $this->getPaginationLinks('group_url', 'google-dfp-urls'),
        ];

        echo TwigViewer::render("admin/panel/urls.html", $content);
    }

    public function renderFormatGroupsPage()
    {
        $this->doFormAction('google-dfp-grupos-de-formatos');
        
        $content = [
            'action_status' => $_POST['action_status'],
            'grupos_formatos' => $this->getFormatGroups(true),
            'paginacao' => $this->getPaginationLinks('groups', 'google-dfp-grupos-de-formatos'),
        ];

        echo TwigViewer::render("admin/panel/format_groups.html", $content);
    }

    public function renderDocsPage()
    {        
        $content = [
            'config' => $this->config->getPostTypesMapping(),
        ];

        echo TwigViewer::render("admin/panel/docs.html", $content);
    }

    private function doFormAction($page)
    {
        if (!isset($_POST['action']) || is_null($_POST['action'])) {
            return;
        }

        $action = $_POST['action'];
        unset($_POST['action']);

        if (false !== $table) {
            switch ($action){
                case ('create_urls'):
                    $this->insertURLs($action, $_POST);
                    break;
                case ('create_formats_group'):
                    $this->insertFormatsGroup($action, $_POST);
                    break;
                case ('edit_formats_group'):
                    $this->editFormatsGroup($action, $_POST);
                    break;
                case ('edit_url'):
                    $this->editURL($action, $_POST);
                    break;
                case('create_url'):
                    $this->insertHierarchyURL($action, $_POST);
                    break;
                case ('delete_cascade'):
                    $this->deleteCascadeURL($action, $_POST);
                    break;
                case ('delete'):
                    $delete = $this->delete('group_url', $_POST['ID']);
                    $this->setActionStatus($action, $delete);
                    break;
            }
        }
    }

    private function setActionStatus($action, $queryStatus)
    {
        $_POST['action_status'][$action] = $queryStatus;
    }

    private function insertURLs($action, $formData)
    {
        $formData['slot_url'] = $this->removeEmptyURLFields($formData['slot_url']);
        $formatGroup = $formData['group_id'];
        $status = [];

        foreach ($formData['slot_url'] as $url) {
            $url = $this->standardizeURL($url);

            if (false === $this->urlExists($url)) {
                $data = [
                    'slot_url' => $url,
                    'group_id' => $formatGroup,
                ];

                $status[$url] = $this->insert('group_url', $data);
            } else {
                $status[$url] = 'duplicated';
            }
        }
	
        $this->setActionStatus($action, $status);
    }

    private function deleteCascadeURL($action, $formData)
    {
        $delete['url'] = $this->delete('group_url', $formData['ID']);

        if (!empty($formData['related_ID'])) {
            $delete['related_url'] = $this->delete('group_url', $formData['related_ID']);            
        }

        $this->setActionStatus($action, $delete);        
    }

    private function editURL($action, $formData)
    {
        $formData['slot_url'] = $this->normalizeURL($formData['slot_url']);
        $update = $this->update('group_url', $formData, $formData['ID']);

        $this->setActionStatus($action, $update);
        return;
    }

    private function removeEmptyURLFields(array $slotURLs)
    {
        return array_filter($slotURLs, function($value) { 
            return $value !== ''; 
        });
    }

    private function editFormatsGroup($action, $formData)
    {
        $formData = $this->standardizeGroupFormat($formData);        

        if (false === $this->formatGroupExists($formData)) {
            $update = $this->update('groups', $formData, $formData['ID']);
            $this->setActionStatus($action, $update);
            return;
        }

        $this->setActionStatus($action, 'duplicated');
    }

    private function insertFormatsGroup($action, $formData)
    {
        $formData = $this->standardizeGroupFormat($formData);

        if (false === $this->formatGroupExists($formData)) {
            $insert = $this->insert('groups', $formData);
            $this->setActionStatus($action, $insert);
            return;
        }

        $this->setActionStatus($action, 'duplicated');
    }

    private function standardizeGroupFormat(array $group)
    {
        $group['group_name'] = $this->generateGroupName($group['group_formats']);
        $group['group_formats'] = serialize($group['group_formats']);

        return $group;
    }

    private function generateGroupName(array $formats)
    {
        $countFormats = $this->countFormats($formats);
        $name = array_reduce(array_map(function ($format, $count){
            if ($count > 0) {
                $plural = ($count == 1) ? '' : 's';
                return [
                    $format => "{$count} {$format}{$plural}",
                ];
            }
        }, array_keys($countFormats), $countFormats), 'array_merge', []);

        return implode(',', $name);
    }

    private function countFormats(array $formats)
    {
        return array_count_values(array_map(function ($format){
            return preg_replace('/[0-9]+/', '', $format);
        }, $formats));
    }

    public function getPaginationLinks($table, $page)
    {
        $total = $this->getTotalResults($table);
        $url = "{$this->panelUrl}{$page}&page_num=";
        if (isset($_GET['filter'])) {
            $url = "{$this->panelUrl}{$page}&filter={$_GET['filter']}&page_num=";            
        }
        $pageNumber = $this->getPage();

        $lastPage = ceil($total / $this->perPage);
        $lastPageNum = ($lastPage < 1) ? 1 : $lastPage;
        $nextPage = ($pageNumber == $lastPage) ? $lastPage : ($pageNumber + 1);
        $previousPage = ($pageNumber > 1) ? ($pageNumber - 1) : 1;
        $disableLinkStyle = 'style="pointer-events: none; cursor: default;"';
        $isLinkDisabled = ($lastPage > 1) ? '' : $disableLinkStyle;

        return [
            'total' => $total,
            'total_paginas' => $lastPageNum,
            'pagina_atual' => $pageNumber,
            'primeira_pagina' => "{$url}1",
            'ultima_pagina' => $url . $lastPageNum,
            'anterior' => $url . $previousPage,
            'proxima' => $url . $nextPage,
        ];
    }

    public function getPage()
    {
        $page = 1;
        
        if (isset($_GET['page_num']) && !is_null($_GET['page_num'])) {
            $page = (int)$_GET['page_num'];
        }

        return $page;
    }
}