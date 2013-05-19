<?php
/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * Simple, easy to setup and use gallery plugin for WolfCMS
 *
 * @package Plugins
 * @subpackage Gallery
 *
 * @author Keith McGahey
 */
?>
<p class='button'>
	<a href="<?php echo BASE_URI. 'plugin/'. GAL_URL. '/categories' ?>/add">
		<img src='<?php echo URI_PUBLIC ?>/wolf/icons/action-add-32-ns.png' align="middle" alt="snippet icon">
		<?php echo __('Add category') ?>
	</a>
</p>

<p class='button' style='height: 34px;'>
	<img src='<?php echo URI_PUBLIC ?>/wolf/icons/file-folder-32.png' align="middle" alt="snippet icon">
	<a href='<?php echo URL_PUBLIC. 'admin/plugin/'. GAL_URL. '/' ?>'>
		<?php echo __('Edit Items') ?>
	</a>
</p>

<div class="box">
    <h2>Gallery categories</h2>
    <p>
        All the categories are listed here for editing and browsing. Holding <strong>CTRL</strong> brings up more advanced manupulation options if needed.
    </p>
</div>