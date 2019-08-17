<?php
namespace FreePBX\modules\queueprio;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore(){
		$configs = $this->getConfigs();
		$this->importTables($configs['tables']);
		$this->importFeatureCodes($configs['features']);
		$this->importAdvancedSettings($configs['settings']);
	}

	public function processLegacy($pdo, $data, $tables, $unknownTables){
		$tableName [] = 'queueprio';
		$this->restoreLegacyDatabase($pdo, $tableName);
		$this->restoreLegacyFeatureCodes($pdo);
	}
}
