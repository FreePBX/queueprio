<?php
    $usagehtml      = '';
    $data_priority = $queueprio->getPriorityDefault();
    $data_priority['type'] = "new";
    if (isset($_REQUEST['extdisplay']) && ! empty($_REQUEST['extdisplay']))
    {
        $data_priority['id'] = trim($_REQUEST['extdisplay']);
    }

    if ($data_priority['id'] != "")
    {
        $row = $queueprio->getPriority($data_priority['id']);
        if (! empty($row))
        {
            $data_priority = array(
                'type'      => 'edit',
                'id'        => $row['id'],
                'name'      => $row['name'],
                'priority'  => $row['priority'],
                'dest'      => $row['dest'],
            );
            $usage_list = $queueprio->hookDestinationUsage($data_priority['id']);
            if (!empty($usage_list)) {
                $usagehtml = '<div class="well well-info>"';
                $usagehtml .= '<h3>'.$usage_list['text'].'</h3>';
                $usagehtml .= '<p>'.$usage_list['tooltip'].'</p>';
                $usagehtml .= '</div>';
            }
            unset($usage_list);
        }
        else
        {
            $data_priority['type'] = "error";
        }
    }
    if ($data_priority['type'] == "error")
    {
        // TODO: Improve error page design
        echo "ID not found!!!!!";
        return;
    }

    $delURL  = $data_priority['type'] == 'new' ? '' : '?display=queueprio&action=delete&queueprio_id='.$data_priority['id'];
    $subhead = $data_priority['type'] == 'new' ? _("Add Queue Priority") : sprintf(_("Edit: %s (%s)"), $data_priority['name'], $data_priority['priority']);
?>

<h2><?php echo $subhead; ?></h2>
<?php echo $usagehtml; ?>
<div class = "display full-border">
    <div class="row">
		<div class="col-sm-12">

            <form name="editQueuePriority" id="editQueuePriority" class="fpbx-submit" action="" method="post" onsubmit="return checkQueuePriority(editQueuePriority);" data-fpbx-delete="<?php echo $delURL?>">
                <input type="hidden" name="extdisplay" value="<?php echo $data_priority['id']; ?>">
                <input type="hidden" name="priority_id" value="<?php echo $data_priority['id']; ?>">
                <input type="hidden" name="action" value="<?php echo ($data_priority['id'] ? 'edit' : 'add'); ?>">
                <!--Name-->
                <div class="element-container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="form-group">
                                    <div class="col-md-3">
                                        <label class="control-label" for="priority_name"><?php echo _("Name") ?></label>
                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="priority_name"></i>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" id="priority_name" name="priority_name" value="<?php echo $data_priority['name'] ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <span id="priority_name-help" class="help-block fpbx-help-block"><?php echo _("The name of this Queue Priority instance.")?></span>
                        </div>
                    </div>
                </div>
                <!--END Name-->

                <!--Priority-->
                <div class="element-container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="form-group">
                                    <div class="col-md-3">
                                        <label class="control-label" for="priority_priority"><?php echo _("Priority") ?></label>
                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="priority_priority"></i>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="number" min="0" max="20" class="form-control" id="priority_priority" name="priority_priority" value="<?php echo $data_priority['priority'] ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <span id="priority_priority-help" class="help-block fpbx-help-block"><?php echo _("The Queue Priority to set 0 - 20")?></span>
                        </div>
                    </div>
                </div>
                <!--END Priority-->

                <!--Destination-->
                <div class="element-container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="form-group">
                                    <div class="col-md-3">
                                        <label class="control-label" for="goto0"><?php echo _("Destination") ?></label>
                                        <i class="fa fa-question-circle fpbx-help-icon" data-for="goto0"></i>
                                    </div>
                                    <div class="col-md-9">
                                        <?php echo drawselects($data_priority['dest'], 0)?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <span id="goto0-help" class="help-block fpbx-help-block"><?php echo _("Destination")?></span>
                        </div>
                    </div>
                </div>
                <!--END Destination-->
            </form>

        </div>
    </div>
</div>
<script>
    // TODO: list of names to check if it exists, it would be nice to update to ajax and validate it in real time.
    var qprionames = <?php print json_encode($queueprio->getallqprio($data_priority['id'])); ?>;
</script>
