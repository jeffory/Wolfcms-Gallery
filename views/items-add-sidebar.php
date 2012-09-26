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

<div class="box">
    <h2><?php echo __(singularise(GAL_TITLE)) ?> items</h2>
    <p>
        <b>Please note:</b> When editing an item, if you delete a category it only deletes it's association with the item, the category will need to be <a href="<?php echo URL_PUBLIC. 'admin/plugin/'. GAL_URL. '/categories' ?>">deleted seperately</a>.
    </p>
</div>