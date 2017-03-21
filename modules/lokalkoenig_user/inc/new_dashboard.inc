<?php

function lokalkoenig_user_new_dashboard(){

  drupal_set_title('Dashboard');
  lk_set_icon('home');
  lk_set_subtitle('März 2017');
  
  return theme('lokalkoenig_user_new_dashboard');
}
