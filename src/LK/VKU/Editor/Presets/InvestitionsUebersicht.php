<?php

namespace LK\VKU\Editor\Presets;

use LK\PXEdit\Preset;

/**
 * Description of InvestitionsUebersicht
 *
 * @author Maikito
 */
class InvestitionsUebersicht extends Preset {

  var $category = 'sonstiges';
  var $title = 'Investitionsübersicht';

  function getDefaultValues(){

    $sample_table = [
      0 => [
        '<strong>Bedarf des Kunden</strong>',
        '<strong>Merkmal unserer Leistung</strong>',
        '<strong>Nutzen des Kunden</strong>',
        '<strong>Mediawert</strong>',
        '<strong>Investition</strong>',
      ],
      1 => [
        '<p>Was braucht der Kunde?</p><p>Beispiel:</p>',
        '<p>Was bieten wir mit unseren Medien?</p>',
        '<p>Wie viel Zielgruppe erreicht?</p>',
        '<p>Preis laut Preisliste</p>',
        '<p>Angebotspreis oder Paketpreis über mehrere Zeilen</p>',
      ],
      2 => [
        '<p>100 Termine für Probefahrten am Tag der offenen Tür</p>',
        '<p>- 2 Anzeigen Mi+Sa,<br />- 120mm / 3 Spalten<br />- Ausgabe xy</p>',
        '<p>Sie erreichen zweimal 120.000 Autointeressierte</p>',
        '<p>1650,- </p>',
        '<p>1.490,-</p>',
      ],
      3 => [
        '<p>Wirkungsmotive</p>',
        '<p>2 Wirkungsmotive aus dem Lokalkönig</p>',
        '<p>Aufmerksamkeits- und Überzeugungseffekt werden Zielgruppen spezifisch genutzt</p>',
        '<p>Grafikerkosten<br />2 Std. x 60 €<br />120,-</p>',
        '<p>80,-</p>',
      ],
      4 => [
        '',
        '',
        '',
        '<p>1.770,-</p>',
        '<p>1.570,-</p>',
      ],
      5 => [
        '',
        '',
        '',
        '<p>Summe A</p>',
        '<p>Summe B</p>',
      ],
      6 => [
        '',
        '',
        '',
        '<p>Preisvorteil</p>',
        '<p>Summe A-B<br />200,-</p>',
      ],
    ];

    $value = new \stdClass();
    $value -> layout = 'layout-full-investiotion';
    $value -> title = $this -> title;
    $value -> active = 0;

    $value -> content = array();

    $value -> content[] = [
      'id' => 1,
      'widget' => 'table',
      'rows' => $sample_table,
    ];

    return $value;
  }

  public function getAvailableLayouts(){
    return [
      'layout-full-investiotion',
    ];
  }

  function getWidgetOptions(){
    return array(
      'layout_content' => 0,
      'change_layout' => 0,
      'change_input' => 0,
      'page_title' => 'Investitionsuebersicht',
    );
  }
}
