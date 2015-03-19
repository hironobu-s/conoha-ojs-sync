<?php

/**
 * Plugin Name: ConoHa Object Sync
 * Plugin URI: https://github.com/hironobu-s/conoha-ojs-sync
 * Description: This WordPress plugin allows you to upload files from the library to ConoHa Object Storage or other OpenStack Swift-based Object Store.
 * Author: Hironobu Saitoh
 * Author URI: https://github.com/hironobu-s
 * Text Domain: conoha-ojs-sync
 * Version: 0.1
 * License: GPLv2
*/

// Text Domain
load_plugin_textdomain('conoha-ojs-sync', false, basename(dirname(__FILE__)). DIRECTORY_SEPARATOR . 'lang');

// Load SDKs
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'rackspace' . DIRECTORY_SEPARATOR . 'php-opencloud' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'OpenCloud' . DIRECTORY_SEPARATOR . 'OpenStack.php';

// use OpenCloud\Openstack namespace
use OpenCloud\Openstack;
use Guzzle\Http\Exception\ClientErrorResponseException;

function add_pages() {
    add_submenu_page('options-general.php', "ConoHa Object Store", "ConoHa Object Sync", 8, __FILE__, 'option_page');
}

function option_page() {
    wp_enqueue_script('conohaojs-script', plugins_url( '/script/conohaojs.js' , __FILE__ ), array( 'jquery' ), '1.2.4',true);

    wp_enqueue_style('conohaojs-style', plugins_url('style/conohaojs.css', __FILE__));

    // Default options
    if (get_option('conohaojs-region') == null) {
        update_option('conohaojs-region', 'RegionOne');
    }

    include "tpl/setting.php";
}


function conohaojs_options()
{
    // Informations for API authentication.
    register_setting('conohaojs-options', 'conohaojs-username', 'strval');
    register_setting('conohaojs-options', 'conohaojs-password', 'strval');
    register_setting('conohaojs-options', 'conohaojs-tenant-id', 'strval');
    register_setting('conohaojs-options', 'conohaojs-auth-url', 'esc_url');
    register_setting('conohaojs-options', 'conohaojs-region', 'strval');

    // Container name that media files will be uploaded.
    register_setting('conohaojs-options', 'conohaojs-container', 'strval');

    // Synchronization option.
    register_setting('conohaojs-options', 'conohaojs-delafter', 'boolval');
    register_setting('conohaojs-options', 'conohaojs-delobject', 'boolval');
}

// Connection test
function conohaojs_connect_test()
{
    $username = '';
    if(isset($_POST['username'])) {
        $username = sanitize_text_field($_POST['username']);
    }

    $password = '';
    if(isset($_POST['password'])) {
        $password = sanitize_text_field($_POST['password']);
    }

    $tenant_id = '';
    if(isset($_POST['tenantId'])) {
        $tenant_id = sanitize_text_field($_POST['tenantId']);
    }

    $auth_url = '';
    if(isset($_POST['authUrl'])) {
        $auth_url = sanitize_url($_POST['authUrl']);
    }

    $region = '';
    if(isset($_POST['region'])) {
        $region = sanitize_text_field($_POST['region']);
    }

    try {
        $ojs = __get_object_store_service($username, $password, $tenant_id, $auth_url, $region);
        echo json_encode(array(
                             'message' => "Connection was Successfully.",
                             'is_error' => false,
                     ));
        exit;

    } catch(Exception $ex) {
        echo json_encode(array(
                             'message' => "ERROR: ".$ex->getMessage(),
                             'is_error' => true,
                     ));
        exit;
    }
}

// Upload a media file.
function conohaojs_upload_file($file_id) {
    $path = get_attached_file($file_id);
    return __upload_object($path);
}

// Upload thumbnails
function conohaojs_thumb_upload($metadatas) {
    $dir = wp_upload_dir();
    foreach($metadatas['sizes'] as $thumb) {
        $file = $dir['path'] . DIRECTORY_SEPARATOR . $thumb['file'];
        if( ! __upload_object($file)) {
            throw new Exception("upload error");
        }
    }

    return $metadatas;
}

// Delete an object
function conohaojs_delete_object($filepath) {
    return __delete_object($filepath);
}


// Return object URL
function conohaojs_object_storage_url($wpurl) {
    $path = parse_url($wpurl, PHP_URL_PATH);
    $object_name = basename($wpurl);

    $container_name = get_option('conohaojs-container');
    $url = get_option("conohaojs-endpoint-url") . '/' . $container_name . '/' .  $object_name;
    return $url;
}

// add date prefix to the filename.
function conohaojs_modify_uploadfilename($file){
    $dir = wp_upload_dir();
    $prefix = str_replace($dir['basedir'] . DIRECTORY_SEPARATOR, '', $dir['path']);
    $prefix = str_replace(DIRECTORY_SEPARATOR, '-', $prefix);
    $file['name'] = $prefix . '-' . $file['name'];
    return $file;
}

// -------------------- WordPress hooks --------------------

add_action('admin_menu', 'add_pages');
add_action('admin_init', 'conohaojs_options' );
add_action('wp_ajax_conohaojs_connect_test', 'conohaojs_connect_test');

add_action('add_attachment', 'conohaojs_upload_file');
add_filter('wp_generate_attachment_metadata', 'conohaojs_thumb_upload');

if(get_option("conohaojs-delobject") == 1) {
    add_filter('wp_delete_file', 'conohaojs_delete_object');
}

add_filter('wp_handle_upload_prefilter', 'conohaojs_modify_uploadfilename' );

add_filter('wp_get_attachment_url', 'conohaojs_object_storage_url');


// -------------------- internal functions --------------------

// generate the object name from the filepath.
function __generate_object_name_from_path($path) {
    $dir = wp_upload_dir();
    $name = $path;
    $name = str_replace($dir['basedir'] . DIRECTORY_SEPARATOR, '', $name);
    $name = str_replace(DIRECTORY_SEPARATOR, '-', $name);
    return $name;
}

function __generate_object_name_from_url($url) {
    $dir = wp_upload_dir();
    $name = $url;
    $name = str_replace($dir['baseurl'] . '/', '', $name);
    $name = str_replace('/', '-', $name);
    return $name;
}


function __upload_object($filepath) {
    $container_name = get_option('conohaojs-container');

    // Get container
    $service = __get_object_store_service();

    $created = false;
    try {
        $container = $service->getContainer($container_name);
    } catch(\Guzzle\Http\Exception\ClientErrorResponseException $ex) {
        // Create container if it was not found.
        $service->createContainer($container_name);
        $created = true;
    }

    if($created) {
        try {
            $container = $service->getContainer($container_name);
        } catch(\Guzzle\Http\Exception\ClientErrorResponseException $ex) {
            error_log("Can not create the container.");
            return false;
        }

    }

    // Set container ACL
    $prop = $container->getMetadata()->getProperty("read");
    if(strpos($prop, '.r:*') === false) {
        $headers = array(
            'X-Web-Mode' => 'true',
            'X-Container-Read' => '.r:*'
        );
        $url = $service->getUrl($container_name);
        $cli = $service->getClient();
        $cli->put($url, $headers)
            ->send();
    }

    // Upload file
    if(is_readable($filepath)) {
        $fp = fopen($filepath, 'r');
        $object_name = basename($filepath);
        $container->uploadObject($object_name, $fp);
    } else {
        return true;
    }

    // unlink local file if delete_after option is true.
    $object = $container->getObject($object_name);
    if(
        $object instanceof OpenCloud\ObjectStore\Resource\DataObject &&
        get_option('conohaojs-delafter') == 1
    ) {
        @unlink($filepath);
    }

    return true;
}

function __delete_object($filepath) {
    $container_name = get_option('conohaojs-container');

    // Get container
    $service = __get_object_store_service();

    try {
        $container = $service->getContainer($container_name);
    } catch(\Guzzle\Http\Exception\ClientErrorResponseException $ex) {
        error_log("container was not found.");
        return false;
    }

    $object_name = basename($filepath);
    try {
        $object = $container->getObject($object_name);
    } catch(Exception $ex) {
        // OK, Object does not already exists.
        return true;
    }

    $object->delete();

    return true;
}


function __get_object_store_service($username = null,
                                    $password = null,
                                    $tenant_id = null,
                                    $auth_url = null,
                                    $region = null ) {
    static $service = null;

    if( ! $service) {
        if($username == null) {
            $username = get_option('conohaojs-username');
        }
        if($password == null) {
            $password = get_option('conohaojs-password');
        }
        if($tenant_id == null) {
            $tenant_id = get_option('conohaojs-tenant-id');
        }
        if($auth_url == null) {
            $auth_url = get_option('conohaojs-auth-url');
        }
        if($region == null) {
            $region = get_option('conohaojs-region');
        }

        $client = new Openstack(
            $auth_url,
            array(
                'tenantId' => $tenant_id,
                'username' => $username,
                'password' => $password,
            )
        );

        $service = $client->objectStoreService('swift', $region);

        // Set endpoint URL to option
        update_option('conohaojs-endpoint-url', $service->getEndpoint()->getPublicUrl());
    }
    return $service;
}
