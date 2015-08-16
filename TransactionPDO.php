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
    if($this->beginTransaction()) {
      try {
        $ret = call_user_func($call);
      } catch(Exception $e) {
        $this->rollBack();
        throw $e;
      }

      if($ret) {
        if(!$this->commit()) throw new Exception("Transaction was not committed.");
      } else {
        $this->rollBack();
      }

      return $ret;
    } else {
      throw new Exception("Transaction was not started.");
    }
  }

  public function beginTransaction() {
    
    if(!$this->nestable() || $this->transLevel == 0) {
      $ret = parent::beginTransaction();
      $this->transLevel++;
      return $ret;
    } else {
      $this->exec("SAVEPOINT LEVEL{$this->transLevel}");
      $this->transLevel++;
      return true;
    }

  }

  public function commit() {
    $this->transLevel--;
    if(!$this->nestable() || $this->transLevel == 0) {
      return parent::commit();
    } else {
      $this->exec("RELEASE SAVEPOINT LEVEL{$this->transLevel}");
      return true;
    }
  }

  public function rollBack() {
    $this->transLevel--;

    if(!$this->nestable() || $this->transLevel == 0) {
      return parent::rollBack();
    } else {
      $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transLevel}");
      return true;
    }
  }
}
