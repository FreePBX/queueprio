<?php
namespace FreePBX\modules\queueprio;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
    $configs = reset($this->getConfigs());
    foreach ($configs as $value) {
      foreach ($value as $k => $v) {
              $info = $data = $this->loadDbentries($k, $v);
          printf($info);
      }
    }
  }
  public function processLegacy($pdo, $data, $tablelist, $unknowntables, $tmpdir){
    $tables = ['queueprio'];
    $data = ['tables' => $tables, 'pdo' => $pdo];
    return $this->addDataDB($data);
  }
}