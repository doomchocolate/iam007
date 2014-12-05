/**
 * @package    HikaShop for Joomla!
 * @version    2.3.0
 * @author     hikashop.com
 * @copyright  (C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
var hikashop_ratings = new Class({
	Implements : Options,

	options : {
		showSelectBox : false,
		container : null,
		defaultRating : null,
		id : 'hikashop_vote_'
	},

	selectBox : null,
	container : null,
	initialize : function(selectBox, options) {
		// set the custom options
		this.setOptions(options);
		var id = selectBox.getAttribute('id') + '_chzn';
		// set the selectbox
		this.selectBox = $(selectBox);
		// hide the selectbox
		if (!this.options.showSelectBox) {
			//this.selectBox.setStyle('display', 'none');
			if(document.getElementById(id) != null) {
				document.id(id).dispose();
				if(typeof(jQuery)!='undefined' && jQuery().chosen){
					jQuery(id+'-chzn').remove();
				}
			}
		}
		// set the container
		this.setContainer();
		// add stars
		this.selectBox.getElements('option').each(
			this.createStar.bind(this)
		);
		// bind events
		this.container.addEvents({
			mouseover : this.mouseOver.bind(this),
			mouseout : this.mouseOut.bind(this),
			click : this.click.bind(this)
		});
		// bind change event for selectbox if shown
		if (this.options.showSelectBox) {
			this.selectBox.addEvent('change', this.change.bind(this));
		}
		// set the initial rating
		this.setRating(this.options.defaultRating);
	},

	// set the container from options or create default
	setContainer : function() {
		if (document.getElementById(this.options.container)) {
			this.container = document.getElementById(this.options.container);
			return;
		}
		this.createContainer();
	},

	// create the html container for the rating stars
	createContainer : function() {
		this.container = new Element('div', {
			'class': 'ui-rating'
		}).inject(this.selectBox, 'after');

		//this.container = document.getElementById("hikashop_vote_stars").innerHTML = "<div id='ui-rating' class='ui-rating'></div>" ;
	},
	// create the html rating stars
	createStar : function(option) {
		var e = new Element('a', {
		//e.set({
			id : this.options.id + '_' + option.getAttribute('value'),
			'class' : 'ui-rating-star ui-rating-empty',
			title : '' + option.innerHTML,
			value : option.getAttribute('value')
		});
		e.inject(this.container);
		//new Element(document.getElementById("ui-rating").innerHTML = "<a class='ui-rating-star ui-rating-empty' title='"+option.get('html')+"' value='"+option.get('value')+"'></a>").injectAfter(this.container);
	},
	// handle mouseover event
	mouseOver : function(e) {
		if (!e.target)
			e.target = e.srcElement;
		$(e.target).addClass('ui-rating-hover');
		var c = $(e.target).getPrevious();
		while(c) {
			c.addClass('ui-rating-hover');
			c = c.getPrevious();
		}
	},
	// handle mouseout event
	mouseOut : function(e) {
		if (!e.target)
			e.target = e.srcElement;
		$(e.target).removeClass('ui-rating-hover');
		var c = $(e.target).getPrevious();
		while(c) {
			c.removeClass('ui-rating-hover');
			c = c.getPrevious();
		}
	},
	// handle click event
	click : function(e) {
		if (!e.target)
			e.target = e.srcElement;
		var rating = e.target.getAttribute('title').replace('', '');
		var from = this.selectBox.getAttribute('id');
		this.setRating(rating);
		this.selectBox.set({value: rating});
		//send the id of the view which send the vote ( mini / form )
		hikashop_send_vote(rating, from);
	},
	// handle change event
	change : function(e) {
		var rating = $(e.target).get('value');
		this.setRating(rating);
	},
	// set the current rating
	setRating : function(rating) {
		// use selected rating if none supplied
		if (!rating) {
			rating = this.selectBox.getAttribute('value');
			// use first rating option if none selected
			if (!rating) {
				//rating = this.selectBox.getElement('option[value!=]').getAttribute('value');
				rating = 0;
			}
		}
		// get the current selected rating star
		var current = this.container.getElement('a[title=' + rating + ']');

		// highlight current and previous stars in yellow
		if(current && rating != 0) {
			current.set({'class': 'ui-rating-star ui-rating-full'});
			var c = current.getPrevious();
			while(c) {
				c.set({'class': 'ui-rating-star ui-rating-full'});
				c = c.getPrevious();
			}

			// remove highlight from higher ratings
			var c = current.getNext();
			while(c) {
				c.set({'class': 'ui-rating-star ui-rating-empty'});
				c = c.getNext();
			}
		}
		// synchronize the rate with the selectbox
		this.selectBox.set({value: rating});
	}
});
if(MooTools.version == '1.12') {
	hikashop_ratings.implement(new Options);
}
