$("#priority-side").on("click-row.bs.table", function(row, $element) {
	sourceRedirect($element.id);
});

function sourceRedirect(id) {
	window.location = "?display=queueprio&view=form&extdisplay="+id;
}

function checkQueuePriority(theForm) {
	var msgInvalidDescription = _('Invalid name specified');
	var msgduplicate = _("Queue Priorities name already exist");
	if (qprionames.indexOf($("#priority_name").val()) >= 0) {
		return warnInvalid($("#priority_name"),msgduplicate);
	}
	// set up the Destination stuff
	setDestinations(theForm, '_post_dest');

	// form validation
	defaultEmptyOK = false;
	if (isEmpty(theForm.priority_name.value))
		return warnInvalid(theForm.priority_name, msgInvalidDescription);

	if (!validateDestinations(theForm, 1, true))
		return false;

	return true;
}

var dlgPriorityAcction = "";
var dlgPriorityAcctionId = "";
var dlgPriorityAcctionClose = "";
var processing = false;

function getPriorityTable() {
	return $('#queuepriogrid');
}

function priorityTableRefresh() {
	getPriorityTable().bootstrapTable('refresh');
}

function showButtonReloadFreePBX(){
	$("#button_reload").show();
}

function parseResultAJAX(statusAjax, data)
{
	switch (statusAjax)
	{
		case 'ok':
			if (data.message)
			{
				fpbxToast(data.message, '', (data.status ? 'success' : 'error') );
			}
			if (data.needreload)
			{
				showButtonReloadFreePBX();
			}
			break;
		case 'error':
			fpbxToast(data.responseText, '', "error");
			break;
	}
}

getPriorityTable().on("page-change.bs.table", function () {
	$(".btn-priority-remove").prop("disabled", true);
});

getPriorityTable().on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table load-success.bs.table load-error.bs.table', function () {
	var table = $(this);
	var toolbar = $(table).data("toolbar");
	var btn_remove = $(toolbar).find(".btn-priority-remove");
	$(btn_remove).prop('disabled', !$(table).bootstrapTable('getSelections').length);
});

function priorityActions(value, row, index){
	var dialog_id = getDlgCreatePriority().prop("id")

	var html = '';
	html += '<a href="#" title="' + _('Edit Priority') + '" data-type="priority_edit" data-toggle="modal" data-target="#'+ dialog_id +'"><i class="fa fa-pencil"></i></a>';
	html += '&nbsp;';
	html += '<a href="#" title="' + _('Delete Priority') + '" data-type="priority_remove"><i class="fa fa-trash-o"></i></a>';
	return html;
}

function priorityInUse(value, row, index) {
	var html = '';
	if (row.inUsed)
	{
		html += '<i class="fa fa-check fa-lg" title="'+ row.inUsedData.count_pretty +'"></i>';
	}
	else
	{
		html += '<i class="fa fa-times fa-lg"></i>';
	}
	return html;
}

function priorityRowStyle(row, index) {
	if (isEmpty(row['dest']) || row['dest'] === row['dest_pretty']) {
		return { classes: 'error_validate' }
	}
	return {}
}

$(document).on('click', "button.btn-priority-new", function(e) {
	dlgPriorityAcction = "new";
	dlgPriorityAcctionId = "";
});

$(document).on('click', "button.btn-priority-remove", function(e) {
	e.preventDefault();
	
	var btn = $(this);
	var table = getPriorityTable();
	
	if($(btn).prop("disabled") === false){ // <--- Fixe the Delete button issue with Chrome
		fpbxConfirm(
			_("Are you sure you wish to delete these Priorities?"),
			_("Yes"), _("No"),
			function() {
				btn.find("span").text(_("Deleting..."));
				btn.prop("disabled", true);

				var chosen = $(table).bootstrapTable("getSelections");
				var items = {};
				$.each(chosen, function(k, v) {
					items[k] = v.id;	
				});

				var post_data = {
					module: "queueprio",
					command: "priority_del",
					priority_id: items
				};
				$.post( window.FreePBX.ajaxurl, post_data, function(data) {
					parseResultAJAX("ok", data);
				})
				.fail(function(xhr, textStatus, errorThrown) {
					parseResultAJAX("error", xhr);
				})
				.always(function() {
					btn.find("span").text(_("Delete Priorities"));
					priorityTableRefresh();
				});
			}
		);
	}
});

window.priorityActionsEvents = {
	'click a': function (e, value, row, index) {
		if(processing) {
			fpbxToast(_("A command is already processing, please wait"), '', "error");
			return;
		}
		
		e.preventDefault();
		t = e.target || e.srcElement;

		processing = true;
		
		var $this = this;
		var type  = $(e.currentTarget).data("type");
		var id 	  = row.id;
		var name  = row.name;

		switch (type)
		{
			case "priority_edit":
				dlgPriorityAcction = "edit";
				dlgPriorityAcctionId = row.id;
				processing = false;
				break;

			case 'priority_edit_old':
				document.location.href = sprintf('?display=queueprio&view=form&extdisplay=%s', id);
			 	break;

			case "priority_remove":
				fpbxConfirm(
					sprintf(_('Are you sure you wish to delete the Priority "%s"?'), name),
					_("Yes"), _("No"),
					function() {
						var post_data = {
							module: "queueprio",
							command: "priority_del",
							priority_id: {id}
						};
						$.post(window.FreePBX.ajaxurl, post_data, function(data) {
							parseResultAJAX("ok", data);
						})
						.fail(function(xhr, textStatus, errorThrown) {
							parseResultAJAX("error", xhr);
						})	
						.always(function() {
							processing = false;
							priorityTableRefresh();
						});
					}
				);
				processing = false;
			break;
		}
	}
}

function getDlgCreatePriority(){
	return $('#dlgCreatePriority');	
}

function getDlgCreatePriorityResetDialog(dialog)
{
	var body = $(dialog).find('.modal-body');
	var form = $(body).find('form');

	$(form).hide();
	$(form).trigger("reset");
	$(form).find(".element-container").removeClass("has-error has-warning has-success");
	$(form).find(".element-container .input-warn").remove();
	$(form).find(".processor_multi_options").hide();

	$(dialog).find('.model-body-loading-error').hide();
	$(dialog).find('.model-body-loading').show();

	$(dialog).find('.box-inused').hide();
	$(dialog).find('.box-inused-data').collapse("hide");
}

getDlgCreatePriority().on('show.bs.modal', function(e) {
	var dialog = $(this);
	var btn_save = $(dialog).find('.btn-dlg-save');

	dlgPriorityAcctionClose = "";

	getDlgCreatePriorityResetDialog(dialog);

	$(btn_save).prop('disabled', true);
});

getDlgCreatePriority().on('shown.bs.modal', function(e) {
	var dialog = $(this);
	var body = $(dialog).find('.modal-body');
	var form = $(body).find('form');

	var box_loading = $(body).find('.model-body-loading');
	var box_loading_err = $(body).find('.model-body-loading-error');
	var box_inused = $(form).find('.box-inused');

	var title = $(dialog).find('.modal-title');
	var btn_save = $(dialog).find('.btn-dlg-save');
	
	$(form).find("input[name='type']").val(dlgPriorityAcction);
	$(form).find("input[name='priority_id']").val(dlgPriorityAcctionId);

	if (dlgPriorityAcction == "edit") {
		$(btn_save).text(_("Update Priority"));
		$(title).text( _('Define the settings to update the Priority'));
	} else {
		$(btn_save).text(_("Create Priority"));
		$(title).text( _('Define Settings for a new Priority'));
	}

	var post_data = {
		module: "queueprio",
		command: "priority_dialog",
		dlg_mode: dlgPriorityAcction,
		priority_id: dlgPriorityAcctionId
	};
	$.post(window.FreePBX.ajaxurl, post_data, function(data) {
		if (data.status)
		{
			if (data.priority.inUsed) {
				$(box_inused).find("span.box-inused-title").html(data.priority.inUsedData.count_pretty);
				let inUsedDestHTML = '<ul>';
				$.each(data.priority.inUsedData.list, function(kMod, vMod)
				{
					$.each(vMod, function(kDest, vDest) {
						inUsedDestHTML += '<li><a href="'+ vDest.edit_url +'" target="_blank"><i class="fa fa-pencil"></i></a> ' + vDest.description + '</li>';
					});
				});
				inUsedDestHTML += "</ul>";
				$(box_inused).find("div.panel-body").html(inUsedDestHTML);
				$(box_inused).show();
			}

			if (data.drawselects) {
				$("#goto0").parent().html(data.drawselects);
				bind_dests_double_selects();
			}

			$.each(data.priority, function(k, v) {
				if (k == "id") 	 { return; }
				if (k == "dest") {
					let namesec = $(form).find('option[value="' + v + '"]').parent().attr('name');
					if (namesec != undefined) {
						let namesecfix = namesec.substring(0, namesec.length - 1);
						v = { 0: namesecfix, 1: v }
					}
					else { v = null; }
				}
				switch (typeof v)
				{
					case 'string':
					case 'number':
						$(form).find("[name='priority_" + k + "']").val(v).trigger('change');
						return;
					break;
					case 'object':
						if (k == "dest")
						{
							if (v != null) {
								$(form).find('#goto0 option[value="'+ v[0] +'"]').prop('selected', true).trigger('change');
								$(form).find('#'+ v[0] +'0 option[value="'+ v[1] +'"]').prop('selected', true).trigger('change');
							}
							else
							{
								$(form).find('#goto0 option[value="Error"]').prop('selected', true).trigger('change');
								$(form).find('#Error0').prop('selected', true).trigger('change');
							}
							return;
						}
					break;
				}
			});
			$(btn_save).prop('disabled', false);
			$(box_loading).hide("");
			$(form).show("");
			dlgPriorityAcctionClose = "load_status_ok";
		} else {
			$(box_loading).hide("slow", function() {
				$(box_loading_err).show("slow");
			});
			dlgPriorityAcctionClose = "load_status_error";
		}
		parseResultAJAX("ok", data);
	})
	.fail(function(xhr, textStatus, errorThrown) {
		parseResultAJAX("error", xhr);
		$(box_loading).hide("slow", function() {
			$(box_loading_err).show("slow");
		});
		dlgPriorityAcctionClose = "load_error";
	});
});

getDlgCreatePriority().on('hide.bs.modal', function(e) {
	var dialog = $(this);

	getDlgCreatePriorityResetDialog(dialog);

	switch (dlgPriorityAcctionClose)
	{
		case 'needreload':
			showButtonReloadFreePBX();

		case 'refresh':
			priorityTableRefresh();  
			break;

		case 'redirect_id':
			sourceRedirect(dlgPriorityAcctionId);
			break;
	}

	dlgPriorityAcction = "";
	dlgPriorityAcctionId = "";
	dlgPriorityAcctionClose = "";
});

$(document).on('click', "button.btn-dlg-cancel", function(e) {
	e.preventDefault();

	var btn = $(this);
	var dialog = $(btn).closest('.modal');

	dlgPriorityAcctionClose = "cancel";
	$(dialog).modal('hide');
});

$(document).on('click', "button.btn-dlg-save", function(e) {
	e.preventDefault();
	
	var btn = $(this);
	var dialog = $(btn).closest('.modal');
	var body = $(dialog).find('.modal-body');
	var form = $(body).find('form');

	var post_data = {
		module: "queueprio",
		command: "priority_update",
		dlg_mode: dlgPriorityAcction,
		form_data: {},
	};
	$.each($(form).serializeArray(), function() {
		if (this.value == "popover") {
			return;
		}
		post_data['form_data'][this.name] = this.value;
	});
	$.post(window.FreePBX.ajaxurl, post_data, function(data) {
		if (! data.status) {
			if(data.warnInvalid !== undefined) {
				warnInvalid($("#"+data.warnInvalid));
			}
			fpbxToast(data.message, '', "error");
			dlgPriorityAcctionClose = "save_status_error";
		} else {
			if (data.redirect !== undefined) 
			{
				dlgPriorityAcctionClose = "";
				window.location.replace(data.redirect);
			}
			else if (data.redirect_id !== undefined) 
			{
				dlgPriorityAcctionClose = "redirect_id";
				dlgPriorityAcctionId = data.redirect_id;
			}
			else
			{
				dlgPriorityAcctionClose = "refresh";
			}
			if (data.needreload)
			{
				dlgPriorityAcctionClose = "needreload";
			}
			$(dialog).modal('hide');
		}
	})
	.fail(function(xhr, textStatus, errorThrown) {
		parseResultAJAX("error", xhr);
		dlgPriorityAcctionClose = "save_error";
	})
	.always(function() {
		
	});
});