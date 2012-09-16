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
        $items = (!is_array($items)) ? array($items) : $items;

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
    Options: <input type="button" value="Select all" class="select_all"> <span class="selection_options"><input type="submit" value="Delete selected"></span>
</div>
</form>


<?php
    $page_total = ceil($total / $limit);
    if ($page_total > 0):
?>
<div class='pagination'>
    <?php if ($page > 1): ?>
    <a href="<?php echo URL_PUBLIC. 'admin/plugin/'. GAL_URL. '/page:'. ($page - 1) ?>">&larr; Prev</a>
    <?php endif; ?>

    <span style='display: inline-block; margin-left: 30px; margin-right: 30px;'>
        Page <input type="text" value="<?php echo $page ?>" class="pagination_curpage"> of <?php echo $page_total ?>
    </span>

    <?php if ($page * $limit < $total): ?>
    <a href="<?php echo URL_PUBLIC. 'admin/plugin/'. GAL_URL. '/page:'. ($page + 1) ?>">Next &rarr;</a>
    <?php endif; ?>
</div>
<?php else: ?>
<p style='text-align: center;'>
    No items found.
</p>
<?php endif; ?>

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
    .selection_options {
        display: none;
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
    .pagination {
        text-align: center;
        margin: 0 auto;
        clear: both;
    }
    .pagination_curpage {
        width: 40px;
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
                $('.hidden_options').show();
            }
        });

        $('body').keyup(function(e) {
            if (ck_mode)
            {
                ck_mode = false;
                $('.ck_remove:not(:checked)').each(function(){
                    $(this).hide();
                });

                if ($('.selection_options').is(':hidden'))
                {
                    $('.hidden_options').hide();
                }
            }
        });

        $('.select_all').click(function(){
            $('.ck_remove').click();
            $('.ck_remove').change();
        });

        $('.ck_remove').change(function(){
            if (!this.checked && !ck_mode)
            {
                $(this).hide();
            }
            if ( $('.ck_remove:checked').length > 0 )
            {
                $('.selection_options').show();
            }
            else
            {
                $('.selection_options').hide();

                if (!ck_mode) {
                    $('.hidden_options').hide();
                }
            }
        });

        $('tbody tr:odd').addClass('odd');
        $('tbody tr:even').addClass('even');
    });
</script>