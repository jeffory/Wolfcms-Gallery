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
<h1><?php echo __('Add gallery item'); ?></h1>

<?php //die(print_r($items) )?>

<p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Code</th>
                <th>Description</th>
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
                $id = $details['id'];

                if ($i == 1)
                {
                    echo '<td><a href="'. URL_PUBLIC. 'admin/'. GAL_ID. '/edit/'. $id. '">'. $detail. '</a></td>';
                }
                else
                {
                    echo '<td>'. $detail. '</td>';
                }
                
                $i++;
            }

            echo '<td><div class="remove"><a class="remove" href="' .URL_PUBLIC. 'admin/'. GAL_ID. '/delete/'. $id. '" onclick="return confirm(\'Are you sure you wish to delete?\');"><img src="/new/wolf/admin/images/icon-remove.gif" alt="delete snippet icon" title="Delete snippet"></a></div></td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
</p>

<style>
    table {
        width: 100%;
    }
    table tr {
        border-bottom: 1px solid #DEDEDE;
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
</style>