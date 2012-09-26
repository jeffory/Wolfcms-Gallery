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
    <a href="<?php echo BASE_URI. 'plugin/'. GAL_URL ?>/add">
        <img src='<?php echo URI_PUBLIC ?>/wolf/icons/add-page-32.png' align="middle" alt="snippet icon">
        Add item
    </a>
</p>

<p class='button' style='height: 34px;'>
    <img src='<?php echo URI_PUBLIC ?>/wolf/icons/file-folder-32.png' align="middle" alt="snippet icon">
    <a href='<?php echo URL_PUBLIC. 'admin/plugin/'. GAL_URL. '/categories' ?>'>
        Edit categories
    </a>
</p>

<div class="box">
    <h2><?php echo __(singularise(GAL_TITLE)) ?> items</h2>
    <p>
        All the items in the <?php echo __(GAL_TITLE) ?> are listed here for editing and browsing. Holding <strong>CTRL</strong> brings up more advanced manupulation options if needed.
    </p>
</div>

<?php if (DEBUG): ?>
<div class="box">
    <h2>Debugging options:</h2>

    <p>Only use these functions if you're sure what you're doing.</p>

    <p class='button'>
        <a href="<?php echo BASE_URI. 'plugin/'. GAL_URL ?>/addsamples">
            <img src='<?php echo URI_PUBLIC ?>/wolf/icons/open-32.png' align="middle" alt="snippet icon">
            Add randomized sample data
        </a>
    </p>

    <p class='button'>
        <a href="<?php echo BASE_URI. 'plugin/'. GAL_URL ?>/clearall" onclick="return confirm('Are you sure you wish to delete all the data in the gallery?');">
            <img src='<?php echo URI_PUBLIC ?>/wolf/icons/delete-32.png' align="middle" alt="snippet icon">
            Clear all data!
        </a>
    </p>
</div>
<?php endif; ?>