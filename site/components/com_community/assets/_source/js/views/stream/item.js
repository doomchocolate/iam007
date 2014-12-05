// -----------------------------------------------------------------------------
// views/stream/item
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/base',
	'utils/constants',
	'utils/language'
],

// definition
// ----------
function( $, BaseView, constants, language ) {

	return BaseView.extend({

		el: '#activity-stream-container',

		events: {
			'click .dropdown-menu a[data-action=edit]': 'onEditStream',
			'click .dropdown-menu a[data-action=remove-tag]': 'onRemoveStreamTag',
			'click [data-type=stream-editor] [data-action=cancel]': 'onEditStreamCancel',
			'click [data-type=stream-editor] [data-action=save]': 'onEditStreamSave',
			'click [data-type=stream-action] [data-action=comment]': 'onCommentStreamAdd',
			'click [data-type=stream-comments] [data-action=reply]': 'onCommentStreamAdd',
			'click [data-type=stream-newcomment] [data-action=cancel]': 'onCommentStreamAddCancel',
			'click [data-type=stream-newcomment] [data-action=save]': 'onCommentStreamAddSave',
			'click [data-type=stream-comment] [data-action=edit]': 'onCommentStreamEdit',
			'click [data-type=stream-comment] [data-action=cancel]': 'onCommentStreamEditCancel',
			'click [data-type=stream-comment] [data-action=save]': 'onCommentStreamEditSave',
			'click [data-type=stream-comment] [data-action=remove]': 'onCommentStreamRemove',
			'click [data-type=stream-comment] [data-action=remove-tag]': 'onCommentStreamRemoveTag'
		},

		// ---------------------------------------------------------------------
		// Edit stream events.
		// ---------------------------------------------------------------------

		onEditStream: function( e ) {
			e.preventDefault();

			var el = $( e.target ),
				ct = this.getContainer( el ),
				content = ct.children('[data-type=stream-content]'),
				editor = ct.children('[data-type=stream-editor]');

			if ( editor.is(':visible') )
				return;

			content.hide();
			editor.show();
			this.resetStreamEditor( editor );
			this.focusTextarea( editor );
		},

		onEditStreamCancel: function( e ) {
			e.preventDefault();

			var ct = this.getContainer( e.target ),
				content = ct.children('[data-type=stream-content]'),
				editor = ct.children('[data-type=stream-editor]');

			if ( !editor.is(':visible') )
				return;

			editor.hide();
			content.show();
		},

		onEditStreamSave: function( e ) {
			e.preventDefault();

			var ct = this.getContainer( e.target ),
				editor = ct.children('[data-type=stream-editor]'),
				that = this,
				textarea;

			if ( !editor.is(':visible') )
				return;

			textarea = editor.find('textarea');
			textarea.textntags( 'val', function( value ) {
				joms.stream.ajaxSaveStatus( that.getStreamId( ct ), value );
				editor.hide();
			});
		},

		// ---------------------------------------------------------------------
		// Add stream comment events.
		// ---------------------------------------------------------------------

		onCommentStreamAdd: function( e ) {
			e.preventDefault();

			var ct = this.getContainer( e.target ),
				editor = ct.find('[data-type=stream-newcomment]');

			if ( editor.is(':visible') )
				return;

			editor.show();
			this.resetStreamEditor( editor, true );
			this.focusTextarea( editor );
			this.hideAddCommentButton( ct );
		},

		onCommentStreamAddCancel: function( e ) {
			e.preventDefault();

			var ct = this.getContainer( e.target ),
				editor = ct.find('[data-type=stream-newcomment]');

			if ( !editor.is(':visible') )
				return;

			if ( editor.data('saving') )
				return;

			editor.hide();
			this.showAddCommentButton( ct );
		},

		onCommentStreamAddSave: function( e ) {
			e.preventDefault();

			var el = $( e.target ),
				ct = this.getContainer( e.target ),
				editor = ct.find('[data-type=stream-newcomment]'),
				that = this,
				textarea;

			if ( !editor.is(':visible') )
				return;

			if ( editor.data('saving') )
				return;

			editor.data( 'saving', 1 );
			el.data( 'label', el.text() );
			el.html( 'Saving...' );
			el.prop('disabled',true);

			textarea = editor.find('textarea');
			textarea.textntags( 'val', function( value ) {
				jax.doneLoadingFunction = function() {
					jax.doneLoadingFunction = $.noop;
					editor.removeData( 'saving' );
					el.html( el.data('label') );
					el.prop('disabled',false);
				};
				jax.call( 'community', 'system,ajaxStreamAddComment', that.getStreamId( ct ), value );
			});
		},

		// ---------------------------------------------------------------------
		// Edit stream comment events.
		// ---------------------------------------------------------------------

		onCommentStreamEdit: function( e ) {
			e.preventDefault();

			var el = $( e.target ),
				ct = this.getContainer( e.target ),
				content = el.closest('[data-type=stream-comment-content]'),
				editor = content.siblings('[data-type=stream-comment-editor]'),
				newCommentEditor = ct.find('[data-type=stream-newcomment]');

			if ( editor.is(':visible') )
				return;

			newCommentEditor.hide();
			content.hide();
			editor.show();
			this.resetStreamEditor( editor, true );
			this.focusTextarea( editor );
			this.hideAddCommentButton( ct );
		},

		onCommentStreamEditCancel: function( e ) {
			e.preventDefault();

			var el = $( e.target ),
				ct = this.getContainer( e.target ),
				editor = el.closest('[data-type=stream-comment-editor]'),
				content = editor.siblings('[data-type=stream-comment-content]');

			if ( !editor.is(':visible') )
				return;

			if ( editor.data('saving') )
				return;

			editor.hide();
			content.show();
			this.showAddCommentButton( ct );
		},

		onCommentStreamEditSave: function( e ) {
			e.preventDefault();

			var el = $( e.target ),
				ct = this.getContainer( e.target ),
				editor = el.closest('[data-type=stream-comment-editor]'),
				content = editor.siblings('[data-type=stream-comment-content]'),
				that = this,
				textarea;

			if ( !editor.is(':visible') )
				return;

			if ( editor.data('saving') )
				return;

			editor.data( 'saving', 1 );
			el.data( 'label', el.text() );
			el.html( 'Saving...' );

			textarea = editor.find('textarea');
			textarea.textntags( 'val', function( value ) {
				var id = editor.closest('[data-type=stream-comment]').data('commentid');
				jax.doneLoadingFunction = function() {
					jax.doneLoadingFunction = $.noop;
					editor.removeData( 'saving' );
					el.html( el.data('label') );
				};
				jax.call( 'community', 'system,ajaxeditComment', id, value, that.getStreamId( ct ) );
			});

			editor.hide();
			content.show();
			this.showAddCommentButton( ct );
		},

		// ---------------------------------------------------------------------
		// Remove stream comment events.
		// ---------------------------------------------------------------------

		onCommentStreamRemove: function( e ) {
			e.preventDefault();

			var that = this,
				actions;

			actions = $([
				'<div><button class="btn" onclick="cWindowHide();">', language.get('no'),
				'</button><button class="btn btn-primary pull-right">', language.get('yes'),
				'</button></div>'
			].join(''));

			actions.on( 'click', '.btn-primary', function() {
				that.onCommentStreamRemoveConfirm( e );
				window.cWindowHide();
			});

			window.cWindowShow( null, language.get('stream.remove_comment'), 450, 100 );
			window.cWindowAddContent( '<div>' + language.get('stream.remove_comment_message') + '</div>', actions );
		},

		onCommentStreamRemoveConfirm: function( e ) {
			var ct = this.getContainer( e.target ),
				that = this;

			jax.loadingFunction = $.noop;
			jax.doneLoadingFunction = function() {
				jax.doneLoadingFunction = $.noop;

				var more = ct.find('[data-type=stream-more]'),
					counter = more.find('.wall-cmt-count'),
					n;

				if ( more && more.length ) {
					n = +counter.text();
					if ( n > 1 )
						counter.text( n - 1 );
					else
						more.remove();
				}

				that.showAddCommentButton( ct );
			};

			jax.call(
				'community',
				'system,ajaxStreamRemoveComment',
				$( e.target ).data('id')
			);
		},

		// ---------------------------------------------------------------------
		// Remove tag.
		// ---------------------------------------------------------------------

		onRemoveStreamTag: function( e ) {
			e.preventDefault();

			var el = $( e.target ),
				ct = this.getContainer( el ),
				editor = ct.children('[data-type=stream-editor]'),
				textarea = editor.find('textarea'),
				text = textarea.data('original') || textarea.val(),
				uid = constants.get('uid'),
				tag;

			if ( !uid )
				return;

			tag = this.getTagFromUid( text, uid );
			if ( !tag )
				return;

			jax.call(
				'community',
				'activities,ajaxRemoveUserTag',
				this.getStreamId( ct ),
				tag,
				'post'
			);
		},

		onCommentStreamRemoveTag: function( e ) {
			e.preventDefault();

			var el = $( e.target ),
				content = el.closest('[data-type=stream-comment-content]'),
				editor = content.siblings('[data-type=stream-comment-editor]'),
				textarea = editor.find('textarea'),
				text = textarea.data('original') || textarea.val(),
				uid = constants.get('uid'),
				tag;

			if ( !uid )
				return;

			tag = this.getTagFromUid( text, uid );
			if ( !tag )
				return;

			jax.call(
				'community',
				'activities,ajaxRemoveUserTag',
				editor.closest('[data-type=stream-comment]').data('commentid'),
				tag,
				'comment'
			);
		},

		// ---------------------------------------------------------------------
		// Helper functions.
		// ---------------------------------------------------------------------

		getStreamId: function( el ) {
			return $( el )
				.closest('li[data-streamid]')
				.data('streamid');
		},

		getContainer: function( el ) {
			return $( el )
				.closest('li[data-streamid]')
				.children('.joms-stream-content');
		},

		getTagFromUid: function( text, uid ) {
			if ( !text || !uid )
				return false;

			var re = new RegExp( '@\\[\\[' + uid + ':contact:[^\\]]+\\]\\]' ),
				matches = text.match( re );

			return matches && matches[0];
		},

		focusTextarea: function( editor ) {
			var textarea, length;
			textarea = editor.find('textarea').focus();
			textarea = textarea[0];
			if ( textarea && textarea.setSelectionRange ) {
				length = textarea.value.length;
				textarea.setSelectionRange( length, length );
			}
		},

		hideAddCommentButton: function( ct ) {
			var comment = ct.find('[data-type=stream-action] [data-action=comment]'),
				reply = ct.find('[data-type=stream-reply]');

			comment.hide();
			reply.hide();
		},

		showAddCommentButton: function( ct ) {
			var comment = ct.find('[data-type=stream-action] [data-action=comment]'),
				reply = ct.find('[data-type=stream-reply]'),
				counter = reply.siblings('[data-type=stream-comment]').length,
				more = reply.siblings('[data-type=stream-more]').length;

			comment[ counter || more ? 'hide' : 'show' ]();
			reply[ counter || more ? 'show' : 'hide' ]();
		},

		resetStreamEditor: function( el, isComment ) {
			var textarea = $( el ).find('textarea'),
				initialized = textarea.data('initialized');

			if ( !initialized ) {
				textarea.data( 'original', textarea.val() );
				textarea.data( 'initialized', 1 );
				this.initTextntags( textarea, isComment );
			}

			textarea.textntags( 'val', textarea.data('original') );
		},

		initTextntags: function( textarea, isComment ) {
			var that = this;
			textarea.textntags({
				triggers: { '@': {
					uniqueTags: true,
					minChars: 1
				} },
				onDataRequest: function( mode, query, triggerChar, callback ) {
					var url = 'index.php?option=com_community&view=friends&task=ajaxAutocomplete&streamid=' + that.getStreamId( textarea ),
						settings = constants.get('settings');

					if ( settings.isGroup )
						url += '&groupid=' + constants.get('groupid');
					else if ( settings.isEvent )
						url += '&eventid=' + constants.get('eventid');

					if ( that.fetchTextntags )
						that.fetchTextntags.abort();

					that.fetchTextntags = $.ajax({
						url: url + '&query=' + query + ( isComment ? '&type=comment' : '' ),
						dataType: 'json',
						success: function( json ) {
							var data = [],
								i;

							if ( json && json.suggestions && json.suggestions.length ) {
								for ( i = 0; i < json.suggestions.length; i++ )
									data.push({
										id: json.data[i],
										name: json.suggestions[i],
										avatar: json.img[i].replace( /^.+src="([^"]+)".+$/ , '$1'),
										type: 'contact'
									});

								query = query.toLowerCase();
								data = $.filter( data, function( item ) { return item.name.toLowerCase().indexOf( query ) > -1; });
								callback.call( this, data );
							}
						},
						complete: function() {
							that.fetchTextntags = false;
						}
					});
				}
			});
		}

	});

});
