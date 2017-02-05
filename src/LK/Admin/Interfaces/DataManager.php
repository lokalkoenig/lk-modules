<?php

namespace LK\Admin\Interfaces;

/**
 * Interface for User & Kampagnen Maintenance
 *
 * @author Maikito
 */
abstract class DataManager {

   var $manager = null;

  /**
   * Constructor
   *
   * @param \LK\Admin\Data\DataManager $manager
   */
  final function __construct(\LK\Admin\Data\DataManager $manager) {
    $this->manager = $manager;
  }

  /**
   * Get the Data-Manager
   *
   * @return \LK\Admin\Data\DataManager
   */
  final function getManager(){
    return $this->manager;
  }


  /**
   * Removes the User-Data
   * 
   * @param \LK\User $acccount
   */
  abstract function removeUserData(\LK\User $acccount);

  /**
   * Get User-Data
   *
   * @param \LK\User $acccount
   * @return int
   */
  abstract function getUserDataCount(\LK\User $acccount);

  /**
   * Get the count of a Node
   *
   * @param \LK\Kampagne\Kampagne $kampagne
   * @return int
   */
  abstract function getKampagnenCount(\LK\Kampagne\Kampagne $kampagne);

  /**
   * Removes the Data
   *
   * @param \LK\Kampagne\Kampagne $kampagne
   */
  abstract function removeKampagnenData(\LK\Kampagne\Kampagne $kampagne);
}
