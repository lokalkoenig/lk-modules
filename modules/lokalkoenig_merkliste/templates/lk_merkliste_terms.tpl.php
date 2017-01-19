<select name="items[]" class="items show-tick" multiple="multiple" title="Ihre bestehenden Merklisten">
      <?php while(list($key, $val) = each($select)){
        ?>
        <option value="<?php print $key; ?>"><?php print $val; ?></option>  
        <?php
      }?>
      
      <?php if(count($select) == 0) : ?>
       <option disabled="disabled">Bisher keine Merkliste vorhanden.</option>
      <?php endif; ?>
</select>