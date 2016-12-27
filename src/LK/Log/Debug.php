<?php

/*
 * USAGE
 * \LK\Log\Debug(""message")->save();
 * lk_note_debug("message");
 */

namespace LK\Log;

/**
 * Description of DebugLog
 * 
 * @author Maikito
 */
class Debug extends LogInterface {
    
    function __construct($message) {
        $this -> init("debug", $message);
    
    return $this;    
    }
}
