<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\VKU;

/**
 * Description of Vorlage
 *
 * @author Maikito
 */
class Vorlage {
  //put your code here
  
  /**
   * Copy a Vorlage to a existing VKU
   * 
   * 
   * @param VKUCreator $vku
   * @param Integer $vorlage_vku_id
   * @return VKUCreator
   */
  public static function takeOver(VKUCreator $vku, $vorlage_vku_id){

      $vorlage = new VKUCreator($vorlage_vku_id);
      // Check vorlage

      $copy = new VKUCreator($vku -> getId());

      // empty VKU
      $pages = $copy ->getPages();
      foreach($pages as $page){
          if(!in_array($page["data_class"], array("kampagne"))){
              $copy ->removePage($page["id"]);
              $copy ->removeCategory($page["data_category"]);
          } 
      }   

      $test = $copy ->getCategoryByName('print');

      foreach($test as $item){
          $copy ->removeCategory($item);
      }  

      $test2 = $copy ->getCategoryByName('online');
      foreach($test2 as $item){
          $copy ->removeCategory($item);
      }  

      $pagemanager = new \LK\VKU\PageManager();

      $entries = $vorlage ->getCategoryByName('print');
      $vorlage_catgegory_print = $vorlage -> getCategory($entries[0]);

      $entries = $vorlage ->getCategoryByName('online');
      $vorlage_catgegory_online = $vorlage -> getCategory($entries[0]);

      $category_print = $copy ->setDefaultCategory('print', $vorlage_catgegory_print -> sort_delta);
      $category_online = $copy ->setDefaultCategory('online', $vorlage_catgegory_online -> sort_delta);;

       // we create new once
      // we have now an VKU with only Kampagnen
      $pages_vorlage = $vorlage -> getPages();
      foreach($pages_vorlage as $page){
          $catgegory = $vorlage -> getCategory($page["data_category"]);

          if($catgegory -> category == 'print'){
              $cid = $category_print;
          }    
          elseif($catgegory -> category == 'online'){
              $cid = $category_online;
          }    
          else {
            // we add an new Category and the Page
            $cid = $copy -> setDefaultCategory($catgegory -> category, $catgegory -> sort_delta);
          }
        
          // Allow Modules to influence the result
          $serialized = null;
          if($page["data_serialized"]){
            $serialized = unserialize($page["data_serialized"]);
          }    
          
          // TODO Later, Complex types with COPY
          $copy -> data -> add($page["data_module"], $page["data_class"], $page["data_delta"], $page["data_active"], $page["data_entity_id"], $serialized, $cid);
      }

      // we got an empty one
      $final = new VKUCreator($copy -> getId());

    return $final;  
    }
}
