




var boki = {};

boki.Editor = Class.create({
	initialize:function(t,w,i) {
		this._el = new Element('textarea',{
			'class':'-x-boki-textarea'
		});
		this._el.setStyle({
			'width':'100%',
			'height':'200px'
		});
		var e = t.parentNode;
		e.insertBefore(this._el,t);
		this._el.update(t.innerHTML);
		this._el.focus();
		$(t).hide();
		// t.document.documentElement.appendChild(this._el);
		this._elp = e;
		this._elh = t;
		this._el.observe("blur", this.revert.bind(this));
		this._el.observe("keyup", this.keyup.bind(this));
	},
	addParaBeforeSelf:function(str) {
		var para = new Element("p");
		para.update(str);
		this._elp.insertBefore(para,this._el);
		Event.observe(para, 'click', boki.Field.click.bind());
	},
	addParaAfterSelf:function(str) {
		var para = new Element("p");
		para.update(str);
		this._elp.insertBefore(para,this._elh.nextSibling);
		Event.observe(para, 'click', boki.Field.click.bind());
	},
	keyup:function(e){
		var v = this._el.value;
		while(v.substr(0,2) == "\n\n") {
			v = v.substr(2,v.length);
			this.addParaAfterSelf(v);
			v= "";
		};
		if (v.substr(v.length-2,2) == "\n\n") {
			v = v.substr(0,v.length-2);
			this.addParaBeforeSelf(v);
			v= "";
		};
		// if (v[v.length - 1] == 10) { delete v[v.length - 1]; } else { alert (v[0].charCodeAt(0)); }
		// 3. Look for double newlines in block, and split
		var va = v.split("\n\n");
		if (va.length > 2) {
			console.log("Breaking up!");
			var before = new Element("p");
			before.update(va[0]);
			this._elp.insertBefore(before,this._el);
			Event.observe(before, 'click', boki.Field.click.bind());
			v = va[2];
			this._el.value = v;
		}

		// if ntrail is > 0, create a new box after this one and change to it.
		// or in reality create one before and update it with this ones info.

		this._elh.update(v);
	},
	/**
	 * Clean up afterwards
	 */
	revert:function() {
		var v = this._el.value;
		try { 
			this._elp.removeChild(this._el);
			this._elh.show();
			delete this._el;
			if (v == "") {
				this._elh.parentNode.removeChild(this._elh);
			}
		} catch (e) { }
	},
	/**
	 * Kick it off
	 */
	show:function() {
	
	}

});

boki.Field = {
	click: function(what,idx) {
		var e = new boki.Editor(this,what,idx);
	}
}

Event.observe(window,"load",function(){
	var prp = document.getElementById('book-content');
	var pr = prp.childNodes;
	for(var e = 0; e < pr.length; e++) {
		if(pr[e].nodeName == 'P') {
			$(pr[e]).observe('click', boki.Field.click.bind(pr[e],pr[e],e));
		}
	}
});
