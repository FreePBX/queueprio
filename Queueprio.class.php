<?php
namespace FreePBX\modules;
class Queueprio implements \BMO {

	const ASTERISK_SECTION = 'app-queueprio';

	private $priorityDefault = array(
		"id" 		=> false,
		"name" 		=> "",
		"priority"  => 0,
		"dest" 		=> "",
		"inUsed" 	=> false,
	);

	private $table_name = "queueprio";

	public function __construct($freepbx = null)
	{
		if ($freepbx == null) {
			throw new \Exception("Not given a FreePBX Object");
		}
		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;

		global $active_modules;
		$this->active_modules = &$active_modules;
	}

	public function install() {}
  	public function uninstall() {}

	public function doConfigPageInit($page)
	{
    	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';
    	if (isset($_REQUEST['delete'])) $action = 'delete';

		$isNestedForm = isset($_REQUEST['fw_popover']) ? true : false;
		$formNestedBy = ($isNestedForm && empty($_REQUEST['fw_popover_process'])) ? "" : $_REQUEST['fw_popover_process'];
		$run_ok = false;
		
		$data_priority = array(
			'id'        => isset($_REQUEST['priority_id']) ? $_REQUEST['priority_id'] :  $this->priorityDefault['id'],
			'name'      => isset($_REQUEST['priority_name']) ? $_REQUEST['priority_name'] :  $this->priorityDefault['name'],
			'priority'  => isset($_REQUEST['priority_priority']) ? $_REQUEST['priority_priority'] :  $this->priorityDefault['priority'],
			'dest'      => $this->priorityDefault['dest'],
		);

    	if ( !empty($_REQUEST['goto0']) && !empty($_REQUEST['goto0'].'0') ) {
			$data_priority['dest'] = $_REQUEST[$_REQUEST['goto0'].'0'];
    	}
		
		switch ($action) {
			case 'add':
				$id = $this->addPriority($data_priority['name'], $data_priority['priority'], $data_priority['dest']);
				if (! \DB::IsError($id) && $id !== false) {
					$run_ok = true;
					if (! $isNestedForm) {
						unset($_REQUEST['view']);
					}
				}
			break;

			case 'edit':
				$result_update = $this->updatePriority($data_priority['id'], $data_priority['name'], $data_priority['priority'], $data_priority['dest']);
				if ($result_update) { 
					$run_ok = true;
					if (! $isNestedForm) {
						$_REQUEST['extdisplay'] = $data_priority['id'];
						unset($_REQUEST['view']);
					}
				}
				
			break;

			case 'delete':
			 	$result = $this->delPriority($data_priority['id']);
			 	if(! \DB::IsError($result)) { $run_ok = true; }
			break;
		}
		if ($run_ok)
		{
			needreload();
			if (! $isNestedForm)
			{
				header('Location: config.php?display=queueprio');
				exit();
			}
		}
  	}

	public function getRightNav($request, $params = array())
	{
		$data_return = "";
		$data = array(
			"queueprio" => $this,
			"request" 	=> $request,
		);
		$data = array_merge($data, $params);
		switch($request['view'])
		{
			case '':
			case 'list':
				//No show Nav
			break;
			default:
				$data_return = load_view(__DIR__.'/views/rnav.php', $data);
			break;
		}
		return $data_return;
	}

	public function showPage($page, $params = array())
	{
		$data = array(
			"queueprio"	=> $this,
			'request'	=> $_REQUEST,
			'page' 		=> $page,
		);
		$data = array_merge($data, $params);
		switch ($page) 
		{
			case 'main':
				$data_return = load_view(__DIR__."/views/page.main.php", $data);
				break;

			case "grid":
				$data_return = load_view(__DIR__."/views/view.grid.php", $data);
			break;

			case 'priority.add':
				$data['dPriority']['default'] = $this->getPriorityDefault();
				$data['dPriority']['data'] = $this->getPriorityDefault();
				$data_return = load_view(__DIR__.'/views/view.grid.add.php', $data);
			break;

			case "form":
				$data_return = load_view(__DIR__."/views/view.form.php", $data);
				break;

			default:
				$data_return = sprintf(_("Page Not Found (%s)!!!!"), $page);
		}
		return $data_return;
	}

	public function getActionBar($request)
	{
		$buttons = array();
		switch($request['display'])
		{
			case 'queueprio':
				$buttons = array(
					'delete' => array(
						'name' => 'delete',
						'id' => 'delete',
						'value' => _('Delete')
					),
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset')
					),
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit')
					)
				);
				if (empty($request['extdisplay'])) {
					unset($buttons['delete']);
				}
        		if(!isset($request['view'])){
          			$buttons = array();
        		}
			break;
		}
		return $buttons;
	}

	public function ajaxRequest($req, &$setting)
	{
		// ** Allow remote consultation with Postman **
		// ********************************************
		// $setting['authenticate'] = false;
		// $setting['allowremote'] = true;
		// return true;
		// ********************************************
		switch($req) {
			case 'priority_list':
			case 'priority_get':
			case 'priority_update':
			case 'priority_del':
			case 'priority_dialog':
				return true;
			default:
				return false;
		}
	}

	public function ajaxHandler() {
		$command = isset($_REQUEST['command']) ? trim($_REQUEST['command']) : '';
		$data_return = array("status" => false, "message" => sprintf(_("Command [%s] not valid!"), $command));

		switch ($command)
		{
			case 'priority_list':
				$data_return = $this->getPrioritiesFull();
				break;

			case 'priority_get':
				$id = filter_input(INPUT_POST, 'priority_id', FILTER_SANITIZE_NUMBER_INT);
				if (empty($id))
				{
					$data_return = array("status" => false, "message" => _('Missing ID!'));
				}
				else
				{
					$data = $this->getPriority($id);
					if (empty($data))
					{
						$data_return = array("status" => false, "message" => sprintf(_('ID (%s) Not found!'), $id));
					}
					else
					{
						$data_return = array(
							"status" => true, 
							"priority" => $data, 
							"drawselects" => drawselects(empty($data['dest']) ? '' : $data['dest'], 0, false, false),
						);
					}
				}
				unset($id);
				break;

			case 'priority_update':
				$dlg_mode 		= filter_input(INPUT_POST, 'dlg_mode', FILTER_SANITIZE_STRING, ['options' => array('default' => "")]);
				$fData 	  		= filter_input(INPUT_POST, 'form_data', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
				$priority_name  = preg_replace('/\s/i', '_', preg_replace('/\+/i', '_', trim($fData['priority_name'])));
				$priority_id	= $fData['priority_id'];
				$type 	  		= $fData['goto0'];

				if (empty($priority_name))
				{
					$data_return = array("status" => false, "message" => _("Priority name cannot be empty!"), "warnInvalid" => "priority_name");
				}
				elseif (($dlg_mode != "edit") && (! empty($this->getPriorityIdByName($priority_name))))
				{
					$data_return = array("status" => false, "message" => _("You cannot create a priority the same name as an existing priority!"), "warnInvalid" => "priority_name");
				}
				elseif (($dlg_mode == "edit") && (! $this->isExistPriority($priority_id)))
				{
					$data_return = array("status" => false, "message" => _("The priority with the specified ID could not be located!"));
				}
				elseif (($dlg_mode == "edit") && (! empty($this->getPriorityIdByName($priority_name))) && ($priority_id != $this->getPriorityIdByName($priority_name)))
				{
					$data_return = array("status" => false, "message" => _("You cannot rename a priority to the same name of an existing priority!"), "warnInvalid" => "priority_name");
				}
				elseif (empty($type)) {
					$data_return = array("status" => false, "message" => _("You have not selected a destination!"), "warnInvalid" => "goto0");
				}
				else
				{
					$data = array(
						'id' 		=> $priority_id,
						'name' 		=> $priority_name,
						'priority' 	=> $fData['priority_priority'],
						// 'dest' 		=> empty($fData[$type.'0']) ? array() : array($type, $fData[$type.'0'])
						'dest' 		=> empty($fData[$type.'0']) ? "" : $fData[$type.'0']
					);
					switch ($dlg_mode)
					{
						case "new":
							$return_new = $this->addPriority($data['name'], $data['priority'], $data['dest']);
							if (empty(! $return_new))
							{
								$data_return = array("status" => true, "needreload" => true);
								needreload();
							}
							else
							{
								$data_return = array("status" => false, "message" => _("Error Creating Priority!"));
							}
							break;

						case "edit":
							$return_update = $this->updatePriority($data['id'], $data['name'], $data['priority'], $data['dest']);
							if ($return_update)
							{
								$data_return = array("status" => true, "needreload" => true);
								needreload();
							}
							else
							{
								$data_return = array("status" => false, "message" => _("Error Updating Priority!"));
							}		
							break;

						default:
							$data_return = array("status" => false, "message" => sprintf(_("The '%s' option is not supported!"), $dlg_mode));
						break;
					}
				}
				break;

			case 'priority_del':
				$list_id = filter_input(INPUT_POST, 'priority_id', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
				if (! is_array($list_id))
				{
					$data_return = array("status" => false, "message" => _("Invalid data received!"));
				}
				else
				{
					$somethingWasDeleted = false;
					$delete_error = array();
					foreach ($list_id as $id)
					{
						if (! $this->isExistPriority($id)) { continue; }
						if (! $this->delPriority($id)) {
							$delete_error[] = $id;
							continue;
						}
						$somethingWasDeleted = true;
					}
					if (empty($delete_error) == true)
					{
						$data_return = array("status" => true, "message" => _("Delete Sccessfully"));
					}
					else
					{
						if (count($delete_error) == 1)
						{
							$data_error = $this->getPriority($delete_error[0]);
							$data_return = array("status" => false, "message" => sprintf( _("Could not delete priority '%s'!"), $data_error['name']));
							unset($data_error);
						}
						else
						{
							$data_return = array("status" => false, "message" => _("Some priorities could not be deleted!"));
						}
					}
					if ($somethingWasDeleted) {
						$data_return['needreload'] = true;
						needreload();
					}
					unset($delete_error);
				}
				unset($list_id);
				break;

			case 'priority_dialog':
				$dlg_mode 	 = filter_input(INPUT_POST, 'dlg_mode', FILTER_SANITIZE_STRING, ['options' => array('default' => "")]);
				$priority_id = filter_input(INPUT_POST, 'priority_id', FILTER_SANITIZE_STRING, ['options' => array('default' => "")]);

				if ($dlg_mode == "edit")
				{
					$priority_data = $this->getPriority($priority_id);
					if (empty($priority_data))
					{
						$data_return = array("status" => false, "message" => sprintf(_('ID (%s) Not found!'), $priority_id));
					}
					else
					{
						$data_return = array("status" => true, "priority" => $priority_data, 'drawselects' => drawselects(empty($priority_data['dest']) ? '' : $priority_data['dest'], 0, false, false));
					}
				}
				else
				{
					$data_return = array("status" => true, "priority" => $this->getPriorityDefault(), 'drawselects' => drawselects('', 0, false, false));
				}
				unset($priority_data);
				unset($priority_id);
				unset($dlg_mode);
			break;
		}
		return $data_return;
	}

	public function getallqprio($id ='')
	{
		$dbh = $this->db;
		$sql = sprintf("SELECT description FROM %s %s ORDER BY description", $this->table_name, $id ? 'WHERE queueprio_id != :id' : '');
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array(":id" => $id));
		$results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if(!$results) {
			return array();
		}
		$resu = array();
		foreach($results as $res) {
			$this->fixNameColumnTable($res);
			$resu[] = $res['name'];
		}
		return $resu;
	}
	
	public function getPriorityDefault()
	{
		return $this->priorityDefault;
	}

	public function addPriority($name, $priority, $dest)
	{
		$name = trim($name);
		$priority = is_numeric($priority) ? $priority : $this->priorityDefault['priority'];
		$dest = trim($dest);

		if (empty($name))
		{
			return false;
		}
		elseif($this->getPriorityIdByName($name) != "")
		{
			return false;
		}
		elseif(empty($dest))
		{
			return false;
		}
		else
		{
			$sql  = sprintf("INSERT INTO %s (description, queue_priority, dest) VALUES (?,?,?)", $this->table_name);
			$stmt = $this->db->prepare($sql);
			try
			{
				$stmt->execute(array($name, $priority, $dest));
			}
			catch(\Exception $e)
			{
				return new \DB_Error($e);
			}
			$id = $this->getPriorityIdByName($name);
			return (empty($id) ? false : $id);
		}
	}
	
	public function updatePriority($id, $name, $priority, $dest)
	{
		$name 	  = trim($name);
		$priority = is_numeric($priority) ? $priority : $this->priorityDefault['priority'];
		$dest 	  = trim($dest);

		$valdiate_id   		= $this->getPriorityIdByName($name);
		$validete_priority 	= $this->getPriority($id);

		if (empty($validete_priority))
		{
			// This priority does not exist in the database!
			return false;
		}
		elseif (($valdiate_id != "") && ($valdiate_id != $id))
		{
			// There is already a priority with that name and it's not me!
			return false;
		}
		elseif(empty($dest))
		{
			// Destiny is missing
			return false;
		}
		else
		{
			$sql  = sprintf("UPDATE %s SET `description` = :name, `queue_priority` = :priority, `dest` = :dest WHERE queueprio_id = :id", $this->table_name);
			$stmt = $this->db->prepare($sql);
			try
			{
				$stmt->execute(array(
					":name" 	=> $name,
					":priority" => $priority,
					":dest" 	=> $dest,
					":id" 		=> $id,
				));
			}
			catch(\Exception $e)
			{
				return new \DB_Error($e);
			}
			return true;
		}	
	}

	public function getPriorityIdByName($name)
	{
		if (! empty(trim($name)))
		{
			$sql  = sprintf("SELECT * FROM %s WHERE description = :name", $this->table_name);
			$stmt = $this->db->prepare($sql);
			$stmt->execute(array(":name" => $name));
			$result = $stmt->fetch(\PDO::FETCH_ASSOC);
			if($result)
			{
				$this->fixNameColumnTable($result);
				return $result['id'];
			}
		}
		return "";
	}

	public function isExistPriority($id)
	{
		if (! empty($id) and is_numeric($id))
		{
			// Don't use getPriority() to avoid infinite loop problems
			$sql  = sprintf("SELECT COUNT(*) FROM %s WHERE queueprio_id = :id", $this->table_name);
			$stmt = $this->db->prepare($sql);
			$stmt->execute(array(":id" => $id));
			$count = $stmt->fetchColumn();
			return $count == 0 ? false : true;
		}
		return false;
	}

	public function inUsedPriority($id)
	{
		if (empty($id)) { return false; }
		$usage_list = $this->hookDestinationUsage($id);
		return !empty($usage_list);
	}

	public function getPriority($id)
	{
		$sql  = sprintf("SELECT * FROM %s WHERE queueprio_id = :id", $this->table_name);
		$stmt = $this->db->prepare($sql);
		try
		{
			$stmt->execute(array(":id" => $id));
			$result = $stmt->fetch(\PDO::FETCH_ASSOC);
		}
		catch(\Exception $e)
		{
			return new \DB_Error($e);
		}
		if(!$result) {
			return array();
		}
		$this->fixNameColumnTable($result);

		if (empty($result['dest'])) {
			$result['dest_pretty'] = _("Undefined");
		}
		else
		{
			$dest_pretty = $this->getDestPriorities($result['dest']);
			$result['dest_pretty'] = empty($dest_pretty[$result['dest']]) ? $result['dest'] : $dest_pretty[$result['dest']];
			unset($dest_pretty);
		}

		$result = array_merge($result, $this->getInUsedData($id));
		return $result;
	}

	public function getPriorities()
	{
		$dbh = $this->db;
		$sql = sprintf("SELECT * FROM %s ORDER BY description", $this->table_name);
		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		$results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if(!$results) {
			return array();
		}
		foreach ($results as &$result)
		{
    		$this->fixNameColumnTable($result);
		}
		return $results;
	}

	public function getPrioritiesFull()
	{
		$priorities = $this->getPriorities();
		if (!empty($priorities))
		{
			$all_dest = array();
			foreach ($priorities as &$priority)
			{
				if (! empty($priority['dest']))
				{
					$all_dest[] = $priority['dest'];
				}
				else
				{
					$priority['dest_pretty'] = _("Undefined");
				}
				$priority = array_merge($priority, $this->getInUsedData($priority['id']));
			}
			if (! empty($all_dest))
			{
				$all_dest_pretty = $this->getDestPriorities($all_dest);
				foreach ($priorities as &$priority)
				{
					if (! array_key_exists($priority['dest'], $all_dest_pretty)) {
						continue;
					}
					$priority['dest_pretty'] = empty($all_dest_pretty[$priority['dest']]) ? $priority['dest'] : $all_dest_pretty[$priority['dest']];
				}
				unset($all_dest_pretty);
			}
			unset($all_dest);
		}
		return $priorities;
	}

	public function delPriority($id)
	{
		if (!empty($id))
		{
			$sql = sprintf("DELETE FROM %s WHERE queueprio_id = :id", $this->table_name);
			$stmt = $this->db->prepare($sql);
			try
			{
				$stmt->execute(array(":id" => $id));
			}
			catch(\Exception $e)
			{
				return new \DB_Error($e);
			}
		}
		return true;
	}

	public function delAllPriorities()
	{
		$sql = sprintf("TRUNCATE TABLE %s", $this->table_name);
		$sth = $this->db->prepare($sql);
		try
		{
			$sth->execute();
		}
		catch(\Exception $e) 
		{
			return new \DB_Error($e);
		}
		return true;
	}

	private function getInUsedData($id)
	{
		$data_return = array(
			'inUsed' => false,
			'inUsedData' => array(
				'count'=> 0,
				'count_pretty' => "",
				'list' => array()
			),
		);
		if (! empty($id) && $this->isExistPriority($id))
		{
			$data_return['inUsed'] = $this->inUsedPriority($id);
			if ($data_return['inUsed'])
			{
				$ls_used = $this->hookDestinationUsage($id, false);
				$usage_list = $this->hookDestinationUsage($id);
				$data_return['inUsedData'] = array(
					'count' => array_sum(array_map("count", $ls_used)),
					'count_pretty' => str_replace("&nbsp;", "", $usage_list['text']),
					'list' => $ls_used,
				);
			}
		}
		return $data_return;
	}

	private function getDestPriorities($dests)
	{
		if (! is_array($dests)) {
			$dests = array($dests);
		}
		$get_hook = $this->hookIdentifyDestinations($dests);
		// Example Return:
		// [ext-queues,100,1] => Array
        // (
        //     [queues] => Array
        //         (
        //             [description] => Queue 100 : Entrada
        //             [edit_url] => config.php?display=queues&view=form&extdisplay=100
        //         )
        // )

		$return_data = array();
		foreach ($get_hook as $dest => $value)
		{
			$return_data[$dest] = "";
			if ( empty($value) || ! is_array($value)) {
				continue;
			}
			$dest_pretty = $value[array_key_first($value)];
			if (empty($dest_pretty) || ! is_array($dest_pretty)) {
				continue;
			}
			if (! empty($dest_pretty['description']))
			{
				$return_data[$dest] = $dest_pretty['description'];
			}
		}
		return $return_data;
	}

	// TODO: Temporary fix until migrating data to kvstore.
	private function fixNameColumnTable(&$data)
	{
		if (isset($data['queueprio_id']))	// Id Unique
		{
			$data['id'] = $data['queueprio_id'];
			unset($data['queueprio_id']);
		}
		if (isset($data['description']))	// Name Priority Unique
		{
			$data['name'] = $data['description'];
			unset($data['description']);
		}
		if (isset($data['queue_priority']))	// Priority
		{
			$data['priority'] = $data['queue_priority'];
			unset($data['queue_priority']);
		}
	}

	//Destinations hooks
	public function hookDestinationUsage($id, $pretty = true)
	{
		$result = array();
		if (! empty($id))
		{
			$dest = array($this->getDest($id));
			if ($pretty)
			{
				$result = \FreePBX::Destinations()->destinationUsageArray($dest);
			}
			else
			{
				$result = \FreePBX::Destinations()->getAllInUseDestinations($dest);
			}
		}
		return $result;
	}

	public function hookIdentifyDestinations($dests)
	{
		return \FreePBX::Destinations()->identifyDestinations($dests);
	}

	public function getDest($exten) {
		return sprintf('%s,%s,1', self::ASTERISK_SECTION, $exten);
	}
	public function destinations()
	{
		$extens = array();
		foreach ($this->getPriorities() as $k => $v )
		{
			$extens[] = array(
				'destination' => $this->getDest($v['id']),
				'description' => $v['name']
			);
		}
		if (! empty($extens)) { return $extens; }
		else 				  { return null; }
	}
	public function destinations_check($dest=true)
	{
		$destlist = array();
		if (is_array($dest) && empty($dest)) { return $destlist; }

		$type = $this->type_destination();
		foreach ($this->getPriorities() as $k => $v )
		{
			if (($dest !== true && is_array($dest))) 
			{
				if (! in_array($v['dest'], $dest)) { continue; }
			}
			$destlist[] = array(
				'dest' 		  => $v['dest'],
				'description' => sprintf(_("Queue Priority: %s"), $v['name']),
				'edit_url' 	  => sprintf('config.php?display=queueprio&type=%s&extdisplay=%s' ,$type, urlencode($v['id'])),
			);
		}
		return $destlist;
	}
	public function destinations_change($old_dest, $new_dest)
	{
		$sql  = sprintf("UPDATE %s SET `dest` = :dest_new WHERE dest = :dest_old", $this->table_name);
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array(":dest_new" => $new_dest, ":dest_old" => $old_dest));
	}
	public function destinations_getdestinfo($dest)
	{
		$srt_section = sprintf("%s,", self::ASTERISK_SECTION);
		if (substr(trim($dest),0, strlen($srt_section)) == $srt_section)
		{
			$exten = explode(',', $dest);
			$exten = $exten[1];
			$thisexten = $this->getPriority($exten);
			if (! empty($thisexten))
			{
				$type = $this->type_destination();
				return array(
					'description' => sprintf(_("Queue Priority: %s"), $thisexten['name']),
					'edit_url' 	  => sprintf('config.php?display=queueprio&view=form&type=%s&extdisplay=%s' ,$type, urlencode($exten)),
				);
			}
			return array();
		}
		return false;
	}
	public function destinations_identif($dests)
	{
		if (! is_array($dests)) {
			$dests = array($dests);
		}
		$return_data = array();
		foreach ($dests as $target)
		{
			$info = $this->destinations_getdestinfo($target);
			if (!empty($info))
			{
				$return_data[$target] = $info;
			}
		}
		return $return_data;
	}
	private function type_destination ()
	{
		return isset($this->active_modules['queueprio']['type']) ? $this->active_modules['queueprio']['type'] : 'setup';
	}

	//Dialplan hooks
	public function myDialplanHooks() {
		return true;
	}
	public function doDialplanHook(&$ext, $engine, $priority) {
		if ($engine != "asterisk") { return; }
		$section = self::ASTERISK_SECTION;
		foreach ($this->getPriorities() as $row)
		{
			$srt_noop = sprintf('Changing Channel to queueprio: %s (%s)', $row['priority'], $row['name']);
			$ext->add($section, $row['id'], '', new \ext_noop($srt_noop));
			$ext->add($section, $row['id'], '', new \ext_setvar('_QUEUE_PRIO', $row['priority']));
			$ext->add($section, $row['id'], '', new \ext_goto($row['dest']));
		}
	}

	//BulkHandler hooks
	public function bulkhandlerGetTypes() {
		return array(
			'queueprio' => array(
				'name' => _('Queue Priorities'),
				'description' => _('Import/Export Queue Priorities')
			)
		);
	}
	public function bulkhandlerGetHeaders($type) {
		switch($type){
			case 'queueprio':
				$headers = array();
				$headers['name'] = array(
					'type'	 	  => 'string',
					'required' 	  => true,
					'identifier'  => _("Name"),
					'description' => _('The descriptive name of this Queue Priority instance.')
				);
				$headers['priority'] = array(
					'type' 	  	  => 'number',
					'val_min'	  => 0,
					'val_max'	  => 20,
					'required' 	  => true,
					'identifier'  => _("Priority"),
					'description' => _('The Queue Priority to set 0 - 20')
				);
				$headers['dest'] = array(
					'type'	 	  => 'destination',
					'required' 	  => true,
					'identifier'  => _("Destination"),
					'description' => _("Destination"),
				);
			break;
		}
		return $headers;
	}
	public function bulkhandlerImport($type, $rawData, $replaceExisting = true) {
		switch($type)
		{
			case 'queueprio':
				foreach($rawData as $data)
				{
					if(empty($data['name'])){
						return array('status' => false, 'message'=> _('Name Required'));
					}
					if(! is_numeric($data['priority']) || $data['priority'] < 0 || $data['priority'] > 20){
						return array('status' => false, 'message'=> _('Priority Required'));
					}
					if(empty($data['dest'])){
						return array('status' => false, 'message'=> _('Destination Required'));
					}
					$id_current = $this->getPriorityIdByName($data['name']);
					if (empty($id_current))
					{
						if (empty($this->addPriority($data['name'], $data['priority'], $data['dest']))) {
							return array('status' => false, 'message'=> _('Error Add Priority'));
						}
					}
					else
					{
						if(!$replaceExisting)
						{
							continue;
						}
						$update = $this->updatePriority($id_current, $data['name'], $data['priority'], $data['dest']);
						if (\DB::IsError($update) || $update === false) {
							return array('status' => false, 'message'=> _('Error Update Priority'));
						}
					}
				}
			break;
		}
		return array('status' => true);
	}
	public function bulkhandlerExport($type) {
		$data = NULL;
		switch ($type) {
			case 'queueprio':
				$priorities = $this->getPriorities();
				if (! empty($priorities)) {
					$data = array();
					foreach ($priorities as $row)
					{
						$data[] = array(
							'name' 	=> $row['name'],
							'priority' 	=> $row['priority'],
							'dest' 	=> $row['dest'],
						);
					}
				}
			break;
		}
		return $data;
	}

}