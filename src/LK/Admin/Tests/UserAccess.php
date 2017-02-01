<?php
namespace LK\Admin\Tests;
use LK\Tests\TestCase;

/**
 * Description of UserAccess
 *
 * @author Maikito
 */
class UserAccess extends TestCase {

  var $user1 = 358; // Bea Boot
  var $user2 = 375; // Boda Bald
  var $test_kampagne = 200;

  function printUser(\LK\User $account){

    $plz = [];
    $count = 0;
    $ausgaben = $account ->getCurrentAusgaben();
    foreach($ausgaben as $a){
      $ausgabe = \LK\get_ausgabe($a);
      $plz[] = $ausgabe ->getPlzFormatted();
      $count += count($ausgabe ->getPlz());
    }

    return (string)$account . " / " . implode(', ', $plz) . ' (' . $count .' PLZ)';
  }


  function printPurchaseCan(\LK\Kampagne\Manager\Access $manager){
    $result = $manager ->hasPurchaseAccess();

    if(!$result){
      return '<span class="label label-danger">Gesperrt</span>';
    }
    else {
      return '<span class="label label-success">Offen</span>';
    }
  }

  function build() {

    $account1 = \LK\get_user($this -> user1);
    $account2 = \LK\get_user($this -> user2);

    $verlag = $account1 ->getVerlagObject();
    $ausgaben = $verlag->getAusgaben();
    $ausgaben_int = [];
    while(list($key, $val) = each($ausgaben)){
      $ausgaben_int[] = $key;
      break;
    }

    $account1 ->setAusgaben($ausgaben_int);
    $account2 ->setAusgaben($ausgaben_int);

    $this -> printLine("Verlag", (string)$verlag);
    $this -> printLine("User 1", $this ->printUser($account1));
    $this -> printLine("User 2", $this ->printUser($account2));

    $kampagne = new \LK\Kampagne\Kampagne(node_load($this->test_kampagne));
    $nid = $kampagne -> getNid();

    $access_manager = new \LK\Kampagne\Manager\Access($kampagne, $account1);
    $access_manager2 = new \LK\Kampagne\Manager\Access($kampagne, $account2);

    $this -> printLine("Kampagne", \LK\UI\Kampagne\Picture::get($kampagne->getNid(), ['width' => 100, 'height' => 100]));
    $this -> printLine("User 1", $this -> printPurchaseCan($access_manager));
    $this -> printLine("User 2", $this ->printPurchaseCan($access_manager2));

    $vku = \LK\VKU\VKUManager::createEmptyVKU($account1, ['vku_title' => "Test-VKU"]);
    $vku ->addKampagne($nid);
    $this -> printLine("User 1 kauft Kampagne", (string)$vku ->getTitle());
    
    $lizenz_manager = new \LK\Kampagne\LizenzManager();
    $lizenz = $lizenz_manager ->create($nid, $vku);
    $this -> printLine("Lizenz", $lizenz -> getSummary());
    
    $this -> printLine("User 1", $this -> printPurchaseCan($access_manager));
    $this -> printLine("User 2", $this ->printPurchaseCan($access_manager2));
    $this -> printLine("Lösche Lizenz", (string)$vku ->getTitle());
    
    $lizenz -> remove();
    $vku -> remove();

    $this -> printLine("User 1", $this -> printPurchaseCan($access_manager));
    $this -> printLine("User 2", $this ->printPurchaseCan($access_manager2));

  }
}
