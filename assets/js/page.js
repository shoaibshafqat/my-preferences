(function($) {
    $(function(){

    	//var WP_AJAX_URL = '/wp-admin/admin-ajax.php';
        function prefLog(content){
            if(myprefsInitVars.isDebug){
                //console.log(content);
            }
        }
    	var prefsAccordion = $("#prefs_accordion");
        var prefsLoading = $("#prefs_loading");
        
        var myprefs = MyPrefsCore();
		setPrefHooks();
		
    	function MyPrefsCore(){
    		var me = {};
            me.ajaxUrl = '';
            me.prefs = {};
            me.cats = [];
            me.prefTypes = [];
            me.baseImgUrl = '';
            me.messages = {};
            me.currentFamilyFilter = false;
            me.getPref = function(id){
                return me.prefs[id];
            };

            me.isCat = function(pref){
                pref = typeof pref === 'object' ? pref : me.getPref(pref);
                return pref.item.parent == null;
            };

            me.init =function(){
                me.ajaxUrl = myprefsInitVars.ajaxUrl;
                me.baseImgUrl = myprefsInitVars.imagesUrl;
                me.messages = myprefsInitVars.messages;
                me.loadData();
            };

    		me.loadData = function(){
                me.accordionLoading(true);
				var response = $.parseJSON(myprefsInitVars.getUserPrefs);
				
				me.accordionLoading(false);
				if(me.responseOk(response)){

					var data = response.data;
					me.prefs = data.preferences;
					me.cats = data.categories;
					me.prefTypes = data.prefTypes;

					me.displayItems();  
					 prefsAccordion.accordion({
							collapsible: true, 
							clearStyle: false, 
							active: false,
							autoHeight: false,
							heightStyle: "content", 
							navigation: true  });
				}
				else{
					prefsAccordion.html(me.messages.error.itemsNotLoaded);
					
				}
				prefLog(response);
                    
                /*$.ajax({
                    url: me.ajaxUrl,
                    dataType: 'json',
                    data: {
                        action : 'myprefs_load_data'
                    },
                    success: function(response) {
                        me.accordionLoading(false);
                        if(me.responseOk(response)){

                            var data = response.data;
                            me.prefs = data.preferences;
                            me.cats = data.categories;
                            me.prefTypes = data.prefTypes;

                            me.displayItems();  
                             prefsAccordion.accordion({
                                    collapsible: true, 
                                    clearStyle: false, 
				    active: false,
                                    autoHeight: false,
                                    heightStyle: "content", 
                                    navigation: true  });
                        }
                        else{
                            prefsAccordion.html(me.messages.error.itemsNotLoaded);
                            
                        }
                        prefLog(response);
                    },
                    error: function(response) {
                        me.accordionLoading(false);
                        prefLog(response);
                        me.errorMsg(me.messages.error.itemsNotLoaded);
                    }
                });*/
    			
    		};
    		
            me.save = function(id){
                var input = me.itemInput(id);
                var pref = me.getPref(id);
                if(!pref){
                    me.errorMsg(me.messages.error.itemNotFound);
                    return;
                }
                if(input.val() == pref.type.id){
                    me.successMsg(me.messages.general.prefUpToDate);
                    return;
                }

                me.itemBtn(id).attr('disabled','disabled');
                
                $.ajax({
                    url: me.ajaxUrl,
                    dataType: 'json',
                    method: 'POST',
                    data: {
                        action : 'myprefs_update_pref',
                        prefId : pref.id,
                        itemId : id,
                        prefType : input.val()
                    },
                    success: function(response) {
                        if(me.responseOk(response)){
                            me.successMsg(me.messages.success.prefUpdated);
                            me.updatePrefs(response.data.preferences, id);
                        }
                        else{
                            me.errorMsg(me.messages.error.prefNotUpdated);
                        }
                        prefLog(response);
                        me.itemBtn(id).removeAttr('disabled');
                    },
                    error: function(response){
                        me.errorMsg(me.messages.error.prefNotUpdated);
                        me.itemBtn(id).removeAttr('disabled');
                        prefLog(response);
                    }
                });
                
            };

            me.reset = function(){
                var btn = $('#prefs_reset_btn');
                btn.attr('disabled','disabled');
                $.ajax({
                    url: me.ajaxUrl,
                    dataType: 'json',
                    method: 'POST',
                    data: {
                        action : 'myprefs_reset_prefs'
                    },
                    success: function(response) {
                        if(me.responseOk(response)){
                            me.successMsg(me.messages.success.prefsReset);
                            me.updatePrefs(response.data.preferences);
                            me.displayItems();
                            prefsAccordion.accordion( "refresh" );
                        }
                        else{
                            me.errorMsg(me.messages.error.prefsNotReset);
                        }
                        prefLog(response);
                        btn.removeAttr('disabled');
                    },
                    error: function(response){
                        me.errorMsg(me.messages.error.prefsNotReset);
                        btn.removeAttr('disabled');
                        prefLog(response);
                    }
                });
            }

            me.updatePrefs = function(preferences, current){
                me.prefs = preferences;
                if(!current){
                    /*$.each(me.cats, function(index, catId){
                        var cat = me.getPref(catId);
                        if(!filter || cat.item.family == filter){
                            me.renderCat(cat);
                        }
                    });*/
                }
                else{
                    me.lockCatItems(me.getPref(current));
                }
            };

            me.responseOk = function(response){
                return response.status == 'OK';
            };

    		me.displayItems = function(){
				console.log('11111111');
				
                prefsAccordion.html(' ');
                var filter = me.familyFilter();
				console.log(me.cats);
				
    			$.each(me.cats, function(index, catId){
                    var cat = me.getPref(catId);
                    if(!filter || cat.item.family == filter){
                        me.renderCat(cat);
                    }
                });
               
                //prefsAccordion.accordion( "resize" );

    		};


            me.overrides = function(catId){
                return (me.isCat(catId) && !me.getPref(catId).type.default);
            };

            me.renderCat = function (cat) {
                var itemsHtml = me.openCatPanel(cat);
                itemsHtml += '<div class="prefs_cat_items" data-item-id="'+ cat.item.id +'" >';
                $.each(cat.item.children, function(index, childId){
                    child = me.getPref(childId);
                    itemsHtml += me.getPrefHtml(child);
                });
                itemsHtml += '</div>';
                itemsHtml += '</div>';
                prefsAccordion.append(itemsHtml);
                
            };

            me.openCatPanel = function(cat){
                var cantEditMsg = me.messages.general.catItemsPrefsOverridden;
                var canEditMsg = me.messages.general.canOverrideCatItems;
                cantEditMsg = cantEditMsg.replace('{$cat_name}', cat.item.name);
                canEditMsg = canEditMsg.replace('{$cat_name}', cat.item.name);

                var catHtml = '<h3>' + cat.item.name + '</h3>';
                catHtml += '<div class="prefs_cat_panel" id="prefs_cat_panel_'+ cat.item.id +'" data-item-id="'+ cat.item.id +'" >';
                catHtml += '<div class="prefs_cat_wrap" data-item-id="'+ cat.item.id +'" >';
                catHtml += me.getItemImage(cat.item);
                catHtml += '<div class="prefs_item_name">All '+ cat.item.name;
                catHtml += '<div class="prefs_cant_edit_msg">'
                           + '<i class="fa fa-info-circle"></i>'
                           + cantEditMsg
                           + '</div>';
                catHtml += '<div class="prefs_can_edit_msg">'
                            +'<i class="fa fa-info-circle"></i>'
                            + canEditMsg
                            +'</div>';
                catHtml += ' </div>';

                catHtml += me.getPrefForm(cat);
                catHtml += '<div class="clear"></div>';
                catHtml += '</div>';
                return catHtml;
                //prefsAccordion.append(catHtml);
            };



            me.getPrefHtml = function(pref){
                var item = pref.item;
                var itemHtml = '<div class="prefs_item_wrap">';
                itemHtml += me.getItemImage(item);
                itemHtml += '<div class="prefs_item_name">' + item.name + '</div>';
                itemHtml += me.getPrefForm(pref);
                itemHtml += '<div class="clear"></div>';
                itemHtml += '</div>';
                return itemHtml;

            };

            me.getItemImage = function(item){
                if(item.image){
                    return '<div class="prefs_item_img">' 
                            + '<img class="" src="'+ item.image + '"/>'
                            + '</div>';
                }
                return '';
            };
            me.getPrefsSelectInput = function(typeId, itemId ){
                var html = '<select class="prefs_types_input" id="item_pref_input_'+ itemId +'" data-item-id="'+ itemId +'">';
                $.each(me.prefTypes, function(index, type){
                    html += type.id == typeId 
                                ? '<option value="'+type.id+'" selected >' + type.name +'</option>'
                                : '<option value="'+type.id+'">' + type.name +'</option>';
                });
                html += '</select>';
                return html;
            };
            me.getPrefForm = function(pref){
                var html = '<div class="prefs_form_wrap" id="prefs_form_wrap_'+ pref.item.id +'">';
                html += '<div class="prefs_lock_cover"></div>';
                html += '<span class="prefs_lock"><i class="fa fa-lock"></i></span>';
                html+= me.getPrefsSelectInput(pref.type.id, pref.item.id);
                html+= '<button class="prefs_types_btn" id="item_pref_btn_'+ pref.item.id +'" data-item-id="'+ pref.item.id +'" type="button">Update</button>';
                html+= '</div>';
                return html; 
            };
            me.lockCatItems = function(cat){
                var panel = $('#prefs_cat_panel_'+ cat.item.id +' .prefs_cat_items');
                var cantEdit = $('#prefs_cat_panel_'+ cat.item.id +' .prefs_cant_edit_msg');
                var canEdit  = $('#prefs_cat_panel_'+ cat.item.id +' .prefs_can_edit_msg');
                var formWraps = panel.find('.prefs_form_wrap');
                var selectInupt = panel.find('select');
                var selectDisabled = (selectInupt.attr('disabled') == 'disabled');
                if(me.overrides(cat.item.id)){
                    cantEdit.show();
                    canEdit.hide();
                    selectInupt.attr('disabled','disabled');
                    selectInupt.val(cat.type.id);
                    formWraps.addClass('prefs_locked_frm');
                    panel.find('button').attr('disabled','disabled');
                }
                else{

                    canEdit.show();
                    cantEdit.hide();
                    selectInupt.each(function(){
                        var pref = me.getPref($(this).attr('data-item-id'));
                        $(this).val(pref.type.id);
                    });
                    formWraps.removeClass('prefs_locked_frm');
                    panel.find('select').removeAttr('disabled');
                    panel.find('button').removeAttr('disabled');
                }
            };

            me.familyFilter = function(){
                var selected = $("#prefs_filters #prefs_filter_value").val();
                var filter = selected != 0 ? selected : false;
                me.currentFamilyFilter = filter;
                return filter;
            }

            me.applyFamilyFilter = function(){
                var current = me.currentFamilyFilter;
                prefLog('Current:' + current + ' New: ' + me.familyFilter());
                if(current != me.familyFilter()){
                    prefLog('Filter changed:');
                    me.displayItems();
                }
                prefsAccordion.accordion( "refresh" );
               // prefsAccordion.accordion({ autoHeight: false });
            }

            me.itemInput = function(id){
                return $('#item_pref_input_' + id);
            };

            me.itemBtn = function(id){
                return $('#item_pref_btn_' + id);
            };

            me.isLockedMsg = function(itemId){
                var msg = me.messages.general.lockedItems;
                me.errorMsg(msg);
            }

            me.successMsg = function(msg, delay){
                me.popUpMsg(1, msg, delay);
            };

            me.errorMsg = function(msg, delay){
                me.popUpMsg(0, msg, delay);
            };

            me.popUpMsg = function(type, msg, delay){
                var popUp = type 
                    ? $('.pref_success.prefs_msg_container')
                    : $('.pref_error.prefs_msg_container');

                var wrap = $('#prefs_msg_wrap');

                delay = delay >= 0 ? delay : 4000;
                popUp.find('.pref_msg').html(msg);
                wrap.show();
                popUp.show();
               /* setTimeout(function(){ 
                    popUp.find('.pref_msg').html(' ');
                    wrap.hide();
                    popUp.hide();
                }, delay);*/
            }

            me.hidePopup = function(){
                var popUp = $('.prefs_msg_container');

                var wrap = $('#prefs_msg_wrap');
                popUp.each(function(){
                    $(this).find('.pref_msg').html(' ');
                    $(this).hide();
                });
                wrap.hide();
            }

            me.accordionLoading = function(loading){
                loading = loading == undefined ? true : loading;
                //
                //if(loading){
                //    prefsLoading.show();
                //}
                //else{
                //    prefsLoading.hide();
                //}
            };

    		me.init();
    		return me;
    	
    	}

        function setPrefHooks(){
            prefsAccordion.on( "accordioncreate", function( event, ui ) {
				console.log(myprefs)
                $.each(myprefs.cats, function(index, id){
                    myprefs.lockCatItems(myprefs.getPref(id));
                });
            });


            $('body').on('click', '#prefs_reset_btn', function(){
                var msg = myprefs.messages.general.resetPrefsPrompt;
                    if(confirm(msg)){
                        myprefs.reset();
                    }
            });
            $('body').on('click', '.prefs_types_btn', function(){
                myprefs.save($(this).attr('data-item-id'));
            });

            $('body').on('click','.prefs_locked_frm.prefs_form_wrap *', function(){
               myprefs.isLockedMsg();
            });

            $('body').on('click', '.prefs_item_filter', function(){
                var selected = $(this);
                $('.prefs_item_filter').removeClass('active-filter');
                selected.addClass('active-filter');
                $('#prefs_filter_value').val(selected.attr('data-value'));
                myprefs.applyFamilyFilter();
            });

            $('body').on('click', '#prefs_msg_wrap *', function(){
                myprefs.hidePopup();
            });

            var popUpOpen = '<div id="prefs_msg_wrap" ><div id="prefs_msg_overlay">&nbsp;</div>';
            

            var popUpHtml1 = '<div class="pref_success prefs_msg_container">'
                        + '<span class="pref_icon"> <i class="fa fa-check"></i>  </span>'
                        + '<span class="pref_msg"> This is an error success </span>'
                        +'</div>';

            var popUpHtml2 = '<div class="pref_error prefs_msg_container">'
                        + '<span class="pref_icon"> <i class="fa fa-times"></i>  </span>'
                        + '<span class="pref_msg"> This is an error success </span>'
                        +'</div>';
            var popUpClose = '</div>';
            $('body').append(popUpOpen + popUpHtml1 + popUpHtml2 + popUpClose);
        }


    });
})(jQuery);