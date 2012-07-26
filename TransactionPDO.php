/*
 * License: GNU General Public License v3. 
 * http://www.gnu.org/licenses/gpl-3.0-standalone.html
 */

class TransactionPDO extends PDO {
  // Database drivers that support SAVEPOINTs.
  protected static $savepointTransactions = array("pgsql", "mysql");

  // The current transaction level.
  protected $transLevel = 0;

  protected function nestable() {
    return in_array($this->getAttribute(PDO::ATTR_DRIVER_NAME),
      self::$savepointTransactions);
  }
  
  function transaction($call) {
    $this->beginTransaction();

    try {
      $ret = call_user_func($call);
    } catch(Exception $e) {
      $this->rollBack();
      throw $e;
    }

    if($ret) {
      $this->commit();
    } else {
      $this->rollBack();
    }

    return $ret;
  }

  public function beginTransaction() {
    
    if(!$this->nestable() || $this->transLevel == 0) {
      parent::beginTransaction();
    } else {
      $this->exec("SAVEPOINT LEVEL{$this->transLevel}");
    }

    $this->transLevel++;
  }

  public function commit() {
    $this->transLevel--;
    if(!$this->nestable() || $this->transLevel == 0) {
      parent::commit();
    } else {
      $this->exec("RELEASE SAVEPOINT LEVEL{$this->transLevel}");
    }
  }

  public function rollBack() {
    $this->transLevel--;

    if(!$this->nestable() || $this->transLevel == 0) {
      parent::rollBack();
    } else {
      $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transLevel}");
    }
  }
}
