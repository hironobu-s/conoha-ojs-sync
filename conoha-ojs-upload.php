<?php

/*
Plugin Name: ConoHa ObjectStorage Upload
Plugin URI: http://www.conoha.jp/
Description: UploadデータをConoHaオブジェクトストレージに同期するプラグイン
Author: Hironobu Saitoh
Version: 0.1
Author URI: http://twitter.com/hironobu_s
*/

// SDKをロード
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once dirname(__FILE__) . "/vendor/rackspace/php-opencloud/lib/OpenCloud/OpenStack.php";

// OpenCloud\Openstack名前空間を使う
use OpenCloud\Openstack;
use Guzzle\Http\Exception\ClientErrorResponseException;


add_action('admin_menu', 'add_pages');
add_action('admin_init', 'conohaojs_options' );

function add_pages() {
    //add_menu_page('テキスト設定','テキスト設定',  'level_8', __FILE__, 'option_page', '', 26);
    add_submenu_page('options-general.php', "ConoHa Object Store", "ConoHa Object Store", 8, __FILE__, 'option_page');
}

function option_page() {
    wp_enqueue_script('conohaojs-script', plugins_url( '/script/conohaojs.js' , __FILE__ ), array( 'jquery' ), '1.2.4',true);
    
    wp_enqueue_style('conohaojs-style', plugins_url('style/conohaojs.css', __FILE__));
    include "tpl/setting.php";
}


function conohaojs_options()
{
    register_setting('conohaojs-options', 'conohaojs-username', 'strval');
    register_setting('conohaojs-options', 'conohaojs-password', 'strval');
    register_setting('conohaojs-options', 'conohaojs-tenant-id', 'strval');
    register_setting('conohaojs-options', 'conohaojs-container', 'strval');
    //register_setting('conohaojs-options', 'conohaojs-localpath', 'strval');
    register_setting('conohaojs-options', 'conohaojs-auth-url', 'esc_url');
    register_setting('conohaojs-options', 'conohaojs-region', 'strval');
    register_setting('conohaojs-options', 'conohaojs-delafter', 'boolval');
}

function __sanitize_require($val) {
    
}



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

function conohaojs_upload($file_id) {
    
    $username = get_option('conohaojs-username');
    $password = get_option('conohaojs-password');
    $tenant_id = get_option('conohaojs-tenant-id');
    $auth_url = get_option('conohaojs-auth-url');
    $region = get_option('conohaojs-region');
    
    $container_name = get_option('conohaojs-container');
    $local_path = get_option('conohaojs-localpath');

    // Get container
    $service = __getObjectStoreService($username, $password, $tenant_id, $auth_url, $region);
    try {
        $container = $service->getContainer($container_name);
    } catch(\Guzzle\Http\Exception\ClientErrorResponseException $ex) {
        // Container dose not found.
        $service->createContainer($container_name);
    }
    try {
        $container = $service->getContainer($container_name);
    } catch(\Guzzle\Http\Exception\ClientErrorResponseException $ex) {
        error_log("Can not create the container.");
        return false;
    }

    // Upload file
    $file = get_attached_file($file_id);
    if(is_readable($file)) {
        $fp = fopen($file, 'r');
        $object_name = basename($file);
        $container->uploadObject($object_name, $fp);
    }

    // unlink local file if delete_after option is true.
    $object = $container->getObject($object_name);
    if(
        $object instanceof OpenCloud\ObjectStore\Resource\DataObject &&
        get_option('conohaojs-delafter') == 1
    ) {
        @unlink($file);
    }
    
    return true;
}


function __getObjectStoreService($username, $password, $tenant_id, $auth_url, $region) {
    $client = new Openstack(
        //'https://ident-r1nd1001.cnode.jp/v2.0',
        $auth_url,
        array(
            'tenantId' => $tenant_id,
            'username' => $username,
            'password' => $password,
        )
    );

    return $client->objectStoreService('swift', $region);
}


add_action('wp_ajax_conohaojs_connect_test', 'conohaojs_connect_test');
add_action('add_attachment', 'conohaojs_upload', 10, 1);

add_action('admin_footer', 'admin_footer_example');
function admin_footer_example () {
    $k = get_option('');
    var_dump($k);
}