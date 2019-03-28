<?php
namespace FreePBX\modules\queueprio;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore($jobid){
		$configs = $this->getConfigs();
		foreach ($configs as $value) {
			foreach ($value as $k => $v) {
				$data = $this->loadDbentries($k, $v);
			}
		}
	}
	public function processLegacy($pdo, $data, $tablelist, $unknowntables){
		$this->restoreLegacyDatabase($pdo);
	}
}