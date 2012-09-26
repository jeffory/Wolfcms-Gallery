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
        <b>Please note:</b> When editing an item, if you delete a category it only delete's it's association, the category will need to be deleted seperately.
    </p>
</div>