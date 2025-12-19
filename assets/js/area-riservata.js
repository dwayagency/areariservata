/**
 * Area Riservata - Frontend JavaScript
 */

(function ($) {
    'use strict';

    // Registration Form
    $('#ar-register-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $messages = $form.find('.ar-form-messages');
        var $submitBtn = $form.find('button[type="submit"]');

        // Validate passwords match
        var password = $('#ar-password').val();
        var passwordConfirm = $('#ar-password-confirm').val();

        if (password !== passwordConfirm) {
            showMessage($messages, 'error', arData.strings.error || 'Le password non corrispondono');
            return;
        }

        // Disable submit button
        $submitBtn.prop('disabled', true).html('<span class="ar-loading"></span> ' + (arData.strings.loading || 'Caricamento...'));

        $.ajax({
            url: arData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ar_register_user',
                nonce: arData.registerNonce,
                email: $('#ar-email').val(),
                password: password,
                first_name: $('#ar-first-name').val(),
                last_name: $('#ar-last-name').val()
            },
            success: function (response) {
                if (response.success) {
                    showMessage($messages, 'success', response.data.message);
                    $form[0].reset();
                } else {
                    showMessage($messages, 'error', response.data.message);
                }
            },
            error: function () {
                showMessage($messages, 'error', arData.strings.error || 'Errore di connessione');
            },
            complete: function () {
                $submitBtn.prop('disabled', false).text($submitBtn.data('original-text') || 'Registrati');
            }
        });
    });

    // Login Form
    $('#ar-login-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $messages = $form.find('.ar-form-messages');
        var $submitBtn = $form.find('button[type="submit"]');

        $submitBtn.prop('disabled', true).html('<span class="ar-loading"></span> ' + (arData.strings.loading || 'Caricamento...'));

        $.ajax({
            url: arData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ar_login',
                nonce: arData.registerNonce,
                email: $('#ar-login-email').val(),
                password: $('#ar-login-password').val(),
                remember: $('input[name="remember"]').is(':checked')
            },
            success: function (response) {
                if (response.success) {
                    showMessage($messages, 'success', response.data.message);
                    if (response.data.redirect) {
                        setTimeout(function () {
                            window.location.href = response.data.redirect;
                        }, 1000);
                    }
                } else {
                    showMessage($messages, 'error', response.data.message);
                    $submitBtn.prop('disabled', false).text('Accedi');
                }
            },
            error: function () {
                showMessage($messages, 'error', arData.strings.error || 'Errore di connessione');
                $submitBtn.prop('disabled', false).text('Accedi');
            }
        });
    });

    // Password Reset Form
    $('#ar-reset-password-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $messages = $form.find('.ar-form-messages');
        var $submitBtn = $form.find('button[type="submit"]');

        $submitBtn.prop('disabled', true).html('<span class="ar-loading"></span> ' + (arData.strings.loading || 'Caricamento...'));

        $.ajax({
            url: arData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ar_send_reset_link',
                email: $('#ar-reset-email').val()
            },
            success: function (response) {
                if (response.success) {
                    showMessage($messages, 'success', response.data.message);
                    $form[0].reset();
                } else {
                    showMessage($messages, 'error', response.data.message);
                }
            },
            error: function () {
                showMessage($messages, 'error', arData.strings.error || 'Errore di connessione');
            },
            complete: function () {
                $submitBtn.prop('disabled', false).text('Invia Link Reset');
            }
        });
    });

    // Logout
    $(document).on('click', '.ar-logout-btn', function (e) {
        e.preventDefault();

        $.ajax({
            url: arData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ar_logout'
            },
            success: function (response) {
                if (response.success && response.data.redirect) {
                    window.location.href = response.data.redirect;
                } else {
                    window.location.reload();
                }
            }
        });
    });

    // Admin Tabs
    $('.ar-tab-btn').on('click', function () {
        var tabId = $(this).data('tab');

        $('.ar-tab-btn').removeClass('active');
        $(this).addClass('active');

        $('.ar-tab-content').removeClass('active');
        $('#tab-' + tabId).addClass('active');
    });

    // Approve User
    $(document).on('click', '.ar-approve-user', function () {
        var userId = $(this).data('user-id');
        var $row = $(this).closest('tr');

        $.ajax({
            url: arData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ar_approve_user',
                nonce: arData.adminNonce,
                user_id: userId
            },
            success: function (response) {
                if (response.success) {
                    $row.fadeOut(300, function () {
                        $(this).remove();
                        updatePendingBadge();
                    });
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Reject User
    $(document).on('click', '.ar-reject-user', function () {
        if (!confirm(arData.strings.confirm_reject || 'Sei sicuro di voler rifiutare questo utente?')) {
            return;
        }

        var userId = $(this).data('user-id');
        var $row = $(this).closest('tr');

        $.ajax({
            url: arData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ar_reject_user',
                nonce: arData.adminNonce,
                user_id: userId
            },
            success: function (response) {
                if (response.success) {
                    $row.fadeOut(300, function () {
                        $(this).remove();
                        updatePendingBadge();
                    });
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Disable User
    $(document).on('click', '.ar-disable-user', function () {
        var userId = $(this).data('user-id');
        var $btn = $(this);

        $.ajax({
            url: arData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ar_disable_user',
                nonce: arData.adminNonce,
                user_id: userId,
                disable: 'true'
            },
            success: function (response) {
                if (response.success) {
                    $btn.removeClass('ar-btn-warning ar-disable-user')
                        .addClass('ar-btn-success ar-enable-user')
                        .text('Abilita');
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Enable User
    $(document).on('click', '.ar-enable-user', function () {
        var userId = $(this).data('user-id');
        var $btn = $(this);

        $.ajax({
            url: arData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ar_disable_user',
                nonce: arData.adminNonce,
                user_id: userId,
                disable: 'false'
            },
            success: function (response) {
                if (response.success) {
                    $btn.removeClass('ar-btn-success ar-enable-user')
                        .addClass('ar-btn-warning ar-disable-user')
                        .text('Disabilita');
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Upload Document
    $('#ar-upload-document-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $messages = $form.find('.ar-form-messages');
        var $submitBtn = $form.find('button[type="submit"]');
        var formData = new FormData(this);

        formData.append('action', 'ar_upload_document');
        formData.append('nonce', arData.adminNonce);

        $submitBtn.prop('disabled', true).html('<span class="ar-loading"></span> ' + (arData.strings.loading || 'Caricamento...'));

        $.ajax({
            url: arData.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    showMessage($messages, 'success', response.data.message);
                    $form[0].reset();
                    // Refresh documents tab if needed
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage($messages, 'error', response.data.message);
                }
            },
            error: function () {
                showMessage($messages, 'error', arData.strings.error || 'Errore di connessione');
            },
            complete: function () {
                $submitBtn.prop('disabled', false).text('Carica Documento');
            }
        });
    });

    // Delete Document
    $(document).on('click', '.ar-delete-document', function () {
        if (!confirm(arData.strings.confirm_delete || 'Sei sicuro di voler eliminare questo documento?')) {
            return;
        }

        var docId = $(this).data('doc-id');
        var $row = $(this).closest('tr');

        $.ajax({
            url: arData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ar_delete_document',
                nonce: arData.adminNonce,
                document_id: docId
            },
            success: function (response) {
                if (response.success) {
                    $row.fadeOut(300, function () {
                        $(this).remove();
                    });
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Create User
    $('#ar-create-user-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $messages = $form.find('.ar-form-messages');
        var $submitBtn = $form.find('button[type="submit"]');

        $submitBtn.prop('disabled', true).html('<span class="ar-loading"></span> ' + (arData.strings.loading || 'Caricamento...'));

        $.ajax({
            url: arData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ar_create_user',
                nonce: arData.adminNonce,
                email: $('#ar-new-email').val(),
                password: $('#ar-new-password').val(),
                first_name: $('#ar-new-first-name').val(),
                last_name: $('#ar-new-last-name').val(),
                auto_approve: $('input[name="auto_approve"]').is(':checked') ? 'true' : 'false'
            },
            success: function (response) {
                if (response.success) {
                    showMessage($messages, 'success', response.data.message);
                    $form[0].reset();
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage($messages, 'error', response.data.message);
                }
            },
            error: function () {
                showMessage($messages, 'error', arData.strings.error || 'Errore di connessione');
            },
            complete: function () {
                $submitBtn.prop('disabled', false).text('Crea Utente');
            }
        });
    });

    // Helper: Show message
    function showMessage($container, type, message) {
        $container.removeClass('success error')
            .addClass(type)
            .html(message)
            .show();
    }

    // Helper: Update pending badge
    function updatePendingBadge() {
        var count = $('#tab-pending-users tbody tr').length;
        $('.ar-tab-btn[data-tab="pending-users"] .ar-badge').text(count);
    }

})(jQuery);
