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
<h1><?php echo __('Items'); ?></h1>

<form method="post">
<p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Code</th>
                <th>Description</th>
                <th>Category</th>
                <th>Modify</th>
            </tr>
        </thead>

        <tbody>
        <?php
        foreach ($items as $item => $details)
        {
            echo '<tr>';

            $i = 0;
            foreach ($details as $detail)
            {
                $id = $details->id;

                if ($i == 1)
                {
                    echo '<td><a href="'. URL_PUBLIC. 'admin/plugin/'. GAL_URL. '/edit/'. $id. '">'. $detail. '</a></td>';
                }
                else
                {
                    echo '<td>';
                    if (is_array($detail)) {
                        echo '<ul class="listing">';
                        foreach ($detail as $item)
                        {
                            echo '<li>'. $item. '</li>';
                        }
                        echo '</ul>';
                        
                    }
                    else
                    {
                        echo $detail;
                    }
                    echo '</td>';
                }
                
                $i++;
            }

            echo '<td><div class="item_options"><a class="remove" href="' .URL_PUBLIC. 'admin/plugin/'. GAL_URL. '/delete/'. $id. '" onclick="return confirm(\'Are you sure you wish to delete?\');"><img src="'. URL_PUBLIC. 'wolf/admin/images/icon-remove.gif" alt="delete snippet icon" title="Delete snippet"></a> <input name="remove[]" class="ck_remove" type="checkbox" value="'. $id. '"></div></td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
</p>

<div class="hidden_options">
    Options: <input type="submit" value="Delete selected">
</div>
</form>

<style>
    table {
        width: 100%;
    }
    table tr {
        border-bottom: 1px solid #DEDEDE;
        vertical-align: top;
    }
    table td {
        min-width: 30px;
        padding: 2px 4px;
    }
    table thead {
        background-color: whiteSmoke;
    }
    table th {
        font-size: 90%;
        font-weight: normal;
        text-align: left;

        padding: 2px 4px;
    }
    tbody tr.odd {
        background-color: #F2F2F2;
    }
    tbody tr:hover {
        background-color: #E6F0FC;
    }
    .hidden_options {
        background-color: #E3E3E3;
        float: right;
        display: none;
        padding: .2em .4em;
        margin-top: -.8em;

        -moz-border-radius: 5px;
        border-radius: 5px;
    }
    .ck_remove {
        display: none;
    }
    input {
        padding: .1em .2em;
    }
    .listing  {
        /*list-style: square;*/
    }
</style>

<script type="text/javascript">
    var ck_mode = false;

    /* This is so when control is held down extra commands appear */
    $(function(){
        $('body').keydown(function(e) {
            if (e.ctrlKey && !ck_mode)
            {
                ck_mode = true;
                $('.ck_remove').show();
            }
        });

        $('body').keyup(function(e) {
            if (ck_mode)
            {
                ck_mode = false;
                $('.ck_remove:not(:checked)').each(function(){
                    $(this).hide();
                });
            }
        });

        $('.ck_remove').change(function(){
            if (!this.checked && !ck_mode)
            {
                $(this).hide();
            }
            if ( $('.ck_remove:checked').length > 0 )
            {
                $('.hidden_options').show();
            }
            else
            {
                $('.hidden_options').hide();
            }
        });

        $('tbody tr:odd').addClass('odd');
        $('tbody tr:even').addClass('even');
    });
</script>