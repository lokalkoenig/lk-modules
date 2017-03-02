<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\VKU\Data;

/**
 * Description of Reader
 *
 * @author Maikito
 */
class Reader extends \views_handler_field {

  function render($values) {
    //ID if the value
    $value = $this->get_value($values);
    $dbq = db_query('SELECT * FROM lk_vku_data WHERE id=:id', [':id' => $value]);
    $all = $dbq -> fetchObject();

    $vku = \LK\VKU\VKUManager::getVKU($all -> vku_id);
    $page_info = '';

    if(!$vku){
      $vku_info = '<div class="pull-right text-center"><label class="label label-danger">Broken-VKU</label></div>VKU: ' . $all->vku_id . " is BROKEN: <br /><code class='small'>" . print_r($all, true) . '</code>';
    }
    else {
      $vku_info = "<label class='label label-primary'>". $vku ->getId() ." / ". $vku ->get('vku_title') ." / ". $vku ->getStatus() ."</label> / " . format_date($all -> data_created) . " von " . \LK\u($vku->getAuthor());
      $page_info .= '<div class="pull-right text-center"><label class="label label-success">#'. $all->id .' / ' . strtoupper($all -> data_module) . '</label>'
              . '<div style="margin-top: 10px;"><a href="'. url('vku/' . $vku->getId() . '/preview/' . $all -> id) .'" target="_blank" title="PDF-Ansicht"><span class="glyphicon glyphicon-sunglasses"></span></a> &nbsp;&nbsp;'
              . '<a href="#" title="Zur VKU wechseln"><span class="glyphicon glyphicon-link"></span></a>'
              . '</div></div>';

      if($all -> data_module === 'vku_documents'){
        $dbq_doc = db_query('SELECT * FROM lk_vku_documents WHERE id=:id', [':id' => $all -> data_entity_id]);
        $document = $dbq_doc->fetchObject();
        $page_info .= "Dynamische Seite: <strong>" . $document -> document_page_title . " / ". $document -> document_preset ."</strong>";
      }

      elseif($all -> data_module === 'default'){
        $page_info .= "Statische Seite: <strong>" .\LK\VKU\Pages\PageDefault::getPageTitle($all -> data_class) . "</strong>";
      }

      elseif($all -> data_module === 'node'){
        $page_info .= \LK\UI\Kampagne\Picture::get($all -> data_entity_id);

        if($all -> data_serialized){
          $unserialize = unserialize($all -> data_serialized);
          $page_info .= '<br /><code class="small">Einstellungen: ' . print_r($unserialize, true) . "</code>";
        }
      }

      $page_info .= '<br />';
    }




    return '<div class="well well-white well-log">' . $page_info . $vku_info . '</div>';
  }
}
