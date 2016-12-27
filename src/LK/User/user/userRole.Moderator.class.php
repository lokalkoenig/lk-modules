<?php

namespace LK;

class Moderator extends User {
    
    var $verlag = 0;
    var $user_role = LK_USER_MODERATOR;
    var $telefon = false;
    var $role_name = 'Moderator';
    
    function isModerator() {
        return true;
    }
    
    function getVerlag(){
        return 0;
    }
    
    function getTeam(){
        return 0;
    }
    
    function hasRight($key) {
        return true;
    }
}