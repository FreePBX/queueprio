<?php
// $dPriority = array();
// $dPriority['default'] = $queueprio->getPriorityDefault();
// $dPriority['data'] = $queueprio->getPriorityDefault();

$form_data = array(
	array(
		'name' 	=> 'type',
		'type' 	=> 'hidden',
		'value' => '',
	),
	array(
		'name' 	=> 'priority_id',
		'type' 	=> 'hidden',
		'value' => ! empty($dPriority['data']['id']) ? $dPriority['data']['id'] : $dPriority['default']['id'],
	),
	array(
		'name' 	=> 'priority_name',
		'title'	=> _('Name'),
		'type' 	=> 'text',
		'index'	=> true,
		'opts' 	=> array(
			'maxlength' => "50",
			'value' => ! empty($dPriority['data']['name']) ? $dPriority['data']['id'] : $dPriority['default']['name'],
		),
		'help'	=> _('The descriptive name of this Queue Priority instance.'),
	),
	array(
		'name' 	=> 'priority_priority',
		'title'	=> _('Priority'),
		'type' 	=> 'number',
		'index'	=> true,
		'opts' 	=> array(
			'min' => "0",
			'max' => "20",
			'value' => ! empty($dPriority['data']['id']) ? $dPriority['data']['priority'] : $dPriority['default']['priority'],
		),
		'help'	=> _('The Queue Priority to set 0 - 20'),
	),
	array(
		'name' 	=> 'goto0',
		'title'	=> _('Destination'),
		'type' 	=> 'raw',
		'value' => drawselects('', 0, false, false),
		'help'	=> _('Destination'),
	),
);

$element_container = '
<div class="element-container $$__ELEMENT_CONTAINER_CLASS__$$">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="$$__NAME__$$">$$__TITLE__$$</label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="$$__NAME__$$"></i>
					</div>
					<div class="col-md-9">
						$$__TYPE_INPUT__$$
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="$$__NAME__$$-help" class="help-block fpbx-help-block">$$__HELP__$$</span>
		</div>
	</div>
</div>
';

$type_input = array(
	'text' 	 	=> '<input type="text" id="$$__NAME__$$" class="form-control" name="$$__NAME__$$" $$__OPTIONS__$$>',
	'list' 	 	=> '<select id="$$__NAME__$$" class="form-control" name="$$__NAME__$$">$$__LINES__$$</select>',
	'list_line'	=> '<option value="$$__VALUE__$$" $$__SELECTED__$$ $$__SELECTABLE__$$>$$__TEXT__$$</option>',
	'number'	=> '<input type="number" id="$$__NAME__$$" class="form-control" name="$$__NAME__$$" $$__OPTIONS__$$>',
	'textarea' 	=> '<textarea id="$$__NAME__$$" class="form-control" name="$$__NAME__$$" $$__OPTIONS__$$>$$__VALUE__$$</textarea>',	
);

$new_input_all = "";

foreach ($form_data as $index => $element)
{
	$new_input = "";
	switch($element['type'])
	{
		case 'hidden':
			$new_input = sprintf('<input type="hidden" name="%s" value="%s">', $element['name'], $element['value']);
			break;

		case 'list':
			$tmp_ls_options = "";
			foreach ($element['list'] as $line_option)
			{
				$tmp_option = $type_input['list_line'];
				$tmp_option = str_replace('$$__VALUE__$$', $line_option[$element['keys']['value']], $tmp_option);
				$tmp_option = str_replace('$$__TEXT__$$', $line_option[$element['keys']['text']], $tmp_option);
				$tmp_option = str_replace('$$__SELECTED__$$', ($priority_data['processor'] == $line_option[$element['keys']['value']] ? 'selected' : ''), $tmp_option);
				$tmp_option = str_replace('$$__SELECTABLE__$$', (! $line_option['selectable'] ? 'disabled' : ''), $tmp_option);
				$tmp_ls_options .= $tmp_option;
				unset($tmp_option);
			}

		case 'text':
		case 'number':
		case 'textarea':
			$new_input = $element_container;
			if (isset($type_input[$element['type']]))
			{
				$new_input = str_replace('$$__TYPE_INPUT__$$', $type_input[$element['type']], $new_input);
			}

			if (! empty($tmp_ls_options)) {
				$new_input = str_replace('$$__LINES__$$', $tmp_ls_options, $new_input);
			}

			if (isset($element['index']) && $element['index']) {
				$new_input = str_replace('$$__OPTIONS__$$', sprintf('tabindex="%s" $$__OPTIONS__$$', $index), $new_input);
			}

			if (isset($element['opts'])) {
				foreach ($element['opts'] as $key => $val)
				{
					$new_input = str_replace('$$__OPTIONS__$$', sprintf('%s="%s" $$__OPTIONS__$$', $key, $val), $new_input);
				}
			}
			break;

		case 'raw':
			$new_input = $element_container;
			$new_input = str_replace('$$__TYPE_INPUT__$$', $element['value'], $new_input);
			break;
	}

	// NAME
	if (isset($element['name'])) {
		$new_input = str_replace('$$__NAME__$$', $element['name'], $new_input);
	}

	// TTITLE
	if (isset($element['title'])) {
		$new_input = str_replace('$$__TITLE__$$', $element['title'], $new_input);
	}
	
	// VALUE
	if (isset($element['value'])) {
		$new_input = str_replace('$$__VALUE__$$', $element['value'], $new_input);
	}

	// HELP
	if (isset($element['help'])) {
		if (is_array($element['help']))
		{
			$help_text = "";
			foreach ($element['help'] as $line)
			{
				switch($line['type'])
				{
					case "text":
						$help_text .= $line['value'];
						break;

					case "list":
						foreach ($line['list'] as $list_line)
						{
							$help_text_line = $line['tamplate'];
							foreach ($list_line as $l_key => $l_val)
							{
								if (in_array($l_key, $line['keys'])) {
									$help_text_line = str_replace( sprintf('$$__%s__$$', strtoupper($l_key)), $l_val, $help_text_line);
								}
							}
							$help_text .= $help_text_line;
							unset($help_text_line);
						}
						break;
				}
			}
			$new_input = str_replace('$$__HELP__$$', $help_text, $new_input);
			unset($help_text);
		}
		else { $new_input = str_replace('$$__HELP__$$', $element['help'], $new_input); }
	}

	// CLASS
	if (isset($element['class'])) {
		$new_input = str_replace('$$__ELEMENT_CONTAINER_CLASS__$$', $element['class'], $new_input);
	}
	
	// Clean Up
	foreach (array('TYPE_INPUT', 'OPTIONS', 'VALUE', 'HELP', 'LINES', 'ELEMENT_CONTAINER_CLASS') as $item)
	{
		$new_input = str_replace( sprintf('$$__%s__$$', $item), "", $new_input);
	}

	// echo $new_input;
	$new_input_all .= $new_input;
}

$thisid = uniqid();
?>

<div class="modal fade" id="dlgCreatePriority" tabindex="-1" role="dialog" aria-labelledby="dlgCreatePriorityLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="dlgCreatePriorityLabel"><?php echo _('Define Settings for a new Priority')?></h4>
			</div>
			<div class="modal-body">
                <div class="model-body-loading">
                    <div class="fpbx-container">
                        <div class="display no-border">
                            <h5><?= _("Loading...") ?></h5>
                        </div>
                    </div>
                </div>
                <div class="model-body-loading-error">
                    <div class="fpbx-container">
                        <div class="display no-border">
                            <h5><?= _("Error: Data could not be loaded!") ?></h5>
                        </div>
                    </div>
                </div>
                <form method="POST" action="#" class="fpbx-submit" name="opt_priority">
					<div class="panel panel-info panel-help box-inused">
						<div class="panel-heading collapsed" data-target="#<?php echo $thisid; ?>" data-toggle="collapse">
							<span class="box-inused-title"></span>
							<span class="pull-right"><i class="chevron fa fa-fw"></i></span>
						</div>
						<div id="<?php echo $thisid; ?>" class="panel-collapse collapse box-inused-data">
							<div class="panel-body"></div>
						</div>
					</div>
                    <div class="fpbx-container">
                        <div class="display no-border">
                        <?= $new_input_all; ?>
                        </div>
                    </div>
                </form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger btn-dlg-cancel"><?php echo _('Cancel')?></button>
				<button type="button" class="btn btn-success btn-dlg-save"><?php echo _('Create Priority')?></button>
			</div>
		</div>
	</div>
</div>