<?php
namespace FreePBX\modules\Queueprio;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase
{
	public function runBackup($id,$transaction){
		$this->addDependency('queues');
		$configs = [
			'tables' => $this->dumpTables(),
			'features' => $this->dumpFeatureCodes(),
			'settings' => $this->dumpAdvancedSettings()
		];
		$this->addConfigs($configs);
	}
}