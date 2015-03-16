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

<br><br>

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
                    echo "<div class='form_content'>";

                    if (isset($value) && isset($data))
                    {
                        echo "<img src='". URL_PUBLIC. GAL_URL. "/file/". $field_id. "_thumb/". $data['id']. "'><br>";
                    }

                    echo "<input name='{$field_id}' type='file'></div>";
                }
                elseif ($details['type'] == 'list')
                {
                    echo "<div class='datalist' data-col='{$field_id}'>";
                    echo "<input class='datalist_add' type='button' value='Add'>";

                    if (!is_array($value)) $value = array($value);

                    foreach ($value as $val)
                    {
                        echo "<span class='datalist_line'>";
                        echo "<input name='{$field_id}[]' class='datalist_item' type='text' value='{$val}'>";
                        echo "<input class='datalist_delete' type='button' value='Delete'>";
                        echo "</span>";
                    }

                    echo "</div>";
                }
                elseif ($details['type'] == 'filelist')
                {
                    echo "<ul class='filelist' data-col='{$field_id}'>";
                    // echo "<input class='filelist_add' type='button' value='Add more'><br><br>";

                    if (!is_array($value)) $value = array($value);

                    foreach ($value as $val)
                    {
                        if (!empty($val))
                        {
                            echo "<li class='filelist_item'><img src='". URL_PUBLIC. GAL_URL. '/file/image_thumb/'. $val. "'>";
                            echo "<input name='{$field_id}_keep[]' value='{$val}' type='hidden'>";
                            echo "<input class='filelist_remove' type='button' value='Delete'>";
                            echo "</li>";
                            echo "<input name='{$field_id}_org[]' value='{$val}' type='hidden'>";
                        }
                    }
                    echo "<li class='preview'></li>";

                    echo "<br><input name='{$field_id}[]' class='datalist_item' type='file' multiple>";

                    echo "</ul>";
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

<script>
    $(function(){
        $('head').append('<link href="/wolf/plugins/<?php echo basename(GAL_ROOT)?>/css/add.css" media="screen" rel="Stylesheet" type="text/css" />');

        $('.datalist_add').click(function(){
            field = $(this).parent('.datalist').attr('data-col');

            $(this).parent('.datalist')
                .append("<span class='datalist_line'><input name='"+ field+ "[]' class='datalist_item' type='text'><input class='datalist_delete' type='button' value='Delete'></span>");

            $('.datalist_delete').click(function(){
                $(this).parent('.datalist_line:first-child').remove();
            });

            // New items need to have autocomplete
            $(".datalist_item").autocomplete({
                source: [ <?php if (!isset($categories) && is_array($categories)) echo "'". @implode("', '", $categories). "'" ?> ]
            });
        });

        $('.datalist').on("click", ".datalist_delete", function(){
            $(this).parent('.datalist_line').remove();
        });

        $('.filelist').on("click", ".filelist_remove", function(){
            $(this).parent('.filelist_item').remove();
        });
        // Autocomplete for Categories
        $(".datalist_item[name='category_name[]']").autocomplete({
            source: [ <?php if (isset($categories) && is_array($categories)) echo "'". implode("', '", $categories). "'" ?> ],
            minLength: 0,
            delay: 0
        });

        // Show image previews
        $("input[type='file']").on("change", function () {
            $('.preview').empty();

            $.each($(this).get(0).files, function(index, file){
                var fr = new FileReader();
                console.log(file);
                if (file.type == 'image/jpeg') {
                    fr.readAsDataURL(file);
                    
                    fr.onload = function (e) {

                        $('.preview').append( '<span><img src="' + e.target.result + '"></span>' );
                    };
                }
            });
        });

        $( ".filelist" ).sortable();
        $( ".filelist" ).disableSelection();
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