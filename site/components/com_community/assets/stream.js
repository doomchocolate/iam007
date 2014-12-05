joms.extend({
    settings: '',
    stream: {
        init: function(options) {
            settings = options;
            var Stream = this;
            var elStream = options.elStream;
            var elActivity = options.elActivity;
            var elPrivacy = options.elPrivacy;

            joms.jQuery( document ).on( 'click', [ elStream, elActivity, elPrivacy, '.dropdown-menu li a' ].join(' '), function() {
                var el = joms.jQuery( this ),
                    intStreamId = el.closest( elActivity ).data('streamid'),
                    intPrivacyValue = el.data('option-value');

                Stream.updatePrivacy(intStreamId, intPrivacyValue);
            });

            joms.stream.like();
            joms.stream.dislike();
            joms.stream.comment();
            joms.stream.editStatus();
            joms.stream.hideStatus();
            joms.stream.ajaxAddMood();
            joms.stream.ajaxRemoveMood();
            joms.stream.ignoreUser();
            joms.stream.showlike();

        },
        like: function() {
            joms.jQuery(settings.elStream).on('click', '[data-action="like"]', function(e) {
                e.preventDefault();
                var streamId = joms.jQuery(this).data('stream-id');
                var streamType = joms.jQuery(this).data('stream-type');
                jax.call('community', 'system,ajaxStreamAddLike', streamId, streamType);
            });

        },
        dislike: function() {
            joms.jQuery(settings.elStream).on('click', '[data-action="unlike"]', function(e) {
                e.preventDefault();
                var streamId = joms.jQuery(this).data('stream-id');
                var streamType = joms.jQuery(this).data('stream-type');
                jax.call('community', 'system,ajaxStreamUnlike', streamId, streamType);
            });
        },
        share: function() {

        },
        comment: function() {},
        updatePrivacy: function(intStreamId, intPrivacyValue) {
            jax.call('community', 'activities,ajaxUpdatePrivacyActivity', intStreamId, intPrivacyValue);
        },
        editStatus: function() {},
        showTextarea: function() {},
        hideStatus: function() {
            joms.jQuery(settings.elStream).on('click', '[data-action="hide"]', function(e) {
                e.preventDefault();
                var t = joms.jQuery(this);
                var p = t.parents('li');
                var streamId = t.data('stream-id');
                var userId = t.data('user-id');
                var groupId = t.data('group-id') ? t.data('group-id') : null;
                jax.call('community', 'activities,ajaxHideStatus', streamId, userId, groupId);

            });
        },
        updateHideStatus: function(data) {
            var data = joms.jQuery.parseJSON(data);
            var streamId = data.streamId;
            var userId = data.userId;
            var groupId = data.groupId;
            var html = unescape(data.html);
            var target = joms.jQuery(settings.elStream).find('li[data-streamid="' + streamId + '"]');

            target.children().hide();
            target.html(html);

            target.find('a[data-action="close-hide"]').click(function(e) {
                e.preventDefault();
                target.remove();
            });
        },
        ajaxSaveStatus: function(id, data) {
            jax.call('community', 'activities,ajaxSaveStatus', id, data);
        },
        ajaxAddMood: function() {
            joms.jQuery(settings.elStream).on('click', '[data-action="add-mood"]', function(e) {
                e.preventDefault();
                var t = joms.jQuery(this);
                var streamId = t.data('stream-id');
                var ajaxCall = "jax.call('community', 'activities,ajaxAddMood', '" + streamId + "' )";
                cWindowShow(ajaxCall, '', 200, 200);
            });
        },
        ajaxRemoveMood: function() {
            joms.jQuery(settings.elStream).on('click', '[data-action="remove-mood"]', function(e) {
                e.preventDefault();
                var t = joms.jQuery(this);
                var streamId = t.data('stream-id');
                var ajaxCall = "jax.call('community', 'activities,ajaxConfirmRemoveMood', '" + streamId + "' )";
                cWindowShow(ajaxCall, '', 450, 100);
            });
        },
        showOthers: function(id) {
            var ajaxCall = "jax.call('community', 'activities,ajaxShowOthers', '" + id + "' )";
            cWindowShow(ajaxCall, '', 450, 100);
        },
        /**
         * Bind to ignore menu item
         */
        ignoreUser: function() {
            joms.jQuery(settings.elStream).on('click', '[data-action="ignore"]', function(e) {
                e.preventDefault();
                var t = joms.jQuery(this);
                var userId = t.data('user-id');
                var ajaxCall = "jax.call('community', 'activities,ajaxConfirmIgnoreUser', '" + userId + "' )";
                cWindowShow(ajaxCall, '', 450, 100);
            });
        },
        showlike: function() {
            joms.jQuery(settings.elStream).on('click','[data-action="showlike"]',function(e){
                e.preventDefault();
                var t = joms.jQuery(this);
                var id = t.data('stream-id');
                var ajaxCall = "jax.call('community', 'activities,ajaxshowLikedUser', '" + id + "' )";
                cWindowShow(ajaxCall,'',450,100);
            });
        }
    }
});

joms.jQuery(document).ready(function() {
    joms.stream.init({
        elStream: '#activity-stream-container',
        elActivity: '.joms-stream',
        elPrivacy: '.joms-stream-privacy'
    });
})

