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
<div class="box">
<h2><?php echo __('A sidebar');?></h2>

<p class='button'>
	<a href="<?php echo BASE_URI. 'plugin/'. GAL_ID ?>/add">
		<img src='<?php echo URI_PUBLIC ?>/wolf/icons/add-page-32.png' align="middle" alt="snippet icon">
		Add item
	</a>
</p>

<?php if (DEBUG): ?>
<p class='button'>
	<a href="<?php echo BASE_URI. 'plugin/'. GAL_ID ?>/clearall">
		<img src='<?php echo URI_PUBLIC ?>/wolf/icons/delete-32.png' align="middle" alt="snippet icon">
		Clear all data!
	</a>
</p>
<?php endif; ?>
</div>
