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

<?php $page_total = ceil($total / $limit); ?>

<form method="post">
<p>
    <table>
        <thead>
            <tr>
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
            foreach ($details as $col => $detail)
            {
                $id = $details->id;

                if ($col == 'id')
                {

                }
                elseif ($col == 'name')
                {
                    echo '<td><a href="'. URL_PUBLIC. 'admin/plugin/'. GAL_URL. '/edit:'. $id. '">'. $detail. '</a></td>';
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

            echo '<td class="modify-options"><div class="item_options"><a class="remove" href="' .URL_PUBLIC. 'admin/plugin/'. GAL_URL. '/delete:'. $id. '" onclick="return confirm(\'Are you sure you wish to delete?\');"><img src="'. URL_PUBLIC. 'wolf/admin/images/icon-remove.gif" alt="delete item icon" title="Delete item"></a> <input name="remove[]" class="ck_remove" type="checkbox" value="'. $id. '"></div></td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
</p>

<?php if ($page_total > 0): ?>
<div class="hidden_options">
    Options: <input type="button" value="Select all" class="select_all"> <span class="selection_options"><input type="submit" value="Delete selected"></span>
</div>
<?php endif; ?>
</form>


<?php if ($page_total > 0): ?>
<div class='pagination form_controls'>
    <?php if ($page > 1): ?>
    <a href="<?php echo ($page > 1) ? URL_PUBLIC. 'admin/plugin/'. GAL_URL. '/page:'. ($page - 1) : '' ?>" style="float:left; margin-top: 3px">&larr; Prev</a>
    <?php endif; ?>

    <span style='display: inline-block; margin-left: 30px; margin-right: 30px;'>
        Page <input type='text' value='<?php echo $page ?>' class='pagination_curpage'> of <span class='pagination_total'><?php echo $page_total ?></span>
    </span>

    <?php if ($page * $limit < $total): ?>
    <a href="<?php echo URL_PUBLIC. 'admin/plugin/'. GAL_URL. '/page:'. ($page + 1) ?>" style="float:right; margin-top: 3px">Next &rarr;</a>
    <?php endif; ?>
</div>
<?php else: ?>
<p style='text-align: center; clear: both;'>
    No items found.
</p>
<?php endif; ?>

<style>
    <?php require(GAL_ROOT. '/css/listing.css') ?>
</style>

<script type="text/javascript">
    var baseurl = '<?php echo URL_PUBLIC. 'admin/plugin/'. GAL_URL ?>';
    var curpage = <?php echo $page ?>;

    <?php require(GAL_ROOT. '/js/listing.js') ?>
</script>