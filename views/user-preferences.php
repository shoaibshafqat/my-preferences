<div id="prefs_container" >
    <div id="prefs_loading">Loading....</div>
    <div id="prefs_filters">
    <div class="prefs_filter_head"> Filter Items</div> 
    <div class="prefs_filter_options">
    <input type="hidden" value="<?php echo sanitize_text_field($filter_default); ?>" id="prefs_filter_value" name="prefs_filter_value" />
        <ul>
    <?php
        foreach ($filters as $value => $label) {
            $active = $value == $filter_default ? 'active-filter' : '';?>
                <li class="<?php echo esc_attr($active) ?> prefs_item_filter" data-value="<?php echo esc_attr($value) ?>">
                    <span class="selected-filter"> <i class="fa fa-check-square"></i></span>
                    <span class="not-selected-filter"> <i class="fa fa-square"></i></span>
                    <span><?php echo esc_attr($label) ?></span>
                    
                </li>
         <?php }
    ?>
        </ul>
    </div>
    <div id="prefs_reset">
        <button type="button" id="prefs_reset_btn"> <i class="fa fa-ban"></i> Reset</button>
    </div>
    <div style="clear:both"></div>
    </div>
  <div id="prefs_accordion"></div>
    <div id="cant_edit_msg">
        <div class="prefs_cant_edit_msg">
            <i class="fa fa-info-circle"></i>
            To modify preferences for items under {cat}, change this to "No Preference".
        </div>
    </div>
    <div id="can_edit_msg">
        <div class="prefs_can_edit_msg">
            <i class="fa fa-info-circle"></i>
            This will overide preferences for all items under {cat}.
        </div>

    </div>


</div>   