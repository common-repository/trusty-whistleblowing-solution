<?php
require_once plugin_dir_path(__FILE__) . '../config/constants.php';
require_once plugin_dir_path(__FILE__) . '../config/formConstants.php';

$current_user = wp_get_current_user();
$current_user->user_email;

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain_name = sanitize_url($_SERVER['HTTP_HOST']);

if (get_option('tr_trusty_url')) {
    $createdSiteData = [
        'email' => esc_html(get_option(trEmail)),
        'trusty_url' => esc_html(get_option(trTrustyUrl)),
        'magic_link' => esc_html(get_option(trMagicLink)),
    ];
} else {
    $createdSiteData = false;
}

function addInput($label, $input, $info, $required = false)
{
    $requiredText = $required ? '<span style="color:#EB0487">*</span>' : '';
    echo '<tr valign="top">
            <th scope="row">' . $label . $requiredText . '</th>
            <td>
                ' . $input . '
                <p class="tr-err-text"></p>
            </td>
            <td class="wbs-field-info">
                <p class="wbs-italic-text-info">' . $info . '</p>
            </td>
        </tr>';
}

function addInputTextWithValue($id, $label, $info, $value, $required = false, $maxLength = 255)
{
    $requiredText = $required ? 'required' : '';
    $input = '<input type="text" id="' . $id . '" name="' . $id . '" value="' . $value . '" ' . $requiredText . ' maxlength="'.$maxLength.'"/>';
    addInput($label, $input, $info, $required);
}

function addInputText($id, $label, $info, $required = false, $defaultValue = '', $maxLength = 255)
{
    $value = esc_html(get_option($id, $defaultValue));
    addInputTextWithValue($id, $label, $info, $value, $required, $maxLength);
}

function addInputTel($id, $label, $info, $required = false, $defaultValue = '')
{
    $value = esc_html(get_option($id, $defaultValue));
    $requiredText = $required ? 'required' : '';
    $input = '<input type="tel" id="' . $id . '" name="' . $id . '" value="' . $value . '" ' . $requiredText . '"/>';
    addInput($label, $input, $info, $required);
}

function addSelectKeyInArray($id, $label, $info, $selectData, $required = false, $defaultValue = '')
{
    $requiredText = $required ? 'required' : '';
    $val = esc_html(get_option($id, $defaultValue));

    $options = array();
    foreach($selectData as $key => $value) {
        $option = '<option value="' . $key . '">' . $value . '</option>';
        array_push($options, $option);
    }
    $select = '<select id="' . $id . '" name="' . $id . '" value="' . $val . '" ' . $requiredText . '>
                    <option></option>
                    ' . implode($options) . '
                </select>';
    addInput($label, $select, $info, $required);
}

function addSelect($id, $label, $info, $selectData, $required = false, $defaultValue = '', $customFn = null, $customArr = null)
{
    $requiredText = $required ? 'required' : '';
    $value = esc_html(get_option($id, $defaultValue));
    if (is_null($customFn)) {
        $func = function (string $option): string {
            return '<option value="' . $option . '">' . $option . '</option>';
        };
    } else {
        $func = $customFn;
    }

    if (is_array($customArr)) {
        $options = implode(array_map($func, $selectData, $customArr));
    } else {
        $options = implode(array_map($func, $selectData));
    }

    $select = '<select id="' . $id . '" name="' . $id . '" value="' . $value . '" ' . $requiredText . '>
                    <option></option>
                    ' . $options . '
                </select>';
    addInput($label, $select, $info, $required);
}

function addCheckbox($id, $label, $checkboxLabel, $info, $defaultValue = '1', $required = false)
{
    $requiredText = $required ? 'required' : '';
    $input = '<input id="' . $id . '" type="checkbox" name="' . $id . '" value="' . $defaultValue . '" ' . $requiredText . '/>' . $checkboxLabel;
    addInput($label, $input, $info, $required);
}

?>

<div class="tr-wrap" data-created-site='<?php echo json_encode($createdSiteData) ?>'>
    <div class="wbs-header-section">
        <div class="wbs-header-logo">
            <img src="<?php echo TWBS_PLUGIN_URL . "/public/img/WBS.png"; ?>" height="100px">
        </div>
        <div class="wbs-header-title-section">
            <h1>Whistleblowing Solution</h1>
            <h2>INSTANT. SECURE. CUSTOMIZABLE</h2>
        </div>
    </div>

    <div class="tr-status-section tr-hidden">
        <div style="display: flex;align-items: center;">
            <span class="tr-status-error-icon tr-hidden">&#x26A0;</span>
            <svg class="tr-status-spinner tr-hidden" width="70" style="margin: .1em 1em;" height="70"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="3" r="0">
                    <animate id="a" begin="0;l.end-0.5s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0"
                        keySplines=".27,.42,.37,.99;.53,0,.61,.73" />
                </circle>
                <circle cx="16.50" cy="4.21" r="0">
                    <animate id="b" begin="a.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0"
                        keySplines=".27,.42,.37,.99;.53,0,.61,.73" />
                </circle>
                <circle cx="7.50" cy="4.21" r="0">
                    <animate id="l" begin="k.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0"
                        keySplines=".27,.42,.37,.99;.53,0,.61,.73" />
                </circle>
                <circle cx="19.79" cy="7.50" r="0">
                    <animate id="c" begin="b.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0"
                        keySplines=".27,.42,.37,.99;.53,0,.61,.73" />
                </circle>
                <circle cx="4.21" cy="7.50" r="0">
                    <animate id="k" begin="j.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0"
                        keySplines=".27,.42,.37,.99;.53,0,.61,.73" />
                </circle>
                <circle cx="21.00" cy="12.00" r="0">
                    <animate id="d" begin="c.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0"
                        keySplines=".27,.42,.37,.99;.53,0,.61,.73" />
                </circle>
                <circle cx="3.00" cy="12.00" r="0">
                    <animate id="j" begin="i.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0"
                        keySplines=".27,.42,.37,.99;.53,0,.61,.73" />
                </circle>
                <circle cx="19.79" cy="16.50" r="0">
                    <animate id="e" begin="d.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0"
                        keySplines=".27,.42,.37,.99;.53,0,.61,.73" />
                </circle>
                <circle cx="4.21" cy="16.50" r="0">
                    <animate id="i" begin="h.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0"
                        keySplines=".27,.42,.37,.99;.53,0,.61,.73" />
                </circle>
                <circle cx="16.50" cy="19.79" r="0">
                    <animate id="f" begin="e.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0"
                        keySplines=".27,.42,.37,.99;.53,0,.61,.73" />
                </circle>
                <circle cx="7.50" cy="19.79" r="0">
                    <animate id="h" begin="g.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0"
                        keySplines=".27,.42,.37,.99;.53,0,.61,.73" />
                </circle>
                <circle cx="12" cy="21" r="0">
                    <animate id="g" begin="f.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0"
                        keySplines=".27,.42,.37,.99;.53,0,.61,.73" />
                </circle>
            </svg>
            <span class="tr-status-message"></span>
        </div>
    </div>
    <div class="tr-form-wrap <?php echo (empty(esc_attr(get_option(trRegistrationKey)))) ? '' : 'tr-hidden' ?>">

        <form method="post" action="options.php" id="frm_tr_trusty">
            <?php settings_fields('tr-free-wbs-plugin-settings-group'); ?>
            <?php do_settings_sections('tr-free-wbs-plugin-settings-group'); ?>

            <table class="form-table">

                <?php
                trWriteLog('display form');
                addInputText(trName, 'First Name', '', true, $current_user->display_name, 40);
                addInputText(trLastName, 'Last Name', '', true, '', 40);
                addInputText(trEmail, 'Email', 'Your corporate/business email address<br>(the email address will
                    be initially registered as the email address of the first user of the solution and Trusty
                    account details will also be sent to this address)', true, $current_user->user_email);
                addSelectKeyInArray(trRole, 'Company Role', 'Your role in the company', trRoleList, true);
                addSelectKeyInArray(trCountryCode, 'Country Code', '', trPhoneCodeListCountry, true);
                addInputTel(trPhoneNumber, 'Phone Number', '', true);
                addInputText(trOrganizazion, 'Company Legal Name', 'Your company legal name', true);
                addInputText(trWebsite, 'Company Website', 'Your company homepage', true, esc_url(get_option('tr_website', $domain_name)));
                addInputText(trCity, 'Company City', 'Your company main office/HQ city', true);

                addSelectKeyInArray(trCountry, 'Company Country', 'Your company main office/HQ country', trCountryList, true);


                addSelectKeyInArray(trSize, 'Company Size', 'How many employees does your company currently have ?', trSizeList, true);
                
                $langFunc = function (string $key, string $lang): string {
                    if ($key === 'en') {
                        return '<option value="' . $key . '" selected>' . $lang . '</option>';
                    }
                    return '<option value="' . $key . '">' . $lang . '</option>';
                };
                addSelect(trLanguage, 'Company Language', 'The default language of your Trusty account webpages', array_keys(trLanguageList), true, '', $langFunc, array_values(trLanguageList));
                addCheckbox(trTerms, 'Terms', 'I accept the <a
                    href="' . trTermsLink . '"
                    target="_blank">Terms of service</a> and <a
                    href="' . trPolicyLink . '"
                    target="_blank">Privacy policy</a>.', '', '1', true);
                addCheckbox(trOptional, 'Optional', 'I agree to receive additional communication from Trusty on compliance products, including newsletters and other materials.', '');
                ?>

            </table>

            <input type="hidden" id="<?php echo trSuccessRegistration; ?>" name="tr_success_registration" value="">
            <input type="hidden" id="<?php echo trRegistrationKey; ?>" name="tr_registration_key" value="">

            <?php submit_button("Submit", 'primary', 'tr-submit', true, 'style="width:14em;"'); ?>

        </form>

    </div>

    <div
        class="tr-success-integration <?php echo (!empty(esc_attr(get_option(trRegistrationKey)))) ? '' : 'tr-hidden' ?>">
        <div class="tr-message">

            <div class="wbs-content-section">
                <div class="wbs-account-activated-message">
                    <p>Your Trusty Business Plan has been sucessfully activated and your FREE 14-day trial starts now!</p>
                </div>
                <div class="wbs-link-section">
                    <p class="wbs-diff-style">Your <span class="wbs-decorated-link-span">LINK TO THE WHISTLEBLOWING
                            SOLUTION</span> is ready <a class="wbs-link-style" target="_blank"
                            href="[[MAGIC_LINK]]">[[TRUSTYURL]]</a></p>

                    <p class="wbs-italic-text">
                        We encourage you to make this link available to anyone from whom you wish to receive reports,
                        either by publishing it in your webpage, intranet or sending it out in emails.
                    </p>

                    <p class="wbs-italic-text">
                        You can copy the following code to embed the link on the appropriate section within your
                        website:
                    </p>

                    <div class="wbs-copy-code-section">
                        <button class="wbs-button-copy-code">Copy Code</button>
                        <span class="wbs-copy-code-content">
                            <textarea
                                readonly><a href="[[TRUSTYURL]]" title="Whistleblowing">Whistleblowing</a></textarea>
                        </span>
                    </div>
                </div>
                <div class="wbs-link-section">
                    <p class="wbs-diff-style">
                        Set your <span class="wbs-decorated-link-span">PASSWORD</span> and access your <span class="wbs-decorated-link-span">CASE MANAGEMENT TOOL</span> here <a
                            class="wbs-link-style" target="_blank" href="[[MAGIC_LINK]]">[[TRUSTYURL]]</a>
                    </p>

                    <p class="wbs-italic-text">
                        Details have also been forwarded to the following email: <a href="mailto:[[EMAIL]]">[[EMAIL]]</a>
                    </p>
                </div>
                <div class="wbs-rating">
                    <p class="wbs-italic-text">
                        We hope you'll find our big pink elephant helpful! Please help us by <a
                            href="https://wordpress.org/support/plugin/trusty-whistleblowing-solution/reviews/"
                            target="_blank" class="wbs-decorated-link-span">RATING</a> our plugin. It will only take a
                        moment and we will be happy to replace the Trusty logo with your company/organization logo in
                        your Trusty account.
                    </p>
                </div>
            </div>
        </div>
        <!--        <button id="tr_edit_form" name="tr_edit_form" value="Edit Form" class="button button-primary">Edit Form</button>-->
    </div>

    <div class="tr-status">

    </div>

</div>