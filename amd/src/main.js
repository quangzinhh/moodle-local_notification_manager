define([
    'jquery',
    'core/ajax',
    'core/notification',
    'core/str'
], function($, ajax, notification, str) {

    const USER_INPUT = '#nm-user-label';
    const USER_DATALIST = '#nm-user-suggestions';
    const USER_ID_INPUT = '#form-userid';

    const parseIdFromLabel = function(value) {
        if (!value) {
            return null;
        }
        const match = value.match(/^\s*(\d+)\s*-/);
        return match ? parseInt(match[1], 10) : null;
    };

    var initUserSelect = function() {
        const $input = $(USER_INPUT);
        const $userId = $(USER_ID_INPUT);

        $input.on('input', function() {
            const term = $(this).val().trim();
            const parsedId = parseIdFromLabel(term);
            const $datalist = $(USER_DATALIST);

            if (parsedId) {
                $userId.val(parsedId);
                // When an actual user is selected, submit form to load data
                document.getElementById('notification-manager-args').submit();
            } else {
                $userId.val('');
            }

            if (term.length < 1) {
                $datalist.empty();
                return;
            }

            $.ajax({
                url: M.cfg.wwwroot + '/local/notification_manager/search_users.php',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: term,
                    sesskey: M.cfg.sesskey
                }
            }).done(function(data) {
                if (!data || !Array.isArray(data.items)) {
                    $datalist.empty();
                    return;
                }
                $datalist.empty();
                data.items.forEach(function(user) {
                    const label = user.id + ' - ' + user.fullname + ' (' + user.email + ')';
                    $datalist.append('<option value="' + label + '"></option>');
                });
            }).fail(function() {
                $datalist.empty();
            });
        });

        $input.on('change', function() {
            const parsedId = parseIdFromLabel($(this).val());
            $userId.val(parsedId || '');
            if (parsedId) {
                document.getElementById('notification-manager-args').submit();
            }
        });
    };

    var initTable = function(userid, sesskey) {
        var $checkAll = $('#nm-check-all');
        var $checkItems = $('.nm-check-item');
        var $btnSoftDelete = $('#nm-btn-soft-delete');
        var $btnHardDelete = $('#nm-btn-hard-delete');

        var updateDeleteButtons = function() {
            var selectedCount = $('.nm-check-item:checked').length;
            $btnSoftDelete.prop('disabled', selectedCount === 0);
            $btnHardDelete.prop('disabled', selectedCount === 0);
        };

        $checkAll.on('change', function() {
            $checkItems.prop('checked', $(this).prop('checked'));
            updateDeleteButtons();
        });

        $checkItems.on('change', function() {
            updateDeleteButtons();
        });

        var handleDeleteAction = function(actionType) {
            str.get_strings([
                { key: 'confirm', component: 'moodle' },
                { key: actionType === 'soft' ? 'confirm_move_trash' : 'confirm_delete_permanently', component: 'local_notification_manager' },
                { key: 'yes', component: 'moodle' },
                { key: 'no', component: 'moodle' }
            ]).done(function(s) {
                notification.confirm(
                    s[0], // Confirm
                    s[1], // Are you sure...
                    s[2], // Yes
                    s[3], // No
                    function() {
                        var selectedIds = [];
                        $('.nm-check-item:checked').each(function() {
                            selectedIds.push($(this).val());
                        });

                        $.ajax({
                            url: M.cfg.wwwroot + '/local/notification_manager/delete_notifications.php',
                            method: 'POST',
                            data: {
                                sesskey: sesskey,
                                userid: userid,
                                ids: selectedIds,
                                action: actionType
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    notification.alert('Error', response.message);
                                }
                            },
                            error: function() {
                                notification.alert('Error', 'Failed to communicate with the server.');
                            }
                        });
                    }
                );
            });
        };

        $btnSoftDelete.on('click', function(e) { e.preventDefault(); handleDeleteAction('soft'); });
        $btnHardDelete.on('click', function(e) { e.preventDefault(); handleDeleteAction('hard'); });
    };

    return {
        init: function(config) {
            initUserSelect();
            if (config.userid > 0) {
                initTable(config.userid, config.sesskey);
            }
        }
    };
});
