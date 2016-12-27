
<style>
    .row.document .col-xs-6, .row.document .col-xs-12  {
        background: #DDD;
        font-size: small;
        color: White;
        border: 2px White solid;
        text-align: center;
        
    }
    
    .row.document .col-half {
        height: 50px;
        line-height: 46px;
    }
    
    .row.document .col-full {
        height: 100px;
        line-height: 96px;
    }
    
    a.vkudoc-link:hover .row.document div.col-xs-6, 
        a.vkudoc-link:hover .row.document .col-xs-12 {
        background: lightsteelblue;
    }
    
    a.vkudoc-link {
        display: block;
    }
    
</style>
   
<div class="well well-white col-xs-8">
    <h4 style="margin-top:0">Neues Dokument anlegen</h4> 
    
    <div class="field-type-text form-wrapper form-group">
       <label for="edit_vku_title">Titel des Dokuments <sup class="form-required">(Pflichtfeld)</sup></label>
       <input class="text-full form-control form-text required" type="text" id="edit_vku_title" name="vku_title" value="Krass" size="60" maxlength="75">
       <p class="help-block">Maximallänge: 75 Zeichen, z.B. Regionalargumentation für Region</p>  
   </div>
    
      <div class="field-type-text form-wrapper form-group">
        <label for="edit_vku_title">Layout des Dokuments</label>
 
        <div class="row text-center" style="padding: 0 15px;">
        <?php foreach($layouts as $layout): ?>
                <div class="col-xs-3">
                     <a href="#" class="vkudoc-link">
                        <strong> <?php print $layout["title"]; ?></strong>
                        <?php print $layout["markup"]; ?>
                     </a>
                </div> 

            <div class="col-xs-1">&nbsp;</div>

        <?php endforeach; ?>
        </div>
      </div>
    <hr />
    
    <p><input type="submit" value="Speichern" class="btn btn-success" /></p>
</div>    