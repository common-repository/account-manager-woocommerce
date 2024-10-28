jQuery(document).ready(function () {
    jQuery("#factory_reset").click(function () {
        if (!confirm("Are you sure? This process cannot be undone.")) {
            return false;
        }
    });

    var formatRepo = function (repo) {
        if (repo.loading)
            return repo.text;

        var markup = '';
        markup += '<div class="select2-result-repository clearfix>';
        markup += '<div class="select2-result-repository__meta>';
        markup += '<div class="select2-result-repository__title">' + repo.first_name + ' ' + repo.last_name + '</div>';
        if (repo.hasOwnProperty('billing_company')) {
            markup += '<div class="select2-result-repository__billing_company">' + repo.billing_company + '</div>';
        }
        markup += '</div>';
        markup += '</div>';

        return markup;
    };

    var formatRepoSelection = function (repo) {
        if (repo.hasOwnProperty('first_name') && repo.hasOwnProperty('last_name'))
            return repo.first_name + ' ' + repo.last_name;
        else
            return repo.text;
    };

    let start_date = jQuery('#start_date_datepicker');
    let end_date = jQuery('#end_date_datepicker');

    if (start_date !== undefined && start_date[0] !== undefined) {
        start_date.datepicker({
            dateFormat: 'yy-mm-dd',
            numberOfMonths: 1,
            showButtonPanel: true
        });
        start_date.on('change', function () {
            end_date.datepicker("option", "minDate", jQuery.datepicker.parseDate('yy-mm-dd', start_date[0].value))
        });
        end_date.datepicker({
            dateFormat: 'yy-mm-dd',
            numberOfMonths: 1,
            showButtonPanel: true,
            minDate: jQuery.datepicker.parseDate('yy-mm-dd', start_date[0].value)
        });
    }

    jQuery('#zacctmgr_users_edit_order_commission_list').select2({});
    jQuery('#zacctmgr_allowed_woo_status_list').select2({});
    jQuery('#zacctmgr_allowed_users_to_edit_commission_list').select2({});
    jQuery('#zacctmgr_allowed_users_to_edit_others_commission_list').select2({});


    if (jQuery('#zacctmgr_user_allow_edit_commission_setting_administrators')[0] !== undefined) {
        if (jQuery('#zacctmgr_user_allow_edit_commission_setting_administrators')[0].checked) {
            jQuery('#zacctmgr_allowed_users_to_edit_commission_list_container').css('display', 'none');
        }
    }

    jQuery('#zacctmgr_user_allow_edit_commission_setting_administrators').change(function () {
        if (jQuery('#zacctmgr_user_allow_edit_commission_setting_administrators')[0].checked) {
            jQuery('#zacctmgr_allowed_users_to_edit_commission_list_container').css('display', 'none');
        }
    });

    jQuery('#zacctmgr_user_allow_edit_commission_setting_users').change(function () {
        if (jQuery('#zacctmgr_user_allow_edit_commission_setting_users')[0].checked) {
            jQuery('#zacctmgr_allowed_users_to_edit_commission_list_container').css('display', 'block');
        }
    });

    if (jQuery('#zacctmgr_user_allow_edit_others_commission_setting_administrators')[0] !== undefined) {
        if (jQuery('#zacctmgr_user_allow_edit_others_commission_setting_administrators')[0].checked) {
            jQuery('#zacctmgr_allowed_users_to_edit_others_commission_list_container').css('display', 'none');
        }
    }

    jQuery('#zacctmgr_user_allow_edit_others_commission_setting_administrators').change(function () {
        if (jQuery('#zacctmgr_user_allow_edit_others_commission_setting_administrators')[0].checked) {
            jQuery('#zacctmgr_allowed_users_to_edit_others_commission_list_container').css('display', 'none');
        }
    });

    jQuery('#zacctmgr_user_allow_edit_others_commission_setting_users').change(function () {
        if (jQuery('#zacctmgr_user_allow_edit_others_commission_setting_users')[0].checked) {
            jQuery('#zacctmgr_allowed_users_to_edit_others_commission_list_container').css('display', 'block');
        }
    });

    if (jQuery('#zacctmgr_user_allow_edit_order_commission_setting_administrators')[0] !== undefined) {
        if (jQuery('#zacctmgr_user_allow_edit_order_commission_setting_administrators')[0].checked) {
            jQuery('#zacctmgr_allowed_users_to_edit_order_commission_list_container').css('display', 'none');
        }
    }

    jQuery('#zacctmgr_user_allow_edit_order_commission_setting_administrators').change(function () {
        if (jQuery('#zacctmgr_user_allow_edit_order_commission_setting_administrators')[0].checked) {
            jQuery('#zacctmgr_allowed_users_to_edit_order_commission_list_container').css('display', 'none');
        }
    });

    jQuery('#zacctmgr_user_allow_edit_order_commission_setting_users').change(function () {
        if (jQuery('#zacctmgr_user_allow_edit_order_commission_setting_users')[0].checked) {
            jQuery('#zacctmgr_allowed_users_to_edit_order_commission_list_container').css('display', 'block');
        }
    });


    if (jQuery('#zacctmgr_commission_type1')[0] !== undefined) {
        if (jQuery('#zacctmgr_commission_type1')[0].checked) {
            jQuery('.zacctmgr_edit_customer_commission_table').css('display', 'none');
            jQuery('#zacctmgr_edit_commission_table_manager').css('display', 'block');
        }
    }

    jQuery('#zacctmgr_commission_type1').change(function () { //Order Level selected
        if (jQuery('#zacctmgr_commission_type1')[0].checked) {
            jQuery('.zacctmgr_edit_customer_commission_table').css('display', 'none');
            jQuery('#zacctmgr_edit_commission_table_manager').css('display', 'block');
        }
    });

    if (jQuery('#zacctmgr_commission_type2')[0] !== undefined) {
        if (jQuery('#zacctmgr_commission_type2')[0].checked) {
            jQuery('.zacctmgr_edit_customer_commission_table').css('display', 'block');
            jQuery('#zacctmgr_edit_commission_table_manager').css('display', 'block');

        }
    }

    jQuery('#zacctmgr_commission_type2').change(function () { //Customer Account Level selected
        if (jQuery('#zacctmgr_commission_type2')[0].checked) {
            jQuery('.zacctmgr_edit_customer_commission_table').css('display', 'block');
            jQuery('#zacctmgr_edit_commission_table_manager').css('display', 'block');

        }
    });

    if (jQuery('#zacctmgr_commission_type3')[0] !== undefined) {
        if (jQuery('#zacctmgr_commission_type3')[0].checked) {
            jQuery('.zacctmgr_edit_customer_commission_table').css('display', 'none');
            jQuery('#zacctmgr_edit_commission_table_manager').css('display', 'none');

        }
    }

    jQuery('#zacctmgr_commission_type3').change(function () { //No Commission selected
        if (jQuery('#zacctmgr_commission_type3')[0].checked) {
            jQuery('.zacctmgr_edit_customer_commission_table').css('display', 'none');
            jQuery('#zacctmgr_edit_commission_table_manager').css('display', 'none');
        }
    });

    jQuery('.zacctmgr_select_account_manager_user_page').change(function () {
        if (jQuery('.zacctmgr_select_account_manager_user_page').val() !== jQuery('#zacctmgr_current_manager').text()) {
            jQuery('.zacctmgr_commission_calculation_user_page').css('display', 'none');
        } else {
            jQuery('.zacctmgr_commission_calculation_user_page').css('display', 'block');
        }
    });

    jQuery('#zacctmgr_filter_wc').select2({
        placeholder: 'Filter by Account Manager',
        ajax: {
            url: ajaxurl,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    action: 'search_manager'
                };

                return query;
            },
            processResults: function (managers) {
                return {
                    results: managers
                }
            },
        },
        minimumInputLength: 3,
        escapeMarkup: function (markup) {
            return markup;
        },
        templateResult: formatRepo,
        templateSelection: formatRepoSelection,
        language: {
            errorLoading: function () {
                return "Searching..."
            },
        },
        allowClear: true,
    });

    jQuery('#zacctmgr_insights_managers').select2({
        placeholder: 'Select a Account Manager...',
        ajax: {
            url: ajaxurl,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    action: 'search_manager'
                };

                return query;
            },
            processResults: function (managers) {
                return {
                    results: managers
                }
            },
        },
        minimumInputLength: 3,
        escapeMarkup: function (markup) {
            return markup;
        },
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });

    jQuery('#zacctmgr_insights_customers').select2({
        placeholder: 'Select a Customer...',
        ajax: {
            url: ajaxurl,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    action: 'search_customer'
                }

                return query;
            },
            processResults: function (customers) {
                return {
                    results: customers
                }
            },
        },
        minimumInputLength: 3,
        escapeMarkup: function (markup) {
            return markup;
        },
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });

    jQuery('body').on('change', 'select#zacctmgr_order_account_manager', function () {
        var manager_name = jQuery('select#zacctmgr_order_account_manager').val();
        var order_id = jQuery('input#zacctmgr_order_id').val();
        var recalculate_button = jQuery('a#zacctmgr_recalculate_button');

        var recalculate_link = recalculate_button.attr('href');
        var aux = recalculate_link.slice(0, recalculate_link.indexOf('&order_id=') + 10);

        var new_link = aux.replace('&order_id=', '&order_id=' + order_id + '&account_manager=' + manager_name);

        recalculate_button.attr('href', new_link);
    });


    jQuery('body').on('submit', 'form#zacctmgr_edit_order_commission', function () {
        var new_value = jQuery('input#zacctmgr_order_commission_new').val();
        var existing_value = jQuery('input#zacctmgr_order_commission_existing').val();
        var error_container = jQuery('p#zacctmgr_edit_order_commisson_error');

        if (new_value != 0 && existing_value != 0) {
            console.log('True');
            console.log('new: ');
            console.log(new_value);
            console.log('Existing:');
            console.log(existing_value);
            error_container.text('At least one of the two values should be 0. Please change one of them, then click Update Manually again!');
            return false;
        } else {
            console.log('false');
            return true;
        }

    });

    jQuery('body').on('change', 'select#zacctmgr_commission_new_type_select', function () {
        var new_type = jQuery('select#zacctmgr_commission_new_type_select').val();
        var new_value_input = jQuery('input#commission_new_value');

        if (new_type === 'percentage') {
            new_value_input.attr('max', '100');
            new_value_input.css('width', '60%');
        } else {
            new_value_input.attr('max', '1000000');
            new_value_input.css('width', '60%');
        }
    });

    jQuery('body').on('change', 'select#zacctmgr_commission_existing_type_select', function () {
        var existing_type = jQuery('select#zacctmgr_commission_existing_type_select').val();
        var existing_value_input = jQuery('input#commission_existing_value');

        if (existing_type === 'percentage') {
            existing_value_input.attr('max', '100');
            existing_value_input.css('width', '60%');
        } else {
            existing_value_input.attr('max', '1000000');
            existing_value_input.css('width', '60%');
        }
    });


    jQuery('body').on('change', 'select#zacctmgr_filter', function () {
        var manager_id = jQuery(this).val();
        var link = jQuery(this).attr("data-link");

        if (!manager_id)
            window.location.href = link + 'admin.php?page=zacctmgr';
        else
            window.location.href = link + 'admin.php?page=zacctmgr&manager_filter=' + manager_id;
    });


    jQuery('body').on('change', 'select#zacctmgr_order_filter', function () {
        var manager_id = jQuery(this).val();
        var link = jQuery(this).attr("data-link");

        if (!manager_id)
            window.location.href = link + 'admin.php?page=zacctmgr_commission&tab=orders';
        else
            window.location.href = link + 'admin.php?page=zacctmgr_commission&tab=orders&manager_filter=' + manager_id;
    });

    jQuery('body').on('click', 'input#zacctmgr_order_search_submit', function () {
        var search_term = jQuery('input#zacctmgr_search_order_term').val();
        var link = jQuery('input#zacctmgr_search_order_term').attr("data-link");

        if (search_term)
            window.location.href = link + 'admin.php?page=zacctmgr_commission&tab=orders&search_order=' + search_term;
    });

    jQuery('body').on('click', 'input#zacctmgr_customer_search_submit', function () {
        var search_term = jQuery('input#zacctmgr_search_customer_term').val();
        var link = jQuery('input#zacctmgr_search_customer_term').attr("data-link");

        if (search_term)
            window.location.href = link + 'admin.php?page=zacctmgr&name=' + search_term;
    });

    jQuery('body').on('change', 'select#zacctmgr_insights_customers', function () {
        var customer_id = jQuery(this).val();

        if (!customer_id)
            window.location.href = '/wp-admin/admin.php?page=zacctmgr_insights';
        else
            window.location.href = '/wp-admin/admin.php?page=zacctmgr_insights&customer_id=' + customer_id;
    });

    jQuery('body').on('change', 'select#zacctmgr_insights_managers', function () {
        var manager_id = jQuery(this).val();

        if (!manager_id)
            window.location.href = '/wp-admin/admin.php?page=zacctmgr_insights&tab=account_manager';
        else
            window.location.href = '/wp-admin/admin.php?page=zacctmgr_insights&tab=account_manager&manager_id=' + manager_id;
    });

    jQuery('#zacctmgr_edit_settings_form').submit(function () {
        var length = jQuery('.zacctmgr_roles_selection:checked').length;

        if (length == 0) {
            alert("Select at least one role!");
            return false;
        }
    });

    jQuery('#zacctmgr_commission_new_type_select').change(function () {
        var type = jQuery(this).val();
        jQuery('.zacctmgr_commission_new_type_result').hide();
        var id = 'zacctmgr_commission_new_type_result_' + type;
        jQuery('#' + id).fadeIn(300);
    });

    jQuery('#zacctmgr_commission_existing_type_select').change(function () {
        var type = jQuery(this).val();
        jQuery('.zacctmgr_commission_existing_type_result').hide();
        var id = 'zacctmgr_commission_existing_type_result_' + type;
        jQuery('#' + id).fadeIn(300);
    });

    var timer = null;
    jQuery('.zacctmgr_roles_selection').change(function () {
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }

        var default_manager = parseInt(jQuery('#current_default_manager').val());

        var roles = [];

        var objects = jQuery('.zacctmgr_roles_selection:checked');
        if (objects.length == 0) {
            jQuery('#zacctmgr_default').html('<option value="">Select...</option>');
        } else {
            objects.each(function (ind, obj) {
                roles.push(jQuery(obj).val());
            });

            roles = roles.join(',');

            var data = {
                action: 'get_em_users',
                roles
            };

            timer = setTimeout(function () {
                jQuery.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data,
                    dataType: 'json'
                })
                    .done(function (res) {
                        if (!res || res.length == 0) {
                            jQuery('#zacctmgr_default').html('<option value="">Select...</option>');
                        } else {
                            var html = '<option value="">Select...</option>';
                            for (var i in res) {
                                var extra = res[i].ID == default_manager ? 'selected="selected"' : '';
                                html += '<option value="' + res[i].ID + '" ' + extra + '>' + res[i].first_name + ' ' + res[i].last_name + '</option>';
                            }

                            jQuery('#zacctmgr_default').html(html);
                        }
                    })
            }, 500);
        }
    });
});