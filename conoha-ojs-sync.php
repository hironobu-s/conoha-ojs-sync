<?php

/**
 * Plugin Name: ConoHa Object Sync
 * Plugin URI: http://www.conoha.jp/
 * Description: This WordPress plugin allows you to upload files from the library to ConoHa Object Storage or other OpenStack Swift-based Object Store.
 * Author: Hironobu Saitoh
 * Author URI: https://github.com/hironobu-s
 * Version: 0.1
 * License: MIT
*/

// Load SDKs
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once dirname(__FILE__) . "/vendor/rackspace/php-opencloud/lib/OpenCloud/OpenStack.php";

// use OpenCloud\Openstack namespace
use OpenCloud\Openstack;
use Guzzle\Http\Exception\ClientErrorResponseException;


function add_pages() {
    add_submenu_page('options-general.php', "ConoHa Object Store", "ConoHa Object Sync", 8, __FILE__, 'option_page');
}

function option_page() {
    wp_enqueue_script('conohaojs-script', plugins_url( '/script/conohaojs.js' , __FILE__ ), array( 'jquery' ), '1.2.4',true);
    
    wp_enqueue_style('conohaojs-style', plugins_url('style/conohaojs.css', __FILE__));
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
}

// Connection test
function conohaojs_connect_test()
{
    $username = '';
    if(isset($_POST['username'])) {
        $username = $_POST['username'];
    }

    $password = '';
    if(isset($_POST['password'])) {
        $password = $_POST['password'];
    }
    

    $tenant_id = '';
    if(isset($_POST['tenantId'])) {
        $tenant_id = $_POST['tenantId'];
    }

    $auth_url = '';
    if(isset($_POST['authUrl'])) {
        $auth_url = $_POST['authUrl'];
    }
    
    $region = '';
    if(isset($_POST['region'])) {
        $region = $_POST['region'];
    }
    
    try {
        $ojs = __getObjectStoreService($username, $password, $tenant_id, $auth_url, $region);
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
function conohaojs_upload($file_id) {
    $file = get_attached_file($file_id);
    return __uploadObject($file);
}

function conohaojs_object_storage_url($wpurl) {
    $path = parse_url($wpurl, PHP_URL_PATH);
    $filename = basename($path);
    $container_name = get_option('conohaojs-container');
    return get_option("conohaojs-endpoint-url") . '/' . $container_name . '/' .  $filename;
}


// WordPress hooks
add_action('admin_menu', 'add_pages');
add_action('admin_init', 'conohaojs_options' );
add_action('wp_ajax_conohaojs_connect_test', 'conohaojs_connect_test');
add_action('add_attachment', 'conohaojs_upload', 10, 1);
add_filter('wp_generate_attachment_metadata', 'selupload_thumbUpload', 10, 1);
add_filter('wp_get_attachment_url', 'conohaojs_object_storage_url' );


// -------------------- internal functions -------------------- 


function __uploadObject($filepath) {
    $container_name = get_option('conohaojs-container');

    // Get container
    $service = __getObjectStoreService();

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
        
        // Set container ACL
        $headers = array(
            'X-Web-Mode' => 'true',
            'X-Container-Read' => '.r:*,.rlistings'
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


function __getObjectStoreService($username = null,
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

