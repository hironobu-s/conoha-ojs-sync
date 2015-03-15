
<div id="conohaojs-flash" class="updated">
		<p></p>
</div>

<h2><?php _e('Setting ConoHa Object Sync', "conoha-ojs-sync"); ?></h2>

<p><?php _e("Type the API informations for the ConoHa Object storage. No account? Let's ", 'conoha-ojs-sync'); ?><a href="<?php _e('https://www.conoha.jp/en/', 'conoha-ojs-sync'); ?>" target="_blank" ><?php _e('signup', 'conoha-ojs-sync'); ?></a></p>

<form method="post" action="options.php">
		<?php settings_fields('conohaojs-options'); ?>
		<?php do_settings_sections('conohaojs-options'); ?>
		<table>
				<tr>
						<th><?php _e('API Account', 'conoha-ojs-sync') ?>:</th>
						<td>
								<input id="conohaojs-username" name="conohaojs-username" type="text"
												size="15" value="<?php echo esc_attr(
																				 get_option('conohaojs-username')
																				 ); ?>" class="regular-text code"/>

						</td>
				</tr>
				<tr>
						<th><?php _e('API Password', 'conoha-ojs-sync') ?>:</th>
						<td>
								<input id="conohaojs-password" name="conohaojs-password" type="password"
												size="15" value="<?php echo esc_attr(
																				 get_option('conohaojs-password')
																				 ); ?>"  class="regular-text code"/>

						</td>
				</tr>
				<tr>
						<th><?php _e('Tenant ID', 'conoha-ojs-sync') ?>:</th>
						<td>
								<input id="conohaojs-tenant-id" name="conohaojs-tenant-id" type="text"
												size="15" value="<?php echo esc_attr(
																				 get_option('conohaojs-tenant-id')
																				 ); ?>" class="regular-text code"/>

						</td>
				</tr>
				<tr>
						<th><?php _e('Auth URL', 'conoha-ojs-sync') ?>:</th>
						<td>
								<input id="conohaojs-auth-url" name="conohaojs-auth-url" type="text"
												size="15" value="<?php echo esc_attr(
																				 get_option('conohaojs-auth-url')
																				 ); ?>" class="regular-text code"/>

						</td>
				</tr>
				<tr>
						<th><?php _e('Region', 'conoha-ojs-sync') ?>:</th>
						<td>
								<input id="conohaojs-region" name="conohaojs-region" type="text"
												size="15" value="<?php echo esc_attr(
																				 get_option('conohaojs-region')
																				 ); ?>" class="regular-text code"/>

						</td>
				</tr>
				<tr>
						<th><?php _e('Container Name', 'conoha-ojs-sync') ?>:</th>
						<td>
								<input id="conohaojs-container" name="conohaojs-container" type="text"
												size="15" value="<?php echo esc_attr(
																				 get_option('conohaojs-container')
																				 ); ?>" class="regular-text code"/>
								<p class="description"><?php _e('Container name that media files is uploaded. If the container not found, It will create automatically.', 'conoha-ojs-sync'); ?></p>
								<p class="conohaojs-warning"><?php _e('The plugin will set the ACL to allow public access.', 'conoha-ojs-sync'); ?></p>
						</td>
				</tr>
				<tr>
						<td colspan="2" style="padding-top: 1em">
                        <input type="button" name="test" id="submit" class="button button-primary"
                               value="<?php _e('Check the connection', 'conoha-ojs-sync'); ?>"
																onclick="conohaojs_connect_test()"/>
						</td>
				</tr>
		</table>

		<h3><?php _e('Synchronization options', 'conoha-ojs-sync'); ?></h3>
		<table>
				<tr>
						<td colspan="2">
                <input id="delete_after" type="checkbox" name="conohaojs-delafter"
                        value="1" <?php checked(get_option('conohaojs-delafter'),1); ?> />
                <label for="delete_after"><?php _e('Delete the uploaded file from the local storage after a successful upload to the object storage.', 'conoha-ojs-sync'); ?></label>
						</td>
				</tr>
				<tr>
						<td colspan="2">
                <input id="delobject" type="checkbox" name="conohaojs-delobject"
                        value="1" <?php checked(get_option('conohaojs-delobject'),1); ?> />
                <label for="delobject"><?php _e('Delete the object from the object storage when the library file is deleted.', 'conoha-ojs-sync'); ?></label>
						</td>
				</tr>
				<tr>
						<td colspan="2">
								<?php submit_button(); ?>
						</td>
				</tr>
		</table>

</form>
