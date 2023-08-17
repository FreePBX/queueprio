<?php
namespace FreePBX\modules\Queueprio;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore(){
		$configs = $this->getConfigs();
		$this->importAdvancedSettings($configs['settings']);
		$this->importFeatureCodes($configs['features']);
		$this->importTables($configs['tables']);
	}

	public function processLegacy($pdo, $data, $tables, $unknownTables){
		$tableName = [];
  $tableName [] = 'queueprio';
		$this->restoreLegacyDatabase($pdo, $tableName);
		$this->restoreLegacyFeatureCodes($pdo);
	}
}