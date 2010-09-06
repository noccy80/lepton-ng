/******************************************************************************
 *
 *   lepton.ui
 *
 ******************************************************************************/

lepton.ui = {};

/**
 *
 *
 *
 */
lepton.ui.Table = Class.create({
	initialize:function(elem,oArguments) {

    }
});

lepton.ui.Tooltip = Class.create({
	initialize:function(elem,opts) {
        this._options = Object.extend({
            opacity: '0.9',
            backgroundColor: '#FFFFE0',
			border: 'solid 1px #404040',
			font: '8pt sans-serif',
			zIndex: '1999',
			padding: '2px',
			textColor: '#101010'
        },opts);
		this._el = new Element('div');
		this._el.setStyle({
			position:'absolute',
			left:'0px',
			top:'0px',
			display:'none',
			padding: this._options.padding,
			opacity: this._options.opacity,
			backgroundColor: this._options.backgroundColor,
			border: this._options.border,
			font: this._options.font,
			color: this._options.textColor,
			zIndex:this._options.zIndex
		});
		this._el.update( this._options.text );
		document.body.appendChild(this._el);
		if (this._options.bind) {
			$(this._options.bind).observe('mouseover', function() { this._el.show(); });
			$(this._options.bind).observe('mouseout', function() { this._el.hide(); });
		}
    },
	show:function(elem,text) {
		if (text) this._el.update(text);
		if (elem) {
			var p = $(elem).cumulativeOffset();
			var x = p[0];
			var y = p[1] + $(elem).getHeight() + 5;
			this._el.setStyle({ left:x+'px', top:y+'px' });
		}
		this._el.show();
	},
	hide:function() {
		this._el.hide();
	}
});

lepton.ui.tooltip = {
	show:function(elem,text) {
		if (lepton.ui.tooltip.instance) delete(lepton.ui.tooltip.instance);
		lepton.ui.tooltip.instance = new lepton.ui.Tooltip();
		lepton.ui.tooltip.instance.show(elem,text);
	},
	hide:function() {
		if (lepton.ui.tooltip.instance) {
			lepton.ui.tooltip.instance.hide();
			delete(lepton.ui.tooltip.instance);
		}
	}
};

lepton.ui.Overlay = Class.create({
	initialize:function(opts) {
        this._options = Object.extend({
            opacity: '0.9',
            backgroundColor: '#303030',
			zIndex: '999'
        },opts);
		this.attached = false;
		this.oElem = new Element('div');
		this.oElem.setStyle({
			position:'fixed',
			display:'none',
			left:'0px',
			top:'0px',
			right:'0px',
			bottom:'0px',
			opacity: this._options.opacity,
			backgroundColor: this._options.backgroundColor,
			zIndex:this._options.zIndex
		});
	},
	getElement:function() {
		return this.oElem;
	},
	show:function() {
		if (!this.attached) {
			document.documentElement.appendChild(this.oElem);
			this.attached=true;
		}
		this.oElem.show();
	},
	hide:function() {
		this.oElem.hide();
	}
});

lepton.ui.InlineEditor = Class.create({
	initialize:function(elem,oArguments) {
		this.oElem = $(elem);
		this.args = oArguments || {};
		// hook the element
		this.oElem.observe("click", this._click.bindAsEventListener(this));
	},
	_click:function() {

	}
});

lepton.ui.Dialog = Class.create({
	initialize:function(el,opts) {
		this._el = $(el);
		this._opts = {
			overlay:null
		};
		Object.extend(this._opts, opts);
		this._el.setStyle({
			position:'absolute',
			zIndex:20000
		});
		this._el.addClassName('ljs-ui-dialog');
		this._el.hide();
	},
	show:function() {
		if (this._opts.overlay) this._opts.overlay.show();
		this._el.center();
		this._el.show();
	},
	hide:function() {
		if (this._opts.overlay) this._opts.overlay.hide();
		this._el.hide();
	}
});

lepton.ui.dropmenu = {
	current: null
};

lepton.ui.DropMenu = Class.create({
	initialize:function(el,menu,opts) {
		this._parent = $(el);
		if (!this._parent) { return; }
		this._menu = $(menu);
		this._opts = opts;
		this._menu.setStyle({
			'position':'absolute',
			'display':'none'
		});
		this._parent.addClassName('expandable');
		Event.observe(this._parent,'mouseover',this.showMenu.bindAsEventListener(this));
		Event.observe(this._parent,'mousemove',this.__cbShow.bindAsEventListener(this));
		Event.observe(this._parent,'mouseout',this.__cbHide.bindAsEventListener(this));
	},
	showMenu:function() {
		if (lepton.ui.dropmenu.current) {
			lepton.ui.dropmenu.current.hideMenu();
			lepton.ui.dropmenu.current = null;
		}
		if (!this._menu) return;
		lepton.ui.dropmenu.current = this;
		this._menu.show();
		this._state = true;
		this._parent.addClassName('expanded');
		var p = this._parent.cumulativeOffset();
		var x = p[0];
		var y = p[1] + this._parent.getHeight();
		this._menu.setStyle({ left:x+'px', top:y+'px' });
		Event.observe(this._menu,'mousemove',this.__cbShow.bindAsEventListener(this));
		Event.observe(this._menu,'mouseout',this.__cbHide.bindAsEventListener(this));
	},
	hideMenu:function() {
		this._menu.hide();
		this._state = false;
		lepton.ui.dropmenu.current = null;
		this._parent.removeClassName('expanded');
		if (this._htimer) clearTimeout(this._htimer);
	},
	__refreshTimer:function() {
		if (this._htimer) clearTimeout(this._htimer);
		this._htimer = setTimeout(this.__invokeTimer.bindAsEventListener(this),100);
	},
	__invokeTimer:function() {
		if (this._state == false) this.hideMenu();
	},
	__cbHide:function() { this._state = false; this.__refreshTimer(); },
	__cbShow:function() { this._state = true; this.__refreshTimer(); }
});

lepton.ui.TabBar = Class.create({
	initialize:function(el,opts) {
		this._parent = el;
		this._el = new Element('div');
		$(this._el).addClassName('ljs-ui-tabbar');
		$(el).appendChild(this._el);
	},
	addButton:function(btn) {
		btn.attach(this._el);
	},
	setActive:function(btnid) {
		var e = $(this._el).childNodes;
		for(var n = 0; n < e.length; n++) {
			var el = e[n];
			if (el.nodeName == 'A') {
				if (el.id == btnid) {
					$(el).addClassName('ljs-current');
				} else {
					$(el).removeClassName('ljs-current');
				}
			}
		}
	}
});

lepton.ui.Accordion = Class.create({
	initialize:function(el,opts) {
		this._el = $(el);
		this._pages = [];
		var e = this._el.childNodes;
		var i = 0;
		for(var n = 0; n < e.length; n++) {
			var te = e[n];
			if (te.nodeName == 'DIV') {
				te.addClassName('ljs-accordion-hidden');
				te.addClassName('ljs-accordion');
				this._pages[i++] = te;
			}
		}
		this.showPage(0);
	},
	showPage:function(index) {
		for(var n = 0; n < this._pages.length; n++) {
			if (n==index) {
				this._pages[n].addClassName('ljs-accordion-visible');
				this._pages[n].removeClassName('ljs-accordion-hidden');
			} else {
				this._pages[n].addClassName('ljs-accordion-hidden');
				this._pages[n].removeClassName('ljs-accordion-visible');
			}
		}
	}
});

lepton.ui.ToolBar = Class.create({

});

lepton.ui.Button = Class.create({
	initialize:function(opts,cb) {
		this._el = new Element('a');
		if (opts.id) $(this._el).id = opts.id;
		if (opts.hash) $(this._el).href = '#' + opts.hash;
		$(this._el).addClassName('ljs-ui-button');
		$(this._el).setStyle({
			backgroundImage:'url(' + opts.icon +')'
		});
		$(this._el).observe('click', cb);
	},
	attach:function(parentel) {
		parentel.appendChild(this._el);
	}
})

/******************************************************************************
 *
 *   lepton.ui.bluebox
 *
 ******************************************************************************/

/**
 * This is the BlueBox namespace. Use the setup() method to hook all elements
 * with the specified class name and turn them into links by creating a new
 * instance of the bluebox.Image class wrapping it.
 *
 * TODO: Make the code respect the tagname - This should only convert A-tags
 * into clickable thingies.
 *
 * @namespace lepton.ui.bluebox
 *
 */
lepton.ui.bluebox = {
	setup:function(cn,opts) {
		var els = document.getElementsByClassName(cn);
		for (var i = 0; i < els.length; i++) {
			new lepton.ui.bluebox.Image(els[i],opts);
		}
	}
};


/**
 * The bluebox Image manager. The constructor here hooks the click event for
 * the anchor, and replaces it with a call to its own showBox() method.
 *
 * @namespace lepton.ui.bluebox
 * @class Image
 *
 */
lepton.ui.bluebox.Image = Class.create({
	initialize:function(el,opts) {
		this._el = $(el);
		this._src = $(el).getAttribute('href');
		this._opts = {
			overlayColor:'#FFFFFF',
			overlayOpacity:0.9
		};
		Object.extend(this._opts, opts);
		$(el).observe('click', this.showBox.bindAsEventListener(this));
	},
	showBox:function(e) {
		this._ol = new lepton.ui.Overlay({
			backgroundColor:this._opts.overlayColor,
			opacity:this._opts.overlayOpacity
		});
		this._ol.show();
		this._pel = new Element('img', { src:this._src } );
		document.documentElement.appendChild(this._pel);
		$(this._pel).setStyle({
			zIndex:99999,
			border:'solid 3px #101010',
			position:'absolute',
			cursor:'pointer'
		});
		$(this._pel).center();
		$(this._pel).observe('click', this.hideBox.bindAsEventListener(this));
		Event.stop(e);
		return false;
	},
	hideBox:function() {
		this._ol.hide();
		delete this._ol;
		document.documentElement.removeChild(this._pel);
		this._pel.hide();
		delete this._pel;
	}
});
