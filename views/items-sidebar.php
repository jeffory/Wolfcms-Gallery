<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * The skeleton plugin serves as a basic plugin template.
 *
 * This skeleton plugin makes use/provides the following features:
 * - A controller without a tab
 * - Three views (sidebar, documentation and settings)
 * - A documentation page
 * - A sidebar
 * - A settings page (that does nothing except display some text)
 * - Code that gets run when the plugin is enabled (enable.php)
 *
 * Note: to use the settings and documentation pages, you will first need to enable
 * the plugin!
 *
 * @package Plugins
 * @subpackage skeleton
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
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
    <h2>Gallery</h2>
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
        <a href="<?php echo BASE_URI. 'plugin/'. GAL_URL ?>/clearall">
            <img src='<?php echo URI_PUBLIC ?>/wolf/icons/delete-32.png' align="middle" alt="snippet icon">
            Clear all data!
        </a>
    </p>
</div>
<?php endif; ?>