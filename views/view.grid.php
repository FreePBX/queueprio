<div id="toolbar-all">
    <button type="button" id="btnNewPriority" class="btn btn-default btn-lg btn-priority-new" data-toggle="modal" data-target="#dlgCreatePriority">
        <i class="fa fa-plus">&nbsp;</i><?php echo _('New Priority')?>
    </button>
    <button type="button" id="removePriorities" class="btn btn-danger btn-priority-remove btn-lg" disabled>
        <i class="fa fa-trash-o"></i>&nbsp;<?php echo _('Delete Priorities')?>
    </button>
</div>
<table id="queuepriogrid" class="table table-striped"
    data-type="scheme"
    data-toolbar="#toolbar-all"
    data-unique-id="id"
    data-cache="false"
    data-cookie="true"
    data-cookie-id-table="queuepriogrid"
    data-maintain-selected="true"
    data-toggle="table"
    data-pagination="true"
    data-search="true"
    data-escape="true"
    data-show-refresh="true"
    data-url="ajax.php?module=queueprio&amp;command=priority_list"
    data-row-style="priorityRowStyle"
    >
    <thead>
        <tr>
            <th data-checkbox="true"></th>
            <th data-field="id" data-sortable="true" class="priority_id"><?php echo _('ID')?></th>
            <th data-field="name" data-sortable="true" class="priority_name"><?php echo _('Name')?></th>
            <th data-field="priority" data-sortable="true" class="priority_priority"><?php echo _('Priority')?></th>
            <th data-field="dest_pretty" data-sortable="true" class="priority_dest_pretty"><?php echo _('Destination')?></th>
            <th data-field="inUsed" data-sortable="true" class="priority_inused" data-formatter='priorityInUse'><?php echo _('In Use')?></th>
            <th class="priority_actions" data-formatter='priorityActions' data-events='priorityActionsEvents'><?php echo _('Actions')?></th>
        </tr>
    </thead>
</table>

<?= $queueprio->showPage("priority.add") ?>