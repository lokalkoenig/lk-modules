<?php

function lokalkoenig_user_kampagnen($user){
   pathtitle('user/x/kampagnen');
   return views_embed_view('lk_user_kampagnen', 'attachment_1', $user -> uid);
}


?>