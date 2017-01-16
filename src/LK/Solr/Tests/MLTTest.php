<?php
namespace LK\Solr\Tests;

use LK\Tests\TestCase;
use LK\UI\Kampagne\Picture;

/**
 * Description of MLTTest
 *
 * @author Maikito
 */
class MLTTest extends TestCase {
 
  function build(){
    
    $search = new \LK\Solr\Search();
    
    $dbq = \db_query("SELECT nid FROM node WHERE type='kampagne' AND status='1' ORDER BY RAND() LIMIT 1");
    $all = $dbq -> fetchObject();
    
    $nodes = $search ->moreLikeThis($all -> nid);
    $this -> printLine('More like this<br />Zufällige Kampagne', Picture::get($all -> nid));
    
    $output = [];
    
    foreach ($nodes as $nid){
      $output[] = Picture::get($nid, ['height' => 80, 'width' => 80]);
    }
    
    $this->printLine('', implode(" ", $output));
  }  
}
