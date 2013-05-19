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
<h1><?php echo __('Add/Edit Category'); ?></h1>

<p>
    
</p>

<form id='addItem' method='post' enctype="multipart/form-data">
	<ul>
	    <?php
        foreach($item_fields as $field_id => $details)
        {
            // Allowed in input forms?
            if (!isset($details['userinput']) || $details['userinput'] === true)
            {
                echo "<li class='form_line'>";
                if (isset($details['caption']))
                {
                    echo "<label for='{$field_id}'>{$details['caption']}:</label> ";
                }

                // Get previous POST/data data for form
                $value = isset($data[$field_id]) ? $data[$field_id] : '';

                if ($details['type'] == 'string')
                {
                    if (isset($details['maxlength']))
                    {
                        // Guesstimate, ex isn't useful unless they fix it, gives user a rough idea that the form is limited
                        $csswidth = (int) $details['maxlength'] + 2;
                        echo "<input name='{$field_id}' type='text' maxlength='{$details['maxlength']}' style='width: {$csswidth}ex;' value='{$value}'>";
                    }
                    else
                    {
                        echo "<input name='{$field_id}' type='text' value='{$value}'>";
                    }
                }
                elseif ($details['type'] == 'text')
                {
                    echo "<textarea name='{$field_id}'>{$value}</textarea>";
                }
                elseif ($details['type'] == 'file')
                {
                    echo "<input name='{$field_id}' type='file'>";
                }
                elseif ($details['type'] == 'list')
                {
                    echo "<div class='datalist' data-col='{$field_id}'>";
                    echo "<input class='datalist_add' type='button' value='Add category'>";

                    if (!is_array($value)) $value = array($value);

                    foreach ($value as $val)
                    {
                        echo "<span class='datalist_line'>";
                        echo "<input name='{$field_id}[]' class='datalist_item' type='text' value='{$val}'>";
                        echo "<input class='datalist_delete' type='button' value='Delete'>";
                        echo "<span>";
                    }

                    echo "</div>";
                }

                // Check if optional
                if (isset($details['allowEmpty']) && $details['allowEmpty'] === true)
                {
                    echo '<span>(Optional)</span>';
                }
                echo '</li>';
            }
        }
    	?>
        <p class='form_controls'>
            <input type="submit" name="" value="<?php echo __('Save item') ?>"> or <a href="./"><?php echo __('Cancel') ?></a>
        </p>
    </ul>
</form>

<style type="text/css">
    <?php if (file_exists(GAL_ROOT. '/css/add.css')) require(GAL_ROOT. '/css/add.css') ?>
</style>

<script type="text/javascript">
    var baseurl = '<?php echo URL_PUBLIC. 'admin/plugin/'. GAL_URL. '/categories' ?>';
    var curpage = <?php echo $page ?>;

    <?php if (file_exists(GAL_ROOT. '/js/add.js')) require(GAL_ROOT. '/js/add.js') ?>
</script>