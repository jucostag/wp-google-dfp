<?php
namespace AdsGoogleDFP\Models;

use AdsGoogleDFP\Config\Config;

class WpGoogleDFP
{
    protected $config;
    private $wpdb;
    private $prefix;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->prefix = "{$wpdb->prefix}google_dfp_format_";
        $this->config = new Config();
    }

    protected function insert($table, $data)
    {
        $table = "{$this->prefix}{$table}";
        
        return $this->wpdb->insert($table, $data);
    }

    protected function update($table, $formData, $id)
    {
        $table = "{$this->prefix}{$table}";
        
        return $this->wpdb->update($table, $formData, [
            'ID' => $id
        ]);
    }

    protected function delete($table, $id)
    {
        $table = "{$this->prefix}{$table}";
        
        return $this->wpdb->delete($table, [
            'ID' => $id,
        ]);
    }

    protected function getFormatGroupByUrl($url)
    {
        $url = $this->normalizeUrl($url);
        $query = $this->wpdb->prepare("SELECT groups.group_formats
        FROM {$this->prefix}group_url AS urls
        LEFT JOIN {$this->prefix}groups AS groups
        ON urls.group_id = groups.ID
        WHERE urls.slot_url = %s", $url);

        $results = $this->wpdb->get_results($query);

        if (is_null($results)) {
            return false;
        }

        $results = array_shift($results);
        
        return unserialize($results->group_formats);
    }

    protected function getFormatGroups($paginate)
    {
        if ($paginate) {
            $pagination = $this->paginate();
        }

        $query = "SELECT * FROM {$this->prefix}groups
            ORDER BY group_name
            $pagination";

        $groups = $this->wpdb->get_results($query);

        if (false === $groups) {
            return [];
        }

        return array_map(function($group){
            if (isset($group->group_formats)) {
                $group->group_formats = unserialize($group->group_formats);
            }
            return $group;
        }, $groups);
    }

    protected function getUrlFormats()
    {
        $pagination = $this->paginate();

        $query = "SELECT 
            urls.ID as url_id, 
            urls.slot_url,
            urls.group_id,
            groups.ID as format_group_id,
            groups.group_name,
            groups.group_formats
            FROM {$this->prefix}group_url AS urls
            LEFT JOIN {$this->prefix}groups AS groups
            ON urls.group_id = groups.ID
            WHERE urls.slot_url NOT LIKE '%/*'";

        if (isset($_GET['filter'])) {
            $keywords = $this->wpdb->_real_escape($_GET['filter']);
            $query .= " AND urls.slot_url LIKE '%{$keywords}%'";
        }

        $query .= " ORDER BY slot_url {$pagination}";
        $urls = $this->wpdb->get_results($query);

        if (false === $urls) {
            return [];
        }

        return array_map(function($url){
            if (isset($url->group_formats)) {
                $url->group_formats = unserialize($url->group_formats);
                $url->content = $this->getURL("{$url->slot_url}/*");
            }
            return $url;
        }, $urls);
    }

    protected function getURL($url)
    {
        $query = "SELECT 
            urls.ID as url_id, 
            urls.slot_url,
            urls.group_id,
            groups.ID as format_group_id,
            groups.group_name,
            groups.group_formats
            FROM {$this->prefix}group_url AS urls
            LEFT JOIN {$this->prefix}groups AS groups
            ON urls.group_id = groups.ID
            WHERE urls.slot_url = '$url'";

        $urls = $this->wpdb->get_results($query);

        if (false === $urls) {
            return [];
        }

        return array_shift(array_map(function($url){
            if (isset($url->group_formats)) {
                $url->group_formats = unserialize($url->group_formats);
            }
            return $url;
        }, $urls));
    }

    protected function urlExists($url)
    {
        $url = $this->normalizeUrl($url);
        
        $query = "SELECT slot_url FROM {$this->prefix}group_url
            WHERE slot_url = '{$url}'";

        $urls = $this->wpdb->get_results($query);

        return count($urls) > 0;
    }

    protected function formatGroupExists(array $formData)
    {
        $query = "SELECT group_name FROM {$this->prefix}groups
            WHERE group_name = '{$formData['group_name']}'
            OR group_formats = '{$formData['group_formats']}'";

        $groups = $this->wpdb->get_results($query);
        
        return count($groups) > 0;
    }

    protected function paginate()
    {
        $page = $this->getPage();
        $start = ($page == 1) ? 0 : (($start + ($this->perPage * $page)) - $this->perPage);

        return "LIMIT $start , {$this->perPage}";
    }

    protected function getTotalResults($table)
    {
        $query = "SELECT COUNT('ID') FROM {$this->prefix}{$table}";

        if (isset($_GET['filter']) && $table == 'group_url') {
            $keywords = $this->wpdb->_real_escape($_GET['filter']);
            $query .= " WHERE slot_url LIKE '%{$keywords}%'";
        }

        return $this->wpdb->get_var($query);
    }

    protected function normalizeUrl($url)
    {
        return str_replace('//', '/', $url);
    }

    protected function standardizeURL($url)
    {
        return preg_replace('/\s+|^\/|\/$/', '', strtolower($url));
    }

    protected function isInContentEnabled()
    {
        $panelOption = get_option('gdfp_in_content_format');

        return $panelOption === 'true';
    }
}