<?php

/** Cronjob */
function lokalkoenig_admin_cron(){
    $manager = new \LK\Kampagne\SperrenManager();
    $manager ->checkOutDatedSperren();
}