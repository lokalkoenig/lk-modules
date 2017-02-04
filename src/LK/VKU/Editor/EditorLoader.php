<?php
namespace LK\VKU\Editor;

/**
 * Description of EditorLoader
 *
 * @author Maikito
 */
class EditorLoader {
  static $loaded = false;

  public static function enable(){
    self::$loaded = true;
  }

  public static function isLoaded(){
    return self::$loaded;
  }
}
