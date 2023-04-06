$(document).ready(function(){
	//$( ".login-box" ).css("display","none").fadeIn("5500");
	//themes, change CSS with JS
	//default theme(CSS) is cerulean, change it if needed
	var current_theme = $.cookie('current_theme')==null ? 'classic' :$.cookie('current_theme');
	switch_theme(current_theme);
	
	$('#themes a[data-value="'+current_theme+'"]').find('i').addClass('icon-ok');
				 
	$('#themes a').click(function(e){
		e.preventDefault();
		current_theme=$(this).attr('data-value');
		$.cookie('current_theme',current_theme,{expires:365});
		switch_theme(current_theme);
		$('#themes i').removeClass('icon-ok');
		$(this).find('i').addClass('icon-ok');
	});
	
	
	function switch_theme(theme_name)
	{
		$('#bs-css').attr('href','css/bootstrap-'+theme_name+'.css');
	}
	
	//highlight current / active link
	$('ul.main-menu li a').each(function(){
		if($($(this))[0].href==String(window.location))
			$(this).parent().addClass('active');
	});
	
	//establish history variables
	var
		History = window.History, // Note: We are using a capital H instead of a lower h
		State = History.getState(),
		$log = $('#log');

	//animating menus on hover
	$('ul.main-menu li:not(.nav-header)').hover(function(){
		$(this).animate({'margin-left':'+=5'},300);
	},
	function(){
		$(this).animate({'margin-left':'-=5'},300);
	});
	
	//other things to do on document ready, seperated for ajax calls
	docReady();
});
		
		
function docReady(){
	//prevent # links from moving to top
	$('a[href="#"][data-top!=true]').click(function(e){
		e.preventDefault();
	});
	
	//rich text editor
	$('.cleditor').cleditor({
		bodyStyle:    
          	"background-color:#101010; color: #ffffff"
	});
	
	//datepicker
	$('.datepicker').datepicker();
	
	//notifications
	$('.noty').click(function(e){
		e.preventDefault();
		var options = $.parseJSON($(this).attr('data-noty-options'));
		noty(options);
	});


	//uniform - styler for checkbox, radio and file input
	$("input:checkbox, input:radio, input:file").not('[data-no-uniform="true"],#uniform-is-ajax').uniform();

	//chosen - improves select
	$('[data-rel="chosen"],[rel="chosen"]').chosen();

	//tabs
	$('#myTab a:first').tab('show');
	$('#myTab a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	});

	//makes elements soratble, elements that sort need to have id attribute to save the result
	$('.sortable').sortable({
		revert:true,
		cancel:'.btn,.box-content,.nav-header',
		update:function(event,ui){
			//line below gives the ids of elements, you can make ajax call here to save it to the database
			//console.log($(this).sortable('toArray'));
		}
	});

	//slider
	$('.slider').slider({range:true,values:[10,65]});

	//tooltip
	$('[rel="tooltip"],[data-rel="tooltip"]').tooltip({"placement":"bottom",delay: { show: 400, hide: 200 }});

	//auto grow textarea
	$('textarea.autogrow').autogrow();

	//popover
	
	$('[rel="popover"],[data-rel="popover"]').popover();

	//iOS / iPhone style toggle switch
	$('.iphone-toggle').iphoneStyle();

	$('.datatable').dataTable({
			"sDom": "<'row-fluid'<'span6'l><'span6'f>r><'label'i>t<'row-fluid'<'span12 center'p>>",
			"sPaginationType": "bootstrap",
			"bJQueryUI": true,
			"oLanguage": {
				"sSearch": "Поиск:",
				"sLengthMenu": "_MENU_ записей",
				"sZeroRecords": "Не найдено",
				"sInfo": "Показаны записи _START_-_END_ из _TOTAL_",
				"sInfoEmpty": "Нет данных",
				"oPaginate": {
					"sFirst": "В начало",
					"sLast": "В конец",
					"sNext": ">>",
					"sPrevious": "<<"
				},
				"sInfoFiltered": "(фильтр из _MAX_ записей)"
			}
	} );

	$('.btn-close').click(function(e){
		e.preventDefault();
		$(this).parent().parent().parent().fadeOut();
	});
	$('.btn-minimize').click(function(e){
		e.preventDefault();
		var $target = $(this).parent().parent().next('.box-content');
		if($target.is(':visible')) $('i',$(this)).removeClass('icon-chevron-up').addClass('icon-chevron-down');
		else 					   $('i',$(this)).removeClass('icon-chevron-down').addClass('icon-chevron-up');
		$target.slideToggle();
	});	
		
	//initialize the external events for calender

	$('#external-events div.external-event').each(function() {

		// it doesn't need to have a start or end
		var eventObject = {
			title: $.trim($(this).text()) // use the element's text as the event title
		};
		
		// store the Event Object in the DOM element so we can get to it later
		$(this).data('eventObject', eventObject);
		
		// make the event draggable using jQuery UI
		$(this).draggable({
			zIndex: 999,
			revert: true,      // will cause the event to go back to its
			revertDuration: 0  //  original position after the drag
		});
		
	});
}


//additional functions for data table
$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
{
	return {
		"iStart":         oSettings._iDisplayStart,
		"iEnd":           oSettings.fnDisplayEnd(),
		"iLength":        oSettings._iDisplayLength,
		"iTotal":         oSettings.fnRecordsTotal(),
		"iFilteredTotal": oSettings.fnRecordsDisplay(),
		"iPage":          Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
		"iTotalPages":    Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
	};
}
$.extend( $.fn.dataTableExt.oPagination, {
	"bootstrap": {
		"fnInit": function( oSettings, nPaging, fnDraw ) {
			var oLang = oSettings.oLanguage.oPaginate;
			var fnClickHandler = function ( e ) {
				e.preventDefault();
				if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
					fnDraw( oSettings );
				}
			};

			$(nPaging).addClass('pagination').append(
				'<ul>'+
					'<li class="prev disabled"><a href="#">&larr;</a></li>'+
					'<li class="next disabled"><a href="#">&rarr;</a></li>'+
				'</ul>'
			);
			var els = $('a', nPaging);
			$(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
			$(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
		},

		"fnUpdate": function ( oSettings, fnDraw ) {
			var iListLength = 10;
			var oPaging = oSettings.oInstance.fnPagingInfo();
			var an = oSettings.aanFeatures.p;
			var i, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

			if ( oPaging.iTotalPages < iListLength) {
				iStart = 1;
				iEnd = oPaging.iTotalPages;
			}
			else if ( oPaging.iPage <= iHalf ) {
				iStart = 1;
				iEnd = iListLength;
			} else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
				iStart = oPaging.iTotalPages - iListLength + 1;
				iEnd = oPaging.iTotalPages;
			} else {
				iStart = oPaging.iPage - iHalf + 1;
				iEnd = iStart + iListLength - 1;
			}

			for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
				// remove the middle elements
				$('li:gt(0)', an[i]).filter(':not(:last)').remove();

				// add the new list items and their event handlers
				for ( j=iStart ; j<=iEnd ; j++ ) {
					sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
					$('<li '+sClass+'><a href="#">'+j+'</a></li>')
						.insertBefore( $('li:last', an[i])[0] )
						.bind('click', function (e) {
							e.preventDefault();
							oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
							fnDraw( oSettings );
						} );
				}

				// add / remove disabled classes from the static elements
				if ( oPaging.iPage === 0 ) {
					$('li:first', an[i]).addClass('disabled');
				} else {
					$('li:first', an[i]).removeClass('disabled');
				}

				if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
					$('li:last', an[i]).addClass('disabled');
				} else {
					$('li:last', an[i]).removeClass('disabled');
				}
			}
		}
	}
});
/* Bootstrap style pagination control */
$.extend($.fn.dataTableExt.oPagination, {
    "bootstrap1": {
        "fnInit": function (oSettings, nPaging, fnDraw) {
            var oLang = oSettings.oLanguage.oPaginate;
            var fnClickHandler = function (e) {
                e.preventDefault();
                if (oSettings.oApi._fnPageChange(oSettings, e.data.action)) {
                    fnDraw(oSettings);
                }
            };            
                $(nPaging).addClass('pagination').append(
                        '<ul>' +
    '<li class="first disabled"><a href="#">' + oLang.sFirst + '</a></li>' +
    '<li class="prev  disabled"><a href="#">' + oLang.sPrevious + '</a></li>' +
    '<li class="next  disabled"><a href="#">' + oLang.sNext + '</a></li>' +
    '<li class="last  disabled"><a href="#">' + oLang.sLast + '</a></li>' +
                        '</ul>'
                );
                var els = $('a', nPaging);
                $(els[0]).bind('click.DT', { action: "first" }, fnClickHandler);
                $(els[1]).bind('click.DT', { action: "previous" }, fnClickHandler);
                $(els[2]).bind('click.DT', { action: "next" }, fnClickHandler);
                $(els[3]).bind('click.DT', { action: "last" }, fnClickHandler);
        },
 
        "fnUpdate": function (oSettings, fnDraw) {
            var iListLength = 15;
            var oPaging = oSettings.oInstance.fnPagingInfo();
            var an = oSettings.aanFeatures.p;
            var i, j, sClass, iStart, iEnd, iHalf = Math.floor(iListLength / 2);
 
            if (oPaging.iTotalPages > 1) {
                if (oPaging.iTotalPages < iListLength) {
                    iStart = 1;
                    iEnd = oPaging.iTotalPages;
                }
                else if (oPaging.iPage <= iHalf) {
                    iStart = 1;
                    iEnd = iListLength;
                } else if (oPaging.iPage >= (oPaging.iTotalPages - iHalf)) {
                    iStart = oPaging.iTotalPages - iListLength + 1;
                    iEnd = oPaging.iTotalPages;
                } else {
                    iStart = oPaging.iPage - iHalf + 1;
                    iEnd = iStart + iListLength - 1;
                }
 
                for (i = 0, iLen = an.length ; i < iLen ; i++) {
                    // Remove the middle elements
                    $('li:gt(1)', an[i]).filter(':not(.next,.last)').remove();
 
                    // Add the new list items and their event handlers
                    for (j = iStart ; j <= iEnd ; j++) {
                        sClass = (j == oPaging.iPage + 1) ? 'class="active"' : '';
                        $('<li ' + sClass + '><a href="#">' + j + '</a></li>')
                                                            .insertBefore($('.next,.last', an[i])[0])
                                                            .bind('click', function (e) {
                                                                e.preventDefault();
                                                                oSettings._iDisplayStart = (parseInt($('a', this).text(), 10) - 1) * oPaging.iLength;
                                                                fnDraw(oSettings);
                                                            });
                    }
 
                    // Add / remove disabled classes from the static elements
                    if (oPaging.iPage === 0) {
                        $('.first,.prev', an[i]).addClass('disabled');
                    } else {
                        $('.first,.prev', an[i]).removeClass('disabled');
                    }
 
                    if (oPaging.iPage === oPaging.iTotalPages - 1 || oPaging.iTotalPages === 0) {
                        $('.next,.last', an[i]).addClass('disabled');
                    } else {
                        $('.next,.last', an[i]).removeClass('disabled');
                    }
                }
            }
        }
    }
});
function getBit(bitMask, bitNum)
{
    return bitMask & 1 << bitNum;
}

var cur_mask_field;
var cur_proctype_field;
var ProctypeCnt = 11;

function fillmask(b){
	$('#maskform').find("input").each(function()
	{
     		$(this)[0].checked = false;
	});
	for (a=1; a<33; a++) {
		if (getBit(b, a-1)) {
			$('#mask_'+a)[0].checked = true;
		}
	}
}

function fillproctype(b){
	$('#proctypeform').find("input").each(function()
	{
     		$(this)[0].checked = false;
	});
	for (a=1; a<ProctypeCnt; a++) {
		if (getBit(b, a-1)) {
			$('#proctype_'+a)[0].checked = true;
		}
	}
}

function getmask(){
	bitMask = 0;
	for (a=1; a<33; a++) {
		if ($('#mask_'+a)[0].checked) {
			bitMask = bitMask | 1 << (a - 1);
		}
	}	
	cur_mask_field.value = bitMask;
}

function getproctype(){
	bitMask = 0;
	for (a=1; a<ProctypeCnt; a++) {
		if ($('#proctype_'+a)[0].checked) {
			bitMask = bitMask | 1 << (a - 1);
		}
	}	
	cur_proctype_field.value = bitMask;
}

function showmask(t){	
	p = $('.popup_clean');
	cur_mask_field = t;
	var obj = t;
	var x=0, y=0;
	while (obj){
		y = y + parseInt(obj.offsetTop);
		x = x + parseInt(obj.offsetLeft);
		obj = obj.offsetParent;
	}	
	p.css('left', x+t.clientWidth+8);
	p.css('top', y-135);
	$('#maskBody').html('<center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Получение данных <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center>');
	p.show();
	$(document).click( function(event){
		if( $(event.target).closest(".popup_clean").length || $(event.target).closest($(cur_mask_field)).length ) 
			return;
		$(p).css('display', 'none');
		event.stopPropagation();		
	});
	var X = new XMLHttpRequest();
	X.open("POST", "pages/history_process.php?op=maskform&rand="+Math.random());
	X.onreadystatechange = function() {
		if (X.readyState == 4) {				
			if(X.status == 200) {
				$('#maskBody').html(X.responseText);	
				fillmask(t.value);			
			} else $('#maskBody').html('<div class="alert alert-error">Ошибка '+X.status+'</div>');
		}
	};
	X.send();
}

function showproctype(t,t1='.popup_clean1',t2='#proctypeBody'){	
	p1 = $(t1);
	cur_proctype_field = t;
	var obj = t;
	var x=0, y=0;
	while (obj){
		y = y + parseInt(obj.offsetTop);
		x = x + parseInt(obj.offsetLeft);
		obj = obj.offsetParent;
	}	
	p1.css('left', x+t.clientWidth+8);
	p1.css('top', y-80);
	$(t2).html('<center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Получение данных <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center>');
	p1.show();
	$(document).click( function(event){
		if( $(event.target).closest(t1).length || $(event.target).closest($(cur_proctype_field)).length ) 
			return;
		$(p1).css('display', 'none');
		event.stopPropagation();		
	});
	var X = new XMLHttpRequest();
	X.open("POST", "pages/history_process.php?op=proctypeform&rand="+Math.random());
	X.onreadystatechange = function() {
		if (X.readyState == 4) {				
			if(X.status == 200) {
				$(t2).html(X.responseText);	
				fillproctype(t.value);
			} else $(t2).html('<div class="alert alert-error">Ошибка '+X.status+'</div>');
		}
	};
	X.send();
}

function showproctype1(t,t1='.popup_clean1',t2='#proctypeBody'){	
	p1 = $(t1);
	cur_proctype_field = t;
	var obj = t;
	var x=0, y=0;
	while (obj){
		y = y + parseInt(obj.offsetTop);
		x = x + parseInt(obj.offsetLeft);
		obj = obj.offsetParent;
	}	
	p1.css('left', t.clientWidth+140);
	p1.css('top', 200);
	$(t2).html('<center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Получение данных <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center>');
	p1.show();
	$(document).click( function(event){
		if( $(event.target).closest(t1).length || $(event.target).closest($(cur_proctype_field)).length ) 
			return;
		$(p1).css('display', 'none');
		event.stopPropagation();		
	});
	var X = new XMLHttpRequest();
	X.open("POST", "pages/history_process.php?op=proctypeform&rand="+Math.random());
	X.onreadystatechange = function() {
		if (X.readyState == 4) {				
			if(X.status == 200) {
				$(t2).html(X.responseText);	
				fillproctype(t.value);
			} else $(t2).html('<div class="alert alert-error">Ошибка '+X.status+'</div>');
		}
	};
	X.send();
}