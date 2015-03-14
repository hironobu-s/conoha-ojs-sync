# WordPress plugin for ConoHa Object Storage.

This WordPress plugin allows you to upload files from the library to [ConoHa Object Storage](https://www.conoha.jp/en/) or OpenStack Swift-based Object Store. 

# Description

 * Plugin Name: ConoHa Object Sync
 * Plugin URI: https://github.com/hironobu-s/conoha-ojs-sync
 * Description: This WordPress plugin allows you to upload files from the library to ConoHa Object Storage or other OpenStack Swift-based Object Store.
 * Author: Hironobu Saitoh
 * Author URI: https://github.com/hironobu-s
 * Version: 0.1
 * License: MIT


## Install

1. Run the following command.
2. Activate the plugin through the 'Plugins' menu in WordPress

```bash

# Move into WordPress root
cd [WORDPRESS_ROOT]/wp-content/plugins

# Clone plugin repository
git clone https://github.com/hironobu-s/conoha-ojs-sync
cd conoha-ojs-sync

# Install PHP OpenCloud via composer 
curl -sS https://getcomposer.org/installer | php
./composer.phar install

```

# License

MIT License
