'use strict';

var flag = true;
var submit_form = false;
var tr_request_id = null;

var last_customer_status;
var step = 0;

const tr_form_fields = [
    '#tr_name',
    '#tr_email',
    '#tr_organization',
    '#tr_website',
    '#tr_city',
    '#tr_country',
    '#tr_terms',
    '#tr_last_name',
    '#tr_size',
];

const tr_form_dd_fields = [
    '#tr_language'
];

const hostNameBlackList = ['localhost', 'test', 'demo'];

const err_message = {
    'field_required': 'This field is required',
    'email': 'Please enter a valid Email',
    'phone_number': 'Please enter a valid phone number for the specified Country Code',
    'accept': 'Please accept Terms & Conditions',
    'website': 'Please enter a valid Website URL',
    'website_protocol': 'Website URL should start with http:// or https://',
    'website_hostname': `Website URL cannot contain ${hostNameBlackList.join(', ')}`,
    'website_match' : `Email should contain your website domaine name. Please contact <a href="mailto:support@trusty.report?subject=new%20Trusty%20request">support@trusty.report</a> if you cannot meet this criteria.`,
};


//Remove Errors
const remove_form_errors = (formId) => {
    jQuery('#' + formId + ' input').css('box-shadow', '0');
    jQuery('#' + formId + ' select').css('box-shadow', '0');
    jQuery('#' + formId + ' textarea').css('box-shadow', '0');
};

//Apply CSS to Wrong Input
const invalid_input = (el, vflag) => {
    if (vflag) {
        jQuery(el).addClass("input-err");
    } else {
        jQuery(el).removeClass("input-err");
    }
};

//Check Blank Text Field
const is_blank = (el) => {
    if (jQuery.trim(jQuery(el).val()) == "") {
        invalid_input(el, true);
        set_error_message(el, 'field_required');
        flag = false;
    } else {
        invalid_input(el, false);
        set_error_message(el);
    }
};

//Check Blank Text Field
const is_checked = (el) => {
    if (!jQuery(el).prop("checked") == true) {
        invalid_input(el, true);
        set_error_message(el, 'accept');
        flag = false;
    } else {
        invalid_input(el, false);
        set_error_message(el);
    }
};

//Email Validation
const is_email = (el) => {
    if (jQuery.trim(jQuery(el).val()) != "") {
        if (!MailChecker.isValid(jQuery(el).val())) { // run email againt MailChecker library
            invalid_input(el, true);
            set_error_message(el, 'email');
            flag = false;
        } else {
            invalid_input(el, false);
            set_error_message(el);
        }
    } else {
        invalid_input(el, true);
        set_error_message(el, 'email');
    }
};

//phone number validation
const is_phone_number_legit = (phoneNumberEl, countryEl) => {
    const phoneNumber = jQuery.trim(jQuery(phoneNumberEl).val());
    const regionCode = jQuery.trim(jQuery(countryEl).val()).toUpperCase(); // need to have the iso 2 code
    try {
        var parsedNumber = new libphonenumber.parsePhoneNumber(phoneNumber,regionCode);
        if(!parsedNumber.isPossible() || !parsedNumber.isValid()) {
            throw new Error("Not a possible or valid number");
        }
        return parsedNumber;
    } catch (error) {
        console.error(error);
        flag = false;
        invalid_input(phoneNumberEl, true);
        set_error_message(phoneNumberEl, 'phone_number');
    }
};

// check that the website is not test/demo/localhost
const is_url_legit = (el, emailEl) => {
    if (jQuery.trim(jQuery(el).val()) != "") {
        try {
            const isUrlCorrect = new URL(jQuery(el).val());

            // URL should only be http or https
            if (isUrlCorrect.protocol !== 'http:' &&
                isUrlCorrect.protocol !== 'https:') {
                invalid_input(el, true);
                set_error_message(el, 'website_protocol');
                flag = false;
                return;
            }

            // hostname should not contain localhost, demo or test
            const splittedHostName = isUrlCorrect.hostname.split('.');
            if( hostNameBlackList.some(s => splittedHostName.includes(s))) {
                invalid_input(el, true);
                set_error_message(el, 'website_hostname');
                flag = false;
                return;
            }

            // email should contain website hostname
            const email = jQuery(emailEl).val();
            const hostname = isUrlCorrect.hostname.replace('www.', '').split('.')[0];
            if( !email.includes(hostname)) {
                invalid_input(el, true);
                set_error_message(el, 'website_match');
                flag = false;
                return;
            }
            
        } catch (error) {
            invalid_input(el, true);
            set_error_message(el, 'website');
            flag = false;
        }
    }
    else {
        invalid_input(el, true);
        set_error_message(el, 'website');
    }
};

const set_error_message = (el, message = "") => {
    if (message != "" && message != false) {
        if(err_message[message].includes('<')) {
            jQuery(el).parent().find(".tr-err-text").html(err_message[message]);
        } else {
            jQuery(el).parent().find(".tr-err-text").text(err_message[message]);
        }
    } else {
        jQuery(el).parent().find(".tr-err-text").html("");
        jQuery(el).parent().find(".tr-err-text").text("");
    }
};

//Set Validation on Multiple IDs
const check_validation = (arrlst, func) => {
    jQuery.map(arrlst, function (val) {
        func(val);
    });
};


const tr_status_checking = () => {
    setTimeout(tr_status_check, 100);
}

const tr_status_check_success = (res) => {
    // debugger
    if (res && res.status === 'processing') {
        setTimeout(tr_status_check, 3000);
        if(last_customer_status === undefined || last_customer_status !== res.customer_status) {
            step += 1;
            last_customer_status = res.customer_status;
        }
        tr_status_message(res.message);
        jQuery(".tr-status-section, .tr-status-spinner").removeClass("tr-hidden");
        jQuery('.tr-status-error-icon').addClass('tr-hidden');
        window.scrollTo(0, 0);
    } else if (res && res.status === 'success') {
        jQuery(".tr-success-integration").removeClass("tr-hidden");
        jQuery(".tr-form-wrap").addClass("tr-hidden");
        jQuery(".tr-status-section").addClass("tr-hidden");
        tr_fill_success_template(res);
    } else {
        setTimeout(tr_status_check, 3000); // some kind of error? try again
        jQuery('.tr-spinner').hide();
    }
    //
    // setTimeout( () => {
    //     jQuery( document ).find( "#frm_tr_trusty" ).submit();
    // }, 1000 );
}

const tr_fill_success_template = (res) => {
    res.trusty_url = 'https://' + res.domain;
    tr_fill_template(res);
    // res.url =
    // var els = jQuery(".tr-success-integration");
    // var html = els.html();
    // html = html.replace('[[PASSWORD]]', res.password);
    // html = html.replace('[[USERNAME]]', res.username);
    // html = html.replace(/\[\[TRUSTYURL]]/g, url);
    // html = html.replace(/\[\[EMAIL]]/g, res.email);
    // // debugger
    // els.html(html);
}

const tr_fill_template = (res) => {
    var els = jQuery(".tr-success-integration");
    var html = els.html();
    // var url = 'https://' + res.domain;
    html = html.replace('[[PASSWORD]]', res.password);
    html = html.replace('[[USERNAME]]', res.username);
    html = html.replace(/\[\[TRUSTYURL]]/g, res.trusty_url);
    html = html.replace(/\[\[EMAIL]]/g, res.email);
    html = html.replace(/\[\[MAGIC_LINK]]/g, res.magic_link);
    // debugger
    els.html(html);
}



const tr_status_check = () => {

    var formData = jQuery('#frm_tr_trusty').serializeArray();
    // formData.request_id = tr_request_id;
    // formData += '&request_id=' + tr_request_id;
    var item = {}
    item["name"] = 'request_id';
    item["value"] = tr_request_id;
    formData.push(item);
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: 'json',
        data: {
            formData: JSON.stringify(formData),
            action: 'status_check',
        },
        success: tr_status_check_success
    });
}

const tr_status_message = (message) => {

    var html = `${message}`;
    if(step > 0) {
        html += `. Step ${step} of 4.`;
    }
    // message += `<h3>ID: ${res.id}</h3>`;

    jQuery(".tr-status-message").html(html);

}

const fill_template_if_created_site = () => {
    var siteData = jQuery(".tr-wrap").data("created-site");
    if (siteData) {
        tr_fill_template(siteData);
        // debugger;
    }
}

const tr_disable_submit = () => {
    jQuery('#tr-submit').addClass('disabled');
}
const tr_enable_submit = () => {
    jQuery('#tr-submit').removeClass('disabled');
}

const validate_trusty_form = () => {
    flag = true;
    check_validation(tr_form_fields, is_blank);
    is_email("#tr_email");
    is_checked("#tr_terms");
    is_url_legit("#tr_website", "#tr_email");
    var parsedNumber = is_phone_number_legit("#tr_phone_number","#tr_country_code");
    tr_disable_submit();
    if (flag) {
        var formData = jQuery('#frm_tr_trusty').serializeArray();
        formData.forEach(element => {
            if(element.name == 'tr_country_code') {
                element.value = parsedNumber.countryCallingCode;
            } else if(element.name == 'tr_phone_number') {
                element.value = parsedNumber.nationalNumber;
            }
        });
        console.log(JSON.stringify(formData));
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: 'json',
            data: {
                formData: JSON.stringify(formData),
                action: 'post_form_info',
            }, success: function (res) {
                if (res && res.status === 'success') {
                    tr_status_message(res.message);
                    tr_request_id = res.request_id
                    
                    jQuery("#tr_success_registration").val("1");
                    jQuery("#tr_registration_key").val(res.request_id);
                    jQuery(".tr-form-wrap").addClass("tr-hidden");
                    jQuery(".tr-spinner").show();

                    submit_form = true;
                    tr_status_checking();
                } else {
                    let err_message = "Error: Our servers seem to be busy. Please try again in 5 minutes.";
                    if (res && res.message) {
                        err_message = res.message;
                    }
                    tr_status_message(err_message);
                    jQuery('.tr-status-section').removeClass('tr-hidden');
                    jQuery('.tr-status-error-icon').removeClass('tr-hidden');
                    window.scrollTo(0, 0);
                    tr_enable_submit();
                }
            },
            complete: function (response) {

            }
        });
    } else {
        tr_enable_submit(); // validation fail
    }
    return submit_form;
    // return flag;
};


(function (jQuery) {
    jQuery(document).ready(function () {

        jQuery(document).on('submit', '#frm_tr_trusty', function (e) {
            if (!submit_form) {
                validate_trusty_form();
                return false;
            } else {
                return true;
            }
        });

        jQuery(document).on('click', '#tr_edit_form', function (e) {
            jQuery(".tr-form-wrap").removeClass("tr-hidden");
            jQuery(".tr-success-integration").addClass("tr-hidden");
            jQuery(this).addClass("tr-hidden");
        });

        jQuery(document).on('click', '.wbs-button-copy-code', function () {
            document.querySelector("textarea").select();
            document.execCommand('copy');
        });
        fill_template_if_created_site();
    });
})(jQuery);