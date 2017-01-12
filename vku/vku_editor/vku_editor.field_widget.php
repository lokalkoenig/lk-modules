<?php
/**
 * @file
 * Implements a field to be used in the Field-API
 */

function vku_editor_field_schema($field) {
  $schema = array();
  $schema['columns']['option'] = array(
      'type' => 'varchar',
      'length' => 50,
      'not null' => FALSE
  );
  return $schema;
}

function vku_editor_field_info() {
  return array(
    'vku_editor_documents' => array(
      'label' => ('VKU-Editor-Documents'),
      'description' => ('Alle verfuegbaren Dokument-Typen'),
      'settings' => array('max_length' => 255),
      'instance_settings' => array(
        'text_processing' => 0,
      ),
      'default_widget' => 'options_select',
      'default_formatter' => 'states_field_options',
    ),
  );
}

function vku_editor_field_widget_info_alter(&$info) {
  $widgets = array(
    'options_select' => array('vku_editor_documents'),
  );
  foreach ($widgets as $widget => $field_types) {
    $info[$widget]['field types'] = array_merge($info[$widget]['field types'], $field_types);
  }
}

function vku_editor_options_list($field, $instance, $entity_type, $entity) {
  $manager = new \LK\VKU\Editor\Manager();
  $documents = $manager->getPresetsAvailable();
  
  $array = [];
  while(list($key, $val) = each($documents)){
    $array[$key] = $val['title'];
  }
  
  return $array;  
}

function states_field_field_formatter_info() {
  return array(
    'vku_editor_documents' => array(
      'label' => t('Default'),
      'field types' => array('vku_editor_documents'),
    ),
  );
}

function vku_editor_field_is_empty(){
  return false;
}
