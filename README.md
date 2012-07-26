TransactionPDO is a wrapper for the PDO object in PHP.

It adds the ability to nest transactions as well as a nice syntax for writing transactions with a closure syntax.


== Usage ==

    $db = new TransactionPDO($connectDSN);

    $success = $db->transaction(function() use ($db) {
      $db->exec("Insert or whatever");
      if($rollback) {
        return false; //rollback
      } elseif($exception) {
        throw new Exception("This will rollback and thrown outside");
      } else {
        return true; //commit
      }
    });

    if($success) { 
      //committed 
    } else {
      //rollback
    }
  




== Thanks ==

Thanks go to Kenny Net for his initial implementation here: [http://www.kennynet.co.uk/2008/12/02/php-pdo-nested-transactions/]

== License ==

[http://www.gnu.org/licenses/gpl-3.0-standalone.html](GNU General Public License v3.)
