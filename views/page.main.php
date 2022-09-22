<?php
    switch ($_REQUEST['view'])
    {
		case 'form':
            $content = $queueprio->showPage('form');
        break;

		case '':
        case 'list':
        default:
            $content = $queueprio->showPage('grid');
        break;
    }
?>
<div class="container-fluid">
	<h1><?php echo _('Queue Priorities')?></h1>
	<?php echo show_help("<p>" . _("Queue Priority allows you to set a caller's priority in a queue. By default, a caller's priority is set to 0. Setting a higher priority will put the caller ahead of other callers already in a queue. The priority will apply to any queue that this caller is eventually directed to. You would typically set the destination to a queue, however that is not necessary. You might set the destination of a priority customer DID to an IVR that is used by other DIDs, for example, and any subsequent queue that is entered would be entered with this priority.") . "</p>", _('What is Queue Priorities?'), false, true, "info"); ?>
	<div class="row">
		<div class="col-sm-12">
			<div class="fpbx-container">
				<?php echo $content?>
			</div>
		</div>
	</div>
</div>