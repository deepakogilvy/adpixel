jQuery(document).ready(function () {
    var nd = jQuery("#side-overlay-scroll")
    var loadmore = jQuery(".load-more")
    var lastScrollTop = 0
    end = false
    nd.scroll(function (e) {
        var st = nd.scrollTop();
        var ih = nd.innerHeight();
        var th = nd[0].scrollHeight
        scrollposition = 100 * st / (th - ih);
        if (!end & st > lastScrollTop && scrollposition >= 100) {
            if (!loadmore.is(':visible')) {
                loadmore.show()
                last = loadmore.siblings('li').children('div').children('a[id]').last().attr('id');
                jQuery.ajax('/notifications/loadmoreafter/' + last)
                        .done(function (data, textStatus, jqXHR) {
                            data = JSON.parse(data)
                            if (data.success) {
                                if (data.posts) {
                                    loadmore.before(data.posts)
                                } else {
                                    loadmore.show();
                                    loadmore.text('No more notifications');
                                    end = true
                                }
                            }
                        })
                        .always(function (jqXHR, textStatus, errorThrown) {
                            loadmore.hide()
                        })
            }
        }
        lastScrollTop = st
    });


    jQuery.validator.addMethod("alphabet", function (value, element) {
        return this.optional(element) || /^[a-z \s]+$/i.test(value);
    }, "Please enter only Alphabet.");
    jQuery.validator.addMethod("positive", function (value, element) {
        return this.optional(element) || /^\+?[0-9]+$/i.test(value);
    }, "Enter a positive integer number.");
    jQuery.validator.addMethod("special", function (value, element) {
        return this.optional(element) || /^(?=(.*[a-z]){1})(?=(.*[A-Z]){1})(?=(.*[0-9]){1})(?=(.*[\$\@\#]){1}).{4,}$/.test(value);
    }, "One lowercase letter, one capital letter, one number and one of the characters (#@$).");
    jQuery.validator.addMethod("charunderscore", function (value, element) {
        return this.optional(element) || /^[a-zA-Z_]+$/i.test(value);
    }, "Letters and underscores only please");
    jQuery.validator.addMethod("alphanum", function (value, element) {
        return this.optional(element) || /^[a-zA-Z0-9_ ]+$/i.test(value);
    }, "Letters, numbers, underscores and spaces only please");
    jQuery.validator.methods["date"] = function (value, element) {
        return true;
    }
    jQuery('form').each(function () {
        jQuery(this).validate({
            ignore: ".ignore",
            errorClass: 'text-danger'
        });
    });

    jQuery(document).on('click', '.setStatus', function () {
        var id = jQuery(this).attr('id');
        var controller = jQuery(this).attr('data-controller');
        var status = jQuery(this).attr('rel');
        var statusLabel = jQuery.trim(jQuery(this).attr('label'));
        var options = jQuery(this).attr('data-opt');
        var setUrl = '/' + controller + '/setstatus/' + id + '/' + status
        if (typeof (options) != 'undefined')
            setUrl = setUrl + '/' + options;
        jQuery.ajax({
            type: "GET",
            url: setUrl,
            success: function (data) {
                if (data == '0' || data == 0) {
                    if (statusLabel == "") {
                        statusLabel = 'Inactive';
                    }
                    jQuery('#label_' + id).removeClass('label-success').addClass('label-danger').text(statusLabel);
                    jQuery('#' + id).attr('rel', 0);
                } else if (data == '1' || data == 1) {
                    if (statusLabel == "") {
                        statusLabel = 'Active';
                    }
                    jQuery('#label_' + id).removeClass('label-danger').addClass('label-success').text(statusLabel);
                    jQuery('#' + id).attr('rel', 1);
                } else if (data == '-1' || data == -1) {
                    if (statusLabel == "") {
                        statusLabel = 'Rejected';
                    }
                    jQuery('#label_' + id).removeClass('label-success').addClass('label-danger').text(statusLabel);
                    jQuery('#' + id).attr('rel', 1);
                } else {
                    jQuery('#flashMessage').show().addClass('alert alert-error').text(data);
                }
            }
        });
    });
    jQuery(document).on('click', ':reset', function (e) {
        jQuery(document).find('select').each(function () {
            jQuery(this).val('');
        });
        App.initHelpers(['select2']);
    });
    jQuery(document).on('click', '.setConfirm', function () {
        var $this = jQuery(this);
        var id = jQuery(this).data('id');
        var controller = jQuery(this).attr('data-controller');
        var status = jQuery(this).attr('rel');
        var options = jQuery(this).attr('data-opt');
        var setUrl = '/' + controller + '/setConfirm/' + id + '/' + status
        if (typeof (options) != 'undefined')
            setUrl = setUrl + '/' + options;
        jQuery.ajax({
            type: "GET",
            url: setUrl,
            success: function (data) {
                if (data == '0' || data == 0) {
                    $this.text('').addClass('btn-default').attr('rel', 0);
                } else if (data == '1' || data == 1) {
                    jQuery('#confirmation_label_' + id).text('Confirmed');
                    $this.remove();//replaceWith('<span class="btn btn-xs btn-default " style="cursor:text;">Confirmed</span>');
                } else {
                    if (jQuery('#flashMessage').length == 1) {
                        jQuery('#flashMessage').show().addClass('alert alert-danger').text(data);
                    } else {
                        jQuery('#main-container').prepend('<div class="alert alert-danger" id="flashMessage" >' + data + '</div>');
                        jQuery('#flashMessage').show().addClass('alert alert-danger');

                    }

                }
            }
        });
    });

    var langSettings = jQuery.sessionStorage.get('langSettings');
    jQuery('.neo-text-translate').each(function (i, obj) {
        var name = jQuery(this).html();
        var type = jQuery(this).attr('data-setting-type');
        if (langSettings !== null) {
            var language = jql.from(langSettings).equals('name', name).equals('type', type).first();
            if (typeof (language) !== 'undefined')
                jQuery(this).html('').html(language.value);
        }
    });

    jQuery(document).on('click', '.mark_all_read', function () {
        var notification_ids = [];
        var t = jQuery('.notifications');
        jQuery(t).children().find('a[id]').each(function (idx, el) {
            if (jQuery(el).parent().parent('li').attr('style')) {
                notification_ids.push(el.id)
            }
        });
        jQuery(this).children('span.number').html("0")
        if (!notification_ids.length)
            return false;
        jQuery.ajax('/notifications/markallasread/' + notification_ids.join())
                .done(function (data, textStatus, jqXHR) {
                    data = JSON.parse(data)
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    alert('Error: the following error occurred - ' + textStatus + ' Status : ' + jqXHR.Status);
                })
    });

    jQuery(document).on('click', '.terminate_workflow', function () {
        var id = jQuery(this).attr('rel');
        var setUrl = '/projection_workbook/terminate';
        jQuery.ajax({
            url: setUrl,
            type: 'post',
            data: {'id': id},
            success: function (data, status) {
                location.reload();
            },
            error: function (xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
    });
    jQuery(document).on('click', '.delete-record', function () {
        jQuery('.block-title').html(jQuery(this).data('original-title'));
        jQuery('#deleteRecordId')
                .data('id', jQuery(this).data('id'))
                .data('controller', jQuery(this).closest('table').data('controller'));
    });
    jQuery(document).on('click', '.delete-record-id', function () {
        var setUrl = '/' + jQuery(this).data('controller') + '/delete/' + jQuery(this).data('id');
        var thisRowId = jQuery(this).data('id');
        jQuery.ajax({
            url: setUrl,
            type: 'post',
            data: {'id': jQuery(this).data('id')},
            success: function (data, status) {
                if (data.indexOf('could not be deleted') == -1) {
                    jQuery.flashMessage(data, 'success');
                    var deleteLink = jQuery("a[data-id='" + thisRowId + "']");
                    if (deleteLink.data('partial-delete') != 1) {
                        deleteLink.closest('tr').remove();
                    }
                    jQuery('.js-dataTable-serverSide').dataTable().fnDraw();
                } else {
                    jQuery.flashMessage(data, 'danger');
                }
            },
            error: function (xhr, desc, err) {
                jQuery.flashMessage('Could not delete record', 'danger');
                jQuery('.js-dataTable-serverSide').dataTable().fnDraw();
            }
        });
    });

    jQuery.flashMessage = function (message, type) {
        if (jQuery('#flashMessage').length == 1) {
            jQuery('#flashMessage').show().addClass('alert alert-' + type).text(message);
        } else {
            jQuery('#main-container').prepend('<div class="alert alert-' + type + '" id="flashMessage" >' + message + '</div>');
            jQuery('#flashMessage').show().addClass('alert alert-' + type);
        }
    }

    jQuery(document).on('click', '.terminate_workflow_csr', function () {
        var id = jQuery(this).attr('rel');
        var setUrl = '/csr_workbooks/terminate';
        jQuery.ajax({
            url: setUrl,
            type: 'post',
            data: {'id': id},
            success: function (data, status) {
                location.reload();
            },
            error: function (xhr, desc, err) {
                console.log(xhr);
                console.log("Details: " + desc + "\nError:" + err);
            }
        });
    });

    jQuery(".customTableSearch").keyup(function () {
        var value = this.value.toLowerCase();
        var SearchClass = jQuery(this).attr('rel');
        jQuery("table").find(".searchable").each(function (index) {
            var id = jQuery(this).find("." + SearchClass).first().text().toLowerCase();
            jQuery(this).toggle(id.indexOf(value) !== -1);
        });
    });
    jQuery(document).ajaxComplete(function () {
        jQuery('[data-toggle="tooltip"], .js-tooltip').tooltip({container: 'body', animation: false});
    });
    jQuery('table').floatThead({position: 'absolute', top: 60});
});

jQuery(document).on('click','.setConfirm,.removeButton',function() {
    var tooltipid = jQuery(this).attr('aria-describedby');
    jQuery('#'+tooltipid).remove();
});