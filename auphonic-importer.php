<?php

/*
  Plugin Name: Auphonic Importer
  Plugin URI: http://spyndle.com/auphonic-importer-for-wordpress/
  Description: This plugin will import the productions that come through from Auphonic.com as posts into WordPress
  Version: 1.5.1
  Author: Kreg Steppe
  Author URI: http://www.spyndle.com
  License: GPLv2 or later
 */

/*
  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (isset($_GET['ai_update'])) {
    $options = get_option("ai_options");
    if ($options['ai_update_password'] == $_GET['ai_update']) {
        add_action('init', 'ai_read_auphonic');
    }
}

function ai_read_auphonic() {

    global $wpdb;

    $options = get_option("ai_options");

    if ($options['ai_enabled'] == "Yes") {       
        if ($options['ai_username'] != "" && $options['ai_password'] != "") {

            $url = "https://auphonic.com/api/productions.json?limit=10";
            $username = $options['ai_username'];
            $password = $options['ai_password'];
            $tag = $options['ai_tag'];
            $remove_tag = $options['ai_remove_tag'];
            $user = $options['ai_user'];
            $status = $options['ai_status'];
            $category = $options['ai_category'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $output = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);

            if ($info['http_code'] == "200" && isset($output)) {

                $output = json_decode($output, true);
                foreach ($output['data'] as $production) {
                    $tags = $production['metadata']['tags'];
                    if ($production['status_string'] == "Done") {

                        if (in_array($tag, $tags) || $tag == '') {
                            foreach ($production['outgoing_services'] as $key => $service) {

                                foreach ($service['result_urls'] as $result_url) {
                                    $query = "select * from " . $wpdb->prefix . "auphonic_importer where filename = '" . $result_url . "'";
                                    $results = $wpdb->get_row( $query );
                                    if ($results === null){
                                        if (stristr($result_url, ".m")) {
                                            $enclosure = $result_url . "\n";
                                            $enclosure .= $production['output_files'][$key]['size'] . "\n";
                                            $enclosure .= "audio/mpeg";
                                        }
                                    }
                                }
                                if (isset($enclosure)) {
                                    break;
                                }
                            }

                            if (isset($enclosure)) {

                                $post = array(
                                    'post_title' => $production['metadata']['title'],
                                    'post_content' => $production['metadata']['summary'],
                                    'post_status' => $status,
                                    'post_type' => 'post',
                                    'post_author' => $user,
                                    'post_parent' => 0,
                                    'menu_order' => 0,
                                    'to_ping' => '',
                                    'pinged' => '',
                                    'post_password' => '',
                                    'guid' => '',
                                    'post_content_filtered' => '',
                                    'post_excerpt' => '',
                                    'import_id' => 0
                                );

                                $post_ID = wp_insert_post($post, $wp_error);
                                wp_set_post_terms($post_ID, array($category), 'category');
                                update_post_meta($post_ID, "enclosure", $enclosure);

                                if ($tags) {
                                    if ($remove_tag) {
                                        $tags = array_diff($tags, array($tag));
                                    }
                                    wp_set_post_tags($post_ID, $tags);
                                }

                                /* Log Each new Enclosure */
                                $query = "insert into " . $wpdb->prefix . "auphonic_importer set filename = '" . $result_url . "'";
                                $wpdb->query($query);
                            }
                            unset($enclosure, $url);
                        }
                    }
                }
            }
            update_option("ai_lastUpdated", date("Y-m-d H:i:s", mktime() - 14400));
        }
    }
}

/* adding Menu for Option */
add_action('admin_menu', 'ai_admin_menu');

function ai_admin_menu() {
    $page_title = 'Auphonic Importer Admin Settings';
    $menu_title = 'Auphonic Importer';
    $capability = 'manage_options';
    $menu_slug = 'ai-settings';
    $function = 'ai_settings';
    add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
}

function ai_settings() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    $options = get_option("ai_options");
    if ($options['ai_initialized'] != "yes") {
        include( plugin_dir_path(__FILE__) . 'init.php');
    } else {
        include( plugin_dir_path(__FILE__) . 'settings.php');
    }
}

if (isset($_POST['ai_save_options'])) {
    $options = array("ai_username" => $_POST['ai_username'],
        "ai_password" => $_POST['ai_password'],
        "ai_tag" => $_POST['ai_tag'],
        "ai_remove_tag" => $_POST['ai_remove_tag'],
        "ai_enabled" => $_POST['ai_enabled'],
        "ai_category" => $_POST['ai_category'],
        "ai_user" => $_POST['ai_user'],
        "ai_initialized" => "yes",
        "ai_status" => $_POST['ai_status'],
        "ai_update_password" => $_POST['ai_update_password']);
    update_option("ai_options", $options);
}

if (isset($_POST['ai_init'])) {
    global $wpdb;
    $table_name = $wpdb->prefix . "auphonic_importer";

    $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
            `production_ID` int(11) NOT NULL AUTO_INCREMENT,
            `filename` text NOT NULL,
            `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `production_ID` (`production_ID`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);

    $options = array("ai_username" => "",
        "ai_password" => "",
        "ai_tag" => "",
        "ai_enabled" => "",
        "ai_category" => "",
        "ai_user" => "",
        "ai_initialized" => "yes",
        "ai_status" => "",
        "ai_update_password" => "yes");
    update_option("ai_options", $options);
}

if (isset($_GET['ai_clear_cache'])) {
    global $wpdb;
    $table_name = $wpdb->prefix . "auphonic_importer";

    $wpdb->query("truncate table " . $table_name);
}

function ai_show($data) {
    print "<pre>";
    print_r($data);
    print "</pre>";
}