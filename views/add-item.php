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
<h1><?php echo __('Add/Edit item'); ?></h1>

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
    </ul>
    <p class='form_controls'>
        <input type="submit" name="" value="Save item"> or <a href="./">Cancel</a>
    </p>
</form>

<style type="text/css" media="screen">
    #body_plugin_gallery input[type='text'] {
        width: 220px;
    }
    #body_plugin_gallery label {
        display: inline-block;
        width: 160px;
        vertical-align: top;
    }
    #body_plugin_gallery textarea {
        display: inline-block;
        width: 500px !important;
    }
    #body_plugin_gallery .form_line {
        margin-bottom: 5px;
    }
    #body_plugin_gallery .form_controls {
        margin-top: 1.5em;
        padding: 1em 1em;
        background-color: #EBEBEB;

        border-radius: 5px;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
    }
    .datalist {
        display: inline-block;
        clear: none;
    }
    .datalist_add {
        float: right;
    }
    .datalist_item {
        margin-bottom: 3px;
    }
    .datalist_line {
        display: block;
        clear: both;
    }
</style>

<script>
    $(function(){
        $('.datalist_add').click(function(){
            field = $(this).parent('.datalist').attr('data-col');

            $(this).parent('.datalist')
                .append("<span class='datalist_line'><input name='"+ field+ "[]' class='datalist_item' type='text'><input class='datalist_delete' type='button' value='Delete'></span>");

            $('.datalist_delete').click(function(){
                $(this).parent('.datalist_line').remove();
            });
        });

        $('.datalist_delete').click(function(){
            $(this).parent('.datalist_line').remove();
        });
    });
</script>

<!--
<script type="text/javascript">
// <![CDATA[
    function setConfirmUnload(on, msg) {
        window.onbeforeunload = (on) ? unloadMessage : null;
        return true;
    }

    function unloadMessage() {
        return '<?php echo __('You have modified this page.  If you navigate away from this page without first saving your data, the changes will be lost.'); ?>';
    }

    $(document).ready(function() {
        // Prevent accidentally navigating away
        $(':input').bind('change', function() { setConfirmUnload(true); });
        $('form').submit(function() { setConfirmUnload(false); return true; });
    });
// ]]>
</script>
-->