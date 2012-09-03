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
<h1><?php echo __('Add item'); ?></h1>

<p>
	<?php //print_r($item_fields) ?>
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

                if ($details['type'] == 'string')
                {
                    if (isset($details['maxlength']))
                    {
                        echo "<input name='{$field_id}' type='text' maxlength='{$details['maxlength']}' style='width: {$details['maxlength']}ex; '>";
                    }
                    else
                    {
                        echo "<input name='{$field_id}' type='text'>";
                    }
                }
                elseif ($details['type'] == 'text')
                {
                    echo "<textarea name='{$field_id}'></textarea>";
                }
                elseif ($details['type'] == 'file')
                {
                    echo "<input name='{$field_id}' type='file'>";
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
</style>

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