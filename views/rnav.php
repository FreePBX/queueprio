<?php
    if (!defined('FREEPBX_IS_AUTH')) { exit('No direct script access allowed'); }
?>
<div id="rnav-side">
    <div id="toolbar-side">
        <a href="?display=queueprio" class="btn btn-default">
            <i class="fa fa-list"></i> <?php echo _("List Priorities")?>
        </a>
        <a href="?display=queueprio&view=form" class="btn btn-default">
            <i class="fa fa-plus"></i> <?php echo _("Add Priority")?>
        </a>
    </div>
    <table  id="priority-side"
        data-url="ajax.php?module=queueprio&amp;command=priority_list"
        data-cache="false"
        data-toolbar="#toolbar-side"
        data-toggle="table"
        data-search="true"
        data-pagination="true"
        class="table">
        <thead>
            <tr>
                <th data-field="name"><?php echo _("Name")?></th>
            </tr>
        </thead>
    </table>
</div>