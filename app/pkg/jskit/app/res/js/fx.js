/******************************************************************************
 *
 *   lepton.fx
 *
 ******************************************************************************/

lepton.fx = {};




lepton.fx.SlideShow = Class.create({

	/**
	 * Constructor takes the element in which to display the images and
	 * the information on how to display them.
	 *
	 * @param elem {element} Element
	 * @param otps {array} Options
	 */
	initialize:function(elem,opts) {
		this._el = $(elem);
		this._opts = opts;
		this._index = -1;
		// Create elements we need
		this.nextSlide();
	},

	nextSlide:function() {
		this._index++;
		if (this._index >= this._opts.images.length) this._index = 0;
		this.setSlide(this._index);
		setTimeout(this.nextSlide.bind(this), this._opts.delay);
	},

	setSlide:function(index) {

		if (this._next) {
			// fade the current one out while
			this._previous = this._next;
			Effect.Fade(this._previous.disp, {
				afterFinish:function() {
					this._el.removeChild(this._previous.disp);
					delete(this._previous.disp);
				}
			});
		};
		this._next = {
			disp: new Element('img')
		};
		this._next.disp.src = this._opts.images[index].src;
		var pos = this._el.cumulativeOffset();
		this._next.disp.setStyle({
			display:'none',
			position:'absolute',
			left:(pos[0]+1)+'px',
			top:(pos[1]+1)+'px'
		});
		this._el.appendChild(this._next.disp);
		Effect.Appear(this._next.disp, {
			duration:2.0
		});

	}





});
