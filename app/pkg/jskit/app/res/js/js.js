/*

	Lepton/JS - Client Side Library for Lepton

	Licensed under the Gnu Public License (GPL) v2 or later.

	(c) 2009, labs.noccy.com
	(c) 2008-2009, Christopher Vagnetoft

	For more info see http://labs.noccy.com

*/

var lepton = {
	version:'0.1'
};

/******************************************************************************
 *
 *   lepton.image
 *
 ******************************************************************************/

lepton.Image = {
	preload:function(src) {
		if (!(this._images)) this._images = [];
		var i = new Image();
		i.src = src;
		this._images.push(i);
	}
};

/******************************************************************************
 *
 *   lepton.data
 *
 ******************************************************************************/

lepton.data = {};

/**
 * LeptonJS DataSet: Provides an abstract API to handle, request, and
 * manipulate data. Initialize the dataset with a provider
 *
 *  var oDS = new lepton.data.Dataset();
 *  oDS.load(new lepton.data.XHRDataProvider('/foo/bar'),{
 *		onSuccess:function(ds) { dosomethinghere }
 *  });
 *
 *
 *
 *
 */
lepton.data.Dataset = Class.create({
	initialize:function(oDataProvider) {
		if (oDataProvider) this.load(oDataProvider);
	},
	load:function(oDataProvider,oArguments) {
		this._provider = oDataProvider;
		this._arguments = oArguments;
		this._provider.bindDataset(this);
		this._provider.fetch(this.onDataAvailableHandler.bind(this));
	},
	onDataAvailableHandler:function() {
		// When this method is called, we should have the data available in
		// this._providerso we go ahead and call on the appropriate callback
		this._arguments.onSuccess(this);
	}
});

/**
 *
 *
 */
lepton.data.GenericProvider = Class.create({
	bindDataset:function(oDataset) {
		this._dataset = oDataset;
	},
	update:function() { },
	getDataObject:function() { }
});

/**
 *
 *
 */
lepton.data.JSONDataProvider = Class.create(lepton.data.GenericProvider,{
	initialize:function(sURL) {
		this._url = sURL;
	},
	fetch:function(fCallback) {
		this._callback = fCallback;
		// TODO: Do the magic here, and then call on this._dataset.onDataAvailable()
	}
});

/**
 *
 *
 */
lepton.data.CSVDataProvider = Class.create(lepton.data.GenericProvider,{
	initialize:function(sURL) {
		this._url = sURL;
	},
	fetch:function(fCallback) {
		this._callback = fCallback;
		// TODO: Do the magic here, and then call on this._dataset.onDataAvailable()
	}
});

/**
 *
 *
 */
lepton.data.TableDataProvider = Class.create(lepton.data.GenericProvider,{
	initialize:function(oElem) {
		this._elem = oElem;
	},
	fetch:function(fCallback) {
		this._callback = fCallback;
		// TODO: Do the magic here, and then call on this._dataset.onDataAvailable()
	}
});


/******************************************************************************
 *
 *   lepton.forms
 *
 ******************************************************************************/

lepton.forms = {};

/**
 * FormValidation is used to validate a form, and to enable/disable the submit
 * button based on the forms state.
 */
lepton.forms.FormValidation = Class.create({
	initialize:function(opts) {
		this._opts = opts || [];
		this._validators = [];
	},
	addValidator:function(validator) {
		validator.bindForm(this);
		this._validators.push(validator);
	},
	hasValidated:function() {
		var valid = 0;
		for (var n = 0; n < this._validators.length; n++) {
			if (this._validators[n].isValid()) valid++;
		}
		if (valid == this._validators.length) {
			if (this._opts.onValid) this._opts.onValid();
		} else {
			if (this._opts.onInvalid) this._opts.onInvalid();
		}
	}
});

/**
 * This is the template for a validator
 *
 */
lepton.forms.GenericValidator = Class.create({
	initialize:function(elem,opts) {
		this._elem = $(elem) || null;
		if (this._options.validateOnBlur) this._elem.observe('blur',this.validate.bindAsEventListener(this));
		if (this._options.validateOnChange) this._elem.observe('keyup',this.validate.bindAsEventListener(this));
		this._valid = this.validate();
	},
    /**
     * Returns true if the value is valid, or false if the value is invalid
     * @returns (boolean) True if valid
     */
    isValid:function() {
        return(this._valid);
    },
	validate:function() {
		alert('I am validator');
	},
	bindForm:function(f) {
		this._form = f;
	}
});

/**
 *
 *
 */
lepton.forms.StringValidator = Class.create(lepton.forms.GenericValidator,{
	initialize:function($super,elem,opts) {
        this._options = Object.extend({
            required: false,
            minLength: null,
            maxLength: null,
			match: '',
            validateOnBlur: true,
            validateOnChange: false
        },opts);
		$super(elem,opts);
	},
	validate:function() {
		var f = this._elem.value;
		var v = true;
		if (f == '') {
			if (this._options.required) v = false;
		} else {
			if ((this._options.minLength != null) && (f.length < this._options.minLength)) v = false;
		}
		this._valid = v;
		if (this._form) this._form.hasValidated();
		return;
	}
});

/**
 * Numeric validator, validates a field according to numeric rules.
 *
 */
lepton.forms.NumericValidator = Class.create(lepton.forms.GenericValidator,{
	initialize:function($super,elem,opts) {
        // Initialize defaults here
        this._options = Object.extend({
            allowNegative: true,
            required: false,
            minValue: null,
            maxValue: null,
            validateOnBlur: true,
            validateOnChange: false
        },opts);
		$super(elem,opts);
	}
});

/**
 * Ajax validator, performs validation by calling on a server side method
 *
 */
lepton.forms.AjaxValidator = Class.create(lepton.forms.GenericValidator,{
    initialize:function($super,elem,opts) {
        this._options = Object.extend({
			validateOnBlur: true,
			validateOnChange: false,
			url: ''
        },opts);
        $super(elem,opts);
    },
	validate:function() {
		var params = {};
		params[this._options.param] = this._elem.value;
		new Ajax.Request(this._options.url,{
			method:'get',
			parameters:params,
			onSuccess:this._validateCallback.bind(this)
		});
	},
	_validateCallback:function(t) {
		var r = t.responseText.evalJSON(true);
		this._valid = (r[this._options.check]);
		if (this._form) this._form.hasValidated();
	}
});

lepton.forms.InlineUpload = Class.create({
	initialize:function(oForm, oArguments) {

	}
});

lepton.forms.Editor = Class.create({
	initialize:function(el,opts) {
		this._el = $(el);
		this._opts = opts;
	},
	insert:function(before,after) {
		if (document.selection) {
			this._el.focus();
			var sr = document.selection.createRange();
			sr.text = before + sr.text + after;
		}
		else if (this._el.selectionStart || this._el.selectionStart == '0') {
			var startPos = this._el.selectionStart;
			var endPos = this._el.selectionEnd;
			this._el.value = this._el.value.substring(0, startPos)
				+ before
				+ this._el.value.substring(startPos, endPos)
				+ after
				+ this._el.value.substring(endPos, this._el.value.length);
		} else {
			this._el.value += before + after;
		}
		this._el.focus();
	}

});





/******************************************************************************
 *
 *   lepton.canvas
 *
 ******************************************************************************/

lepton.canvas = {};
lepton.canvas.Color = Class.create({
	initialize:function(r,g,b,a) {
		this._r = r;
		this._g = g;
		this._b = b;
		this._a = a;
	},
	getRGB:function() {
		return 'rgb('+this._r+','+this._g+','+this._b+')';
	},
	getRGBA:function() {
		return 'rgba('+this._r+','+this._g+','+this._b+','+this._a+')';
	}
});

lepton.Canvas = Class.create({
	initialize:function(el) {
		this._cx = $(el).getContext('2d');
	},
	drawFilledRect:function(x1,y1,x2,y2,bc,fc) {
		this._cx.fillStyle = fc;
		this._cx.lineStyle = bc;
		this._cx.fillRect(x1,y1,x2,y2);
	}
});



/******************************************************************************
 *
 *   lepton.charting
 *
 ******************************************************************************/

lepton.charting = {
    // Chart type constants
    CHART_TYPE_BAR: 1,
    CHART_TYPE_LINE: 2,
    CHART_TYPE_PIE: 3
};

lepton.charting.Chart = Class.create({ });

lepton.charting.ChartProvider = Class.create({
    initialize:function() {

    },
    setChartType:function(eType) {
        this._type = eType;
    },
    setTitle:function(title) {
        this._title = title;
    }
});

lepton.charting.JsCharts = Class.create(lepton.charting.ChartProvider, {
    initialize:function($super,oArgs) {
        $super(oArgs);
    }
});


/******************************************************************************
 *
 *   lepton.animation
 *
 ******************************************************************************/

lepton.effects = {};

/**
 * Effect superclass.
 *
 */
lepton.effects.Effect = Class.create({
    initialize:function(oOpts) {
        this._elem = $(oOpts.elem);
        var dim = this._elem.getDimensions();
        this._width = dim.width;
        this._height = dim.height;
    },
    setOpacity:function(opacity) {
        this._elem.setOpacity(opacity);
    }
});

/**
 * Fade an element in or out
 *
 * oOpts:   element (element) The element to fade
 *          duration (number) The duration of the effect
 *          onComplete (function) The onComplete hook
 *          onBegin (function) The onBegin hook
 */
lepton.effects.FadeIn = Class.create(lepton.effects.Effect,{
    initialize:function($super,oOpts) {

    }
});



/******************************************************************************
 *
 *   lepton.element
 *
 ******************************************************************************/

lepton.Element = {
    center:function(element) {

        try{
            element = $(element);
        }catch(e){
            return;
        }

        var my_width  = 0;
        var my_height = 0;

        if ( typeof( window.innerWidth ) == 'number' ){
            my_width  = window.innerWidth;
            my_height = window.innerHeight;
        } else if ( document.documentElement &&
                 ( document.documentElement.clientWidth ||
                   document.documentElement.clientHeight ) ){
            my_width  = document.documentElement.clientWidth;
            my_height = document.documentElement.clientHeight;
        } else if ( document.body &&
                ( document.body.clientWidth || document.body.clientHeight ) ){
            my_width  = document.body.clientWidth;
            my_height = document.body.clientHeight;
        }

        try {
            element.style.position = 'absolute';
            // element.style.zIndex   = 90;
        } catch(e) { }

        var scrollY = 0;

        if ( document.documentElement && document.documentElement.scrollTop ){
            scrollY = document.documentElement.scrollTop;
        } else if ( document.body && document.body.scrollTop ){
            scrollY = document.body.scrollTop;
        } else if ( window.pageYOffset ){
            scrollY = window.pageYOffset;
        } else if ( window.scrollY ){
            scrollY = window.scrollY;
        }

        var elementDimensions = Element.getDimensions(element);

        var setX = ( my_width  - elementDimensions.width  ) / 2;
        var setY = ( my_height - elementDimensions.height ) / 2 + scrollY;

        setX = ( setX < 0 ) ? 0 : setX;
        setY = ( setY < 0 ) ? 0 : setY;

        element.style.left = setX + "px";
        element.style.top  = setY + "px";

        element.style.display  = 'block';

    },
	loadPage:function(element,url) {
		new Ajax.Updater(element, url, { method:'get' });
	},
	elastic:function(element,maxHeight) {
		this.maxHeight = maxHeight;
		$(element).observe('keyup',function() {
			var text = $(element);
			if ( !text ) return;
			var adjustedHeight = text.clientHeight;
			if ( !this.maxHeight || this.maxHeight > adjustedHeight ) {
				adjustedHeight = Math.max(text.scrollHeight, adjustedHeight);
				if ( this.maxHeight )
					adjustedHeight = Math.min(this.maxHeight, adjustedHeight);
				if ( adjustedHeight > text.clientHeight )
					text.style.height = (adjustedHeight + 5) + "px";
			}
		});
		var baseHeight = text.clientHeight;
		text.style.height = (baseHeight + 5) + "px";
	}
}

Element.addMethods(lepton.Element);
