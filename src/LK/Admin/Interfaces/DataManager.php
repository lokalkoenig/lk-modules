<?php

namespace LK\Admin\Interfaces;

/**
 * Interface for User & Kampagnen Maintenance
 *
 * @author Maikito
 */
interface DataManager {

  /**
   * Removes the User-Data
   * 
   * @param \LK\User $acccount
   */
  function removeUserData(\LK\User $acccount);

  /**
   * Get User-Data
   *
   * @param \LK\User $acccount
   * @return int
   */
  function getUserDataCount(\LK\User $acccount);

  /**
   * Get the count of a Node
   *
   * @param \LK\Kampagne\Kampagne $kampagne
   * @return int
   */
  function getKampagnenCount(\LK\Kampagne\Kampagne $kampagne);

  /**
   * Removes the Data
   *
   * @param \LK\Kampagne\Kampagne $kampagne
   */
  function removeKampagnenData(\LK\Kampagne\Kampagne $kampagne);
}
