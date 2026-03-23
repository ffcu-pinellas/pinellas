/*
* Side Push Menu
* Author: Dynamic Drive at http://www.dynamicdrive.com/
* Visit http://www.dynamicdrive.com/ for full source code
*/

(function(w, $){

	var menutoggler = '<div class="menutoggler" class="mt" title="Open Push Menu"><span class="wordmenu">Menu</span> <div class="far fa-bars"></div> </div>' // Menu Toggler HTML. Set to empty string '' to disable

	var defaults = {
		position: 'right',
		pushcontent: false,
		source: 'inline',
		fxduration: 100,
		wrapperid: 'contentwrapper',
		revealamt: 0,
		marginoffset: 0,
		dismissonclick: true,
		curstate: 'closed',
		onopenclose: function(state){}
	}

	document.createElement('nav') // for lesser IE browsers
	var menusarray = []
	var transform = true

	w.pushmenu = function(options){
		var s = $.extend({}, defaults, options)
		var thismenu = this,
				$body = $(document.body),
				$contentwrapper = $('#'+s.wrapperid),
				$menu = '',
				expandlength = ''
		menusarray.push( [this, s] )

		function init(menuref){
			$menu = $(menuref).css({top: 0, visibility: 'hidden', zIndex: 1000, transitionDuration: s.fxduration +'ms'}).prependTo(document.body)
			if (menutoggler != ''){
				$menutoggler = $(menutoggler).css({transitionDelay: s.fxduration +'ms'}).addClass(s.position).appendTo('header')
			}
			$menu.on('click touchstart', function(e){
				var target = e.target
					e.stopPropagation()
			})
			.find('.closebutton').on('click', function(e){
				thismenu.toggle('closed')
			})
			$menutoggler.on('click', function(e){
				thismenu.toggle('open')
				e.stopPropagation()
			})
			$menu.addClass(s.position)
			var delta = parseInt($menu.outerWidth()) - s.revealamt
			$menu.css((s.position == 'left')? 'left' : 'right', -delta)
			thismenu.toggle(s.curstate, delta)
			$menu.css({visibility: 'visible'})
			if (transform){
				$menu.on('transitionend webkitTransitionEnd', function(e){
					if (/(left)|(right)/i.test(e.originalEvent.propertyName) && e.target.getAttribute('id') == s.menuid){ // check event fired on "left or right" prop
						var state = (parseInt($menu.css(s.position)) === 0)? 'open' : 'closed'
						s.onopenclose(state)
					}
				})
			}
			return delta
		}

		this.toggle = function(action, w){
			var delta = w || expandlength
			s.curstate = action || ( (s.curstate == 'closed')? 'open' : 'closed' )
			var animprop = (s.position == 'left')? 'left' : 'right'
			$body.removeClass('open closed').addClass(s.curstate)
			$contentwrapper.removeClass('open closed').addClass(s.curstate)
			$menu.css(animprop, (s.curstate == 'open')? 0 : -delta)
			$menu.removeClass('open closed').addClass(s.curstate)
			if (s.pushcontent === true){
				var wrapperstyle = {transitionDuration: s.fxduration +'ms'}
				wrapperstyle[animprop] = (s.curstate == 'open')? delta + s.marginoffset : 0
				$contentwrapper.css(wrapperstyle)
			}
			if (!transform){
				s.onopenclose(s.curstate)
			}
		}

		if (s.source == 'inline'){
			expandlength = init('#' + s.menuid)
		}
		else{
			$.ajax({
				url: s.source,
				dataType: 'html',
				error:function(ajaxrequest){
					alert('Error fetching content.<br />Server Response: '+ajaxrequest.responseText)
				},
				success:function(content){
					expandlength = init(content)
				}
			})
		}

		return this

	}

	jQuery(function(){ // run once in document load
		
		$('body').on('click touchstart', function(e){ // dismiss menus onclick of BODY
			var $target = e.changedTouches? $(e.changedTouches[0]) : $(e.target)
			if (/(click)|(touchstart)/i.test(e.type) && !$target.hasClass('pushmenubutton') && !$target.hasClass('toggleitem')){
				for (var i=0; i < menusarray.length; i++){
					if (menusarray[i][1].dismissonclick && menusarray[i][1].curstate == 'open')
						menusarray[i][0].toggle('closed')
				}
			}
		})


	})


}) (window, jQuery)
