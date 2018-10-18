<?php
namespace FreePBX\modules\queueprio;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
    $settings = [
      'queueprio' => $this->FreePBX->Queueprio->dumpTable()
    ];
    $this->addConfigs($settings);
  }
}