<?php
namespace AdsGoogleDFP\Config;

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

class Database
{
    public $prefix;
    public $charset;

    public function __construct()
    {
        global $wpdb;

        $this->prefix = "{$wpdb->prefix}google_dfp_format_";
        $this->charset = $wpdb->get_charset_collate();
    }

    public function init()
    {
        try {
            $this->createAdsTable();
            $this->createFormatGroupsTable();
        } catch (Exception $e) {
            add_action('admin_notices', function () use ($e) {
                $this->showAdminNotice($e);
            });
        }
    }

    public function createAdsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->prefix}group_url (
            ID int(10) NOT NULL AUTO_INCREMENT,
            slot_url varchar(255) NOT NULL,
            group_id varchar(255) NOT NULL,
            slot_url_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            PRIMARY KEY  (ID)
        ) {$this->charset};";

        dbDelta($sql);
    }

    public function createFormatGroupsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->prefix}groups (
            ID int(10) NOT NULL AUTO_INCREMENT,
            group_name varchar(255) NOT NULL,
            group_formats varchar(255) NOT NULL,
            group_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            PRIMARY KEY  (ID)
        ) {$this->charset};";

        dbDelta($sql);
    }

    public function showAdminNotice($error)
    {
            echo <<<NOTICE
<div id="message" class="notice notice-error">
    <p>Erro ao criar a tabela de anúncios do plugin Google DFP Publicidade. <br><br> $error<p>
</div>
NOTICE;
    }
}