<?php
/**
 * PromoBar (Announcement banner)
 *
 * @author BeDOM - Solutions Web
 * @copyright 2025 BeDOM - Solutions Web
 * @license MIT
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Promobar extends Module
{
    // Config keys
    public const CFG_ENABLED = 'PROMOBAR_ENABLED';
    public const CFG_DISMISSIBLE = 'PROMOBAR_DISMISSIBLE';
    public const CFG_MESSAGE = 'PROMOBAR_MESSAGE';       // multilingual
    public const CFG_BG_COLOR = 'PROMOBAR_BG_COLOR';
    public const CFG_TEXT_COLOR = 'PROMOBAR_TEXT_COLOR';
    public const CFG_START_DATE = 'PROMOBAR_START_DATE';
    public const CFG_END_DATE = 'PROMOBAR_END_DATE';
    public const CFG_COOKIE_DAYS = 'PROMOBAR_COOKIE_DAYS';   // int
    public const CFG_FONT_FAMILY = 'PROMOBAR_FONT_FAMILY';   // whitelist
    public const CFG_ANIMATION = 'PROMOBAR_ANIMATION';     // enum: none,scroll,pulse,blink
    public const CFG_POSITION = 'PROMOBAR_POSITION';      // 'afterbody' | 'top'
    public const CFG_COUNTDOWN = 'PROMOBAR_COUNTDOWN';     // bool
    public const CFG_CTA_ENABLED = 'PROMOBAR_CTA_ENABLED';
    public const CFG_CTA_TEXT = 'PROMOBAR_CTA_TEXT';      // multilingual
    public const CFG_CTA_URL = 'PROMOBAR_CTA_URL';
    public const CFG_CTA_BG_COLOR = 'PROMOBAR_CTA_BG_COLOR';
    public const CFG_CTA_TEXT_COLOR = 'PROMOBAR_CTA_TEXT_COLOR';
    public const CFG_CTA_BORDER = 'PROMOBAR_CTA_BORDER';
    // Author / branding
    public const AUTHOR_NAME = 'BeDOM – Solutions Web';
    public const AUTHOR_SITE = 'https://bedom.fr/';
    public const AUTHOR_CONTACT = 'https://bedom.fr/support';
    public const AUTHOR_LINKEDIN = 'https://www.linkedin.com/company/bedom-web';
    public const AUTHOR_FACEBOOK = 'https://www.facebook.com/agencebedom';
    public const AUTHOR_INSTAGRAM = 'https://www.instagram.com/bedom_web/';

    public function __construct()
    {
        $this->name = 'promobar';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'BeDOM - Solutions Web';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        // English strings → validator-friendly
        $this->displayName = $this->l('PromoBar (Announcement banner)');
        $this->description = $this->l('Configurable, secure and self-contained announcement bar (multilingual, dates, colors, font, button, animations, countdown).');
        $this->confirmUninstall = $this->l('Remove the module and its configuration?');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $idShop = (int) $this->context->shop->id;

        // Defaults
        Configuration::updateValue(self::CFG_ENABLED, 1, false, null, $idShop);
        Configuration::updateValue(self::CFG_DISMISSIBLE, 1, false, null, $idShop);
        Configuration::updateValue(self::CFG_BG_COLOR, '#111111', false, null, $idShop);
        Configuration::updateValue(self::CFG_TEXT_COLOR, '#ffffff', false, null, $idShop);
        Configuration::updateValue(self::CFG_START_DATE, '', false, null, $idShop);
        Configuration::updateValue(self::CFG_END_DATE, '', false, null, $idShop);

        Configuration::updateValue(self::CFG_COOKIE_DAYS, 30, false, null, $idShop);
        Configuration::updateValue(self::CFG_FONT_FAMILY, 'system-ui', false, null, $idShop);
        Configuration::updateValue(self::CFG_ANIMATION, 'none', false, null, $idShop);
        Configuration::updateValue(self::CFG_POSITION, 'afterbody', false, null, $idShop);
        Configuration::updateValue(self::CFG_COUNTDOWN, 0, false, null, $idShop);

        Configuration::updateValue(self::CFG_CTA_ENABLED, 0, false, null, $idShop);
        Configuration::updateValue(self::CFG_CTA_URL, '', false, null, $idShop);
        Configuration::updateValue(self::CFG_CTA_BG_COLOR, 'transparent', false, null, $idShop);
        Configuration::updateValue(self::CFG_CTA_TEXT_COLOR, '#ffffff', false, null, $idShop);
        Configuration::updateValue(self::CFG_CTA_BORDER, '#ffffff', false, null, $idShop);

        // Multilingual defaults
        $messages = [];
        $ctaTexts = [];
        foreach (Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $messages[$idLang] = $this->l('Your message here.');
            $ctaTexts[$idLang] = $this->l('Learn more');
        }
        Configuration::updateValue(self::CFG_MESSAGE, $messages, false, null, $idShop);
        Configuration::updateValue(self::CFG_CTA_TEXT, $ctaTexts, false, null, $idShop);

        // Hooks
        $ok = $this->registerHook('displayTop')
            && $this->registerHook('displayAfterBodyOpeningTag')
            && $this->registerHook('header');

        $this->maybeMigrateOldKeys($idShop);
        return $ok;
    }

    private function maybeMigrateOldKeys($idShop)
    {
        $map = [
            'BEDOM_AB_ENABLED' => self::CFG_ENABLED,
            'BEDOM_AB_DISMISSIBLE' => self::CFG_DISMISSIBLE,
            'BEDOM_AB_BG_COLOR' => self::CFG_BG_COLOR,
            'BEDOM_AB_TEXT_COLOR' => self::CFG_TEXT_COLOR,
            'BEDOM_AB_START_DATE' => self::CFG_START_DATE,
            'BEDOM_AB_END_DATE' => self::CFG_END_DATE,
            'BEDOM_AB_MESSAGE' => self::CFG_MESSAGE,
        ];

        foreach ($map as $old => $new) {
            if ($old === 'BEDOM_AB_MESSAGE') {
                foreach (Language::getLanguages(false) as $lang) {
                    $idLang = (int) $lang['id_lang'];
                    $oldVal = Configuration::get($old, $idLang, null, $idShop);
                    $newVal = Configuration::get($new, $idLang, null, $idShop);
                    if ($oldVal !== false && $newVal === false) {
                        Configuration::updateValue($new, [$idLang => $oldVal], false, null, $idShop);
                    }
                }
            } else {
                $oldVal = Configuration::get($old, null, null, $idShop);
                $newVal = Configuration::get($new, null, null, $idShop);
                if ($oldVal !== false && $newVal === false) {
                    Configuration::updateValue($new, $oldVal, false, null, $idShop);
                }
            }
        }
    }

    public function uninstall()
    {
        $keys = [
            self::CFG_ENABLED,
            self::CFG_DISMISSIBLE,
            self::CFG_MESSAGE,
            self::CFG_BG_COLOR,
            self::CFG_TEXT_COLOR,
            self::CFG_START_DATE,
            self::CFG_END_DATE,
            self::CFG_COOKIE_DAYS,
            self::CFG_FONT_FAMILY,
            self::CFG_ANIMATION,
            self::CFG_POSITION,
            self::CFG_COUNTDOWN,
            self::CFG_CTA_ENABLED,
            self::CFG_CTA_TEXT,
            self::CFG_CTA_URL,
            self::CFG_CTA_BG_COLOR,
            self::CFG_CTA_TEXT_COLOR,
            self::CFG_CTA_BORDER,
        ];
        foreach ($keys as $k) {
            Configuration::deleteByName($k);
        }
        return parent::uninstall();
    }

    /** BO: config page */
    public function getContent()
    {
        $out = '';
        if (Tools::isSubmit('submitPromobar')) {
            $this->postProcess();
            $out .= $this->displayConfirmation($this->l('Configuration saved.'));
        }
        $out .= $this->renderForm();
        $out .= $this->renderAuthorCard();

        return $out;
    }

    protected function postProcess()
    {
        $idShop = (int) $this->context->shop->id;

        $enabled = (int) Tools::getValue(self::CFG_ENABLED, 0);
        $dismiss = (int) Tools::getValue(self::CFG_DISMISSIBLE, 0);
        $bg = Tools::substr(trim((string) Tools::getValue(self::CFG_BG_COLOR, '#111111')), 0, 20);
        $fg = Tools::substr(trim((string) Tools::getValue(self::CFG_TEXT_COLOR, '#ffffff')), 0, 20);
        $start = Tools::substr(trim((string) Tools::getValue(self::CFG_START_DATE, '')), 0, 10);
        $end = Tools::substr(trim((string) Tools::getValue(self::CFG_END_DATE, '')), 0, 10);

        $cookieDays = (int) Tools::getValue(self::CFG_COOKIE_DAYS, 30);
        $allowedCookie = [1, 3, 7, 15, 30, 90, 365];
        if (!in_array($cookieDays, $allowedCookie, true)) {
            $cookieDays = 30;
        }

        $fontFamily = (string) Tools::getValue(self::CFG_FONT_FAMILY, 'system-ui');
        $fontWhitelist = $this->getFontWhitelist();
        if (!isset($fontWhitelist[$fontFamily])) {
            $fontFamily = 'system-ui';
        }

        $animation = (string) Tools::getValue(self::CFG_ANIMATION, 'none');
        $allowedAnim = ['none', 'scroll', 'pulse', 'blink'];
        if (!in_array($animation, $allowedAnim, true)) {
            $animation = 'none';
        }

        $position = (string) Tools::getValue(self::CFG_POSITION, 'afterbody');
        if (!in_array($position, ['afterbody', 'top'], true)) {
            $position = 'afterbody';
        }

        $countdown = (int) Tools::getValue(self::CFG_COUNTDOWN, 0);

        // Dates
        $reDate = '/^\d{4}-\d{2}-\d{2}$/';
        if ($start && !preg_match($reDate, $start)) {
            $start = '';
        }
        if ($end && !preg_match($reDate, $end)) {
            $end = '';
        }

        // Colors
        $reColor = '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$|^transparent$/i';
        if (!preg_match($reColor, $bg)) {
            $bg = '#111111';
        }
        if (!preg_match($reColor, $fg)) {
            $fg = '#ffffff';
        }

        // Multilingual message (plain text only)
        $messages = [];
        foreach (Language::getLanguages(false) as $lang) {
            $val = (string) Tools::getValue(self::CFG_MESSAGE . '_' . $lang['id_lang'], '');
            $val = Tools::substr(trim($val), 0, 1000);
            $val = strip_tags($val);
            $messages[(int) $lang['id_lang']] = $val;
        }

        // CTA
        $ctaEnabled = (int) Tools::getValue(self::CFG_CTA_ENABLED, 0);
        $ctaTexts = [];
        foreach (Language::getLanguages(false) as $lang) {
            $t = (string) Tools::getValue(self::CFG_CTA_TEXT . '_' . $lang['id_lang'], '');
            $t = Tools::substr(trim($t), 0, 80);
            $t = Tools::purifyHTML($t);
            $ctaTexts[(int) $lang['id_lang']] = $t;
        }

        $ctaUrl = Tools::substr(trim((string) Tools::getValue(self::CFG_CTA_URL, '')), 0, 255);
        if ($ctaUrl !== '' && !filter_var($ctaUrl, FILTER_VALIDATE_URL)) {
            $ctaUrl = '';
        }
        if ($ctaUrl !== '' && !preg_match('#^https?://#i', $ctaUrl)) {
            $ctaUrl = '';
        }

        $ctaBg = Tools::substr(trim((string) Tools::getValue(self::CFG_CTA_BG_COLOR, 'transparent')), 0, 20);
        $ctaText = Tools::substr(trim((string) Tools::getValue(self::CFG_CTA_TEXT_COLOR, '#ffffff')), 0, 20);
        $ctaBd = Tools::substr(trim((string) Tools::getValue(self::CFG_CTA_BORDER, '#ffffff')), 0, 20);
        if (!preg_match($reColor, $ctaBg)) {
            $ctaBg = 'transparent';
        }
        if (!preg_match($reColor, $ctaText)) {
            $ctaText = '#ffffff';
        }
        if (!preg_match($reColor, $ctaBd)) {
            $ctaBd = '#ffffff';
        }

        // Save
        Configuration::updateValue(self::CFG_ENABLED, $enabled, false, null, $idShop);
        Configuration::updateValue(self::CFG_DISMISSIBLE, $dismiss, false, null, $idShop);
        Configuration::updateValue(self::CFG_BG_COLOR, $bg, false, null, $idShop);
        Configuration::updateValue(self::CFG_TEXT_COLOR, $fg, false, null, $idShop);
        Configuration::updateValue(self::CFG_START_DATE, $start, false, null, $idShop);
        Configuration::updateValue(self::CFG_END_DATE, $end, false, null, $idShop);

        Configuration::updateValue(self::CFG_COOKIE_DAYS, $cookieDays, false, null, $idShop);
        Configuration::updateValue(self::CFG_FONT_FAMILY, $fontFamily, false, null, $idShop);
        Configuration::updateValue(self::CFG_ANIMATION, $animation, false, null, $idShop);
        Configuration::updateValue(self::CFG_POSITION, $position, false, null, $idShop);
        Configuration::updateValue(self::CFG_COUNTDOWN, $countdown, false, null, $idShop);

        Configuration::updateValue(self::CFG_MESSAGE, $messages, false, null, $idShop);

        Configuration::updateValue(self::CFG_CTA_ENABLED, $ctaEnabled, false, null, $idShop);
        Configuration::updateValue(self::CFG_CTA_TEXT, $ctaTexts, false, null, $idShop);
        Configuration::updateValue(self::CFG_CTA_URL, $ctaUrl, false, null, $idShop);
        Configuration::updateValue(self::CFG_CTA_BG_COLOR, $ctaBg, false, null, $idShop);
        Configuration::updateValue(self::CFG_CTA_TEXT_COLOR, $ctaText, false, null, $idShop);
        Configuration::updateValue(self::CFG_CTA_BORDER, $ctaBd, false, null, $idShop);
    }

    /** Whitelist of fonts (no external loads) */
    protected function getFontWhitelist()
    {
        return [
            'system-ui' => 'system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif',
            'inter' => '"Inter", system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif',
            'arial' => 'Arial, Helvetica, sans-serif',
            'helvetica' => 'Helvetica, Arial, sans-serif',
            'georgia' => 'Georgia, "Times New Roman", Times, serif',
            'times' => '"Times New Roman", Times, serif',
            'courier' => '"Courier New", Courier, monospace',
            'mono' => 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
        ];
    }

    /** Build the configuration form */
    /**
     * For my use case, the Message field was a little restricting.
     * Minor update to make it more lax so i can use other html features
     * Changed the input type from textarea to a tinymce to allow
     * for better message formatting
     * 
     */
    protected function renderForm()
    {
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $fontWhitelist = $this->getFontWhitelist();

        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Banner settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Display position'),
                        'name' => self::CFG_POSITION,
                        'options' => [
                            'query' => [
                                ['id' => 'afterbody', 'name' => $this->l('After opening <body> (recommended)')],
                                ['id' => 'top', 'name' => $this->l('Top of page (displayTop)')],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('The banner will display on the chosen hook only to avoid duplicates.'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable banner'),
                        'name' => self::CFG_ENABLED,
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Close button (do not show again)'),
                        'name' => self::CFG_DISMISSIBLE,
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Cookie lifetime (close button)'),
                        'name' => self::CFG_COOKIE_DAYS,
                        'options' => [
                            'query' => [
                                ['id' => 1, 'name' => '1 day'],
                                ['id' => 3, 'name' => '3 days'],
                                ['id' => 7, 'name' => '7 days'],
                                ['id' => 15, 'name' => '15 days'],
                                ['id' => 30, 'name' => '30 days'],
                                ['id' => 90, 'name' => '90 days'],
                                ['id' => 365, 'name' => '365 days'],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('Used only if the “close” button is enabled.'),
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Message'),
                        'name' => self::CFG_MESSAGE,
                        'lang' => true,
                        'cols' => 40,
                        'rows' => 4,
                        'class' => 'rte',
                        'autoload_rte' => true,
                        'desc' => $this->l('Keep it simple. Avoid long messages and unnecessary HTML.'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Banner font'),
                        'name' => self::CFG_FONT_FAMILY,
                        'options' => [
                            'query' => array_map(
                                function ($k, $v) {
                                    return ['id' => $k, 'name' => $k];
                                },
                                array_keys($fontWhitelist),
                                $fontWhitelist
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('Safe whitelist (no external resources loaded).'),
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l('Background color'),
                        'name' => self::CFG_BG_COLOR,
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l('Text color'),
                        'name' => self::CFG_TEXT_COLOR,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Text animation'),
                        'name' => self::CFG_ANIMATION,
                        'options' => [
                            'query' => [
                                ['id' => 'none', 'name' => $this->l('None')],
                                ['id' => 'scroll', 'name' => $this->l('Horizontal marquee')],
                                ['id' => 'pulse', 'name' => $this->l('Soft pulse')],
                                ['id' => 'blink', 'name' => $this->l('Light blink')],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('Respects accessibility preferences (reduced motion).'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show countdown (if end date set)'),
                        'name' => self::CFG_COUNTDOWN,
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                        'desc' => $this->l('Automatically stops at the end date. Neutral appearance (black digits on white).'),
                    ],
                    [
                        'type' => 'date',
                        'label' => $this->l('Start date (optional)'),
                        'name' => self::CFG_START_DATE,
                    ],
                    [
                        'type' => 'date',
                        'label' => $this->l('End date (optional)'),
                        'name' => self::CFG_END_DATE,
                    ],
                    // CTA
                    [
                        'type' => 'switch',
                        'label' => $this->l('Show a button'),
                        'name' => self::CFG_CTA_ENABLED,
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Button text (multilingual)'),
                        'name' => self::CFG_CTA_TEXT,
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Button URL'),
                        'name' => self::CFG_CTA_URL,
                        'desc' => $this->l('Must start with http:// or https://'),
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l('Button background color'),
                        'name' => self::CFG_CTA_BG_COLOR,
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l('Button text color'),
                        'name' => self::CFG_CTA_TEXT_COLOR,
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l('Button border color'),
                        'name' => self::CFG_CTA_BORDER,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'name' => 'submitPromobar',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitPromobar';

        $helper->languages = $this->context->controller->getLanguages();
        // Do NOT set $helper->id_language (may not exist in some PS versions)

        $fieldsValue = $this->getConfigValues();
        $helper->fields_value = $fieldsValue;
        $helper->tpl_vars = [
            'fields_value' => $fieldsValue,
            'languages' => $helper->languages,
            'id_language' => (int) $this->context->language->id,
        ];

        return $helper->generateForm([$fieldsForm]);
    }
    /** Render the author's badge/card with logo and social links */
    protected function renderAuthorCard()
    {
        $links = [];

        if (self::AUTHOR_SITE !== '' && filter_var(self::AUTHOR_SITE, FILTER_VALIDATE_URL)) {
            $links[] = ['label' => $this->l('Website'), 'url' => self::AUTHOR_SITE];
        }
        if (self::AUTHOR_CONTACT !== '' && filter_var(self::AUTHOR_CONTACT, FILTER_VALIDATE_URL)) {
            $links[] = ['label' => $this->l('Support'), 'url' => self::AUTHOR_CONTACT];
        }
        if (self::AUTHOR_LINKEDIN !== '' && filter_var(self::AUTHOR_LINKEDIN, FILTER_VALIDATE_URL)) {
            $links[] = ['label' => 'LinkedIn', 'url' => self::AUTHOR_LINKEDIN];
        }
        if (self::AUTHOR_FACEBOOK !== '' && filter_var(self::AUTHOR_FACEBOOK, FILTER_VALIDATE_URL)) {
            $links[] = ['label' => 'Facebook', 'url' => self::AUTHOR_FACEBOOK];
        }
        if (self::AUTHOR_INSTAGRAM !== '' && filter_var(self::AUTHOR_INSTAGRAM, FILTER_VALIDATE_URL)) {
            $links[] = ['label' => 'Instagram', 'url' => self::AUTHOR_INSTAGRAM];
        }

        $this->context->smarty->assign([
            'pb_author_name' => self::AUTHOR_NAME,
            'pb_author_logo' => $this->_path . 'views/img/author_logo.png',
            'pb_links' => $links,
            'pb_title' => $this->l('Module by'),
            'pb_tagline' => $this->l('High-performance web solutions, built for impact.'),
            'pb_module_version' => $this->version,
            'pb_module_name' => $this->displayName,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/author_card.tpl');
    }

    protected function getConfigValues()
    {
        $idShop = (int) $this->context->shop->id;

        $vals = [
            self::CFG_POSITION => (string) Configuration::get(self::CFG_POSITION, null, null, $idShop),
            self::CFG_ENABLED => (int) Configuration::get(self::CFG_ENABLED, null, null, $idShop),
            self::CFG_DISMISSIBLE => (int) Configuration::get(self::CFG_DISMISSIBLE, null, null, $idShop),
            self::CFG_BG_COLOR => (string) Configuration::get(self::CFG_BG_COLOR, null, null, $idShop),
            self::CFG_TEXT_COLOR => (string) Configuration::get(self::CFG_TEXT_COLOR, null, null, $idShop),
            self::CFG_START_DATE => (string) Configuration::get(self::CFG_START_DATE, null, null, $idShop),
            self::CFG_END_DATE => (string) Configuration::get(self::CFG_END_DATE, null, null, $idShop),
            self::CFG_COOKIE_DAYS => (int) Configuration::get(self::CFG_COOKIE_DAYS, null, null, $idShop),
            self::CFG_FONT_FAMILY => (string) Configuration::get(self::CFG_FONT_FAMILY, null, null, $idShop),
            self::CFG_ANIMATION => (string) Configuration::get(self::CFG_ANIMATION, null, null, $idShop),
            self::CFG_COUNTDOWN => (int) Configuration::get(self::CFG_COUNTDOWN, null, null, $idShop),
            self::CFG_CTA_ENABLED => (int) Configuration::get(self::CFG_CTA_ENABLED, null, null, $idShop),
            self::CFG_CTA_URL => (string) Configuration::get(self::CFG_CTA_URL, null, null, $idShop),
            self::CFG_CTA_BG_COLOR => (string) Configuration::get(self::CFG_CTA_BG_COLOR, null, null, $idShop),
            self::CFG_CTA_TEXT_COLOR => (string) Configuration::get(self::CFG_CTA_TEXT_COLOR, null, null, $idShop),
            self::CFG_CTA_BORDER => (string) Configuration::get(self::CFG_CTA_BORDER, null, null, $idShop),
        ];

        // Multilingual
        $vals[self::CFG_MESSAGE] = [];
        $vals[self::CFG_CTA_TEXT] = [];
        foreach (Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $vals[self::CFG_MESSAGE][$idLang] = (string) Configuration::get(self::CFG_MESSAGE, $idLang, null, $idShop);
            $vals[self::CFG_CTA_TEXT][$idLang] = (string) Configuration::get(self::CFG_CTA_TEXT, $idLang, null, $idShop);
        }

        return $vals;
    }

    /** Front: assets */
    public function hookHeader($params)
    {
        $controller = $this->context->controller;

        if (method_exists($controller, 'registerStylesheet')) {
            $controller->registerStylesheet(
                'promobar-front',
                'modules/' . $this->name . '/views/css/front.css',
                ['media' => 'all', 'priority' => 150]
            );
            $controller->registerJavascript(
                'promobar-front',
                'modules/' . $this->name . '/views/js/front.js',
                ['position' => 'bottom', 'priority' => 150]
            );
        } else {
            $controller->addCSS('modules/' . $this->name . '/views/css/front.css', 'all');
            $controller->addJS('modules/' . $this->name . '/views/js/front.js');
        }
    }

    private function shouldRenderInHook($where)
    {
        $idShop = (int) $this->context->shop->id;
        $pos = (string) Configuration::get(self::CFG_POSITION, null, null, $idShop);
        if ($pos !== 'top' && $pos !== 'afterbody') {
            $pos = 'afterbody';
        }
        return $where === $pos;
    }

    /**
     * Safe mini-markup to HTML:
     * - **bold** → <strong>
     * - [text](https://url) → <a href="…">
     * - auto-link http(s)://… → <a href="…">
     * - \n → <br>
     */
    private function renderMiniMarkup($text)
    {
        // 1) escape everything
        $safe = htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');

        // 2) [label](url)
        $safe = preg_replace_callback(
            '#\[(.+?)\]\((https?://[^\s)]+)\)#i',
            function ($m) {
                $label = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                $href = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
                return '<a href="' . $href . '" target="_blank" rel="noopener noreferrer">' . $label . '</a>';
            },
            $safe
        );

        // 3) auto-link naked URLs
        $safe = preg_replace_callback(
            '#(?<!["\'])(https?://[^\s<]+)#i',
            function ($m) {
                $u = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                return '<a href="' . $u . '" target="_blank" rel="noopener noreferrer">' . $u . '</a>';
            },
            $safe
        );

        // 4) **bold**
        $safe = preg_replace('#\*\*(.+?)\*\*#s', '<strong>$1</strong>', $safe);

        // 5) newlines → <br>
        $safe = nl2br($safe, false);

        return $safe;
    }

    private function renderBar()
    {
        $idShop = (int) $this->context->shop->id;
        if (!(int) Configuration::get(self::CFG_ENABLED, null, null, $idShop)) {
            return '';
        }

        // Period
        $start = (string) Configuration::get(self::CFG_START_DATE, null, null, $idShop);
        $end = (string) Configuration::get(self::CFG_END_DATE, null, null, $idShop);
        $today = new \DateTime('now');

        try {
            $eligibleStart = !$start || ($today >= new \DateTime($start . ' 00:00:00'));
            $eligibleEnd = !$end || ($today <= new \DateTime($end . ' 23:59:59'));
            $show = $eligibleStart && $eligibleEnd;
        } catch (\Exception $e) {
            $show = true;
        }

        if (!$show) {
            return '';
        }

        $idLang = (int) $this->context->language->id;
        $message = (string) Configuration::get(self::CFG_MESSAGE, $idLang, null, $idShop);
        $message = trim($message);
        if ($message === '') {
            return '';
        }

        // Safe HTML (rendered client-side via data-html to satisfy validator)
        $messageHtml = $this->renderMiniMarkup($message);

        $dismissible = (int) Configuration::get(self::CFG_DISMISSIBLE, null, null, $idShop);
        if (Tools::getValue('promobar_preview') != 1) {
            if ($dismissible && isset($_COOKIE['promobar_dismissed']) && $_COOKIE['promobar_dismissed'] === '1') {
                return '';
            }
        }

        // Countdown
        $countdownEnabled = (int) Configuration::get(self::CFG_COUNTDOWN, null, null, $idShop);
        $countdownEndIso = '';
        if ($countdownEnabled && $end) {
            try {
                $dtEnd = new \DateTime($end . ' 23:59:59');
                if ($dtEnd > $today) {
                    $countdownEndIso = $dtEnd->getTimestamp() * 1000;
                } else {
                    $countdownEnabled = 0;
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        // CTA
        $ctaEnabled = (int) Configuration::get(self::CFG_CTA_ENABLED, null, null, $idShop);
        $ctaText = (string) Configuration::get(self::CFG_CTA_TEXT, $idLang, null, $idShop);
        $ctaUrl = (string) Configuration::get(self::CFG_CTA_URL, null, null, $idShop);

        $this->context->smarty->assign([
            'promobar_message' => $messageHtml,     // sanitized HTML string
            'promobar_message_plain' => $message,         // fallback text

            'promobar_bg' => (string) Configuration::get(self::CFG_BG_COLOR, null, null, $idShop),
            'promobar_fg' => (string) Configuration::get(self::CFG_TEXT_COLOR, null, null, $idShop),
            'promobar_dismissible' => $dismissible,
            'promobar_cookie' => 'promobar_dismissed',
            'promobar_cookie_days' => (int) Configuration::get(self::CFG_COOKIE_DAYS, null, null, $idShop),
            'promobar_font' => (string) Configuration::get(self::CFG_FONT_FAMILY, null, null, $idShop),
            'promobar_animation' => (string) Configuration::get(self::CFG_ANIMATION, null, null, $idShop),

            'promobar_countdown' => (int) $countdownEnabled,
            'promobar_countdown_end' => $countdownEndIso,

            'promobar_cta_enabled' => $ctaEnabled,
            'promobar_cta_text' => trim($ctaText),
            'promobar_cta_url' => $ctaUrl,
            'promobar_cta_bg' => (string) Configuration::get(self::CFG_CTA_BG_COLOR, null, null, $idShop),
            'promobar_cta_text_col' => (string) Configuration::get(self::CFG_CTA_TEXT_COLOR, null, null, $idShop),
            'promobar_cta_border' => (string) Configuration::get(self::CFG_CTA_BORDER, null, null, $idShop),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayTop.tpl');
    }

    public function hookDisplayTop($params)
    {
        return $this->shouldRenderInHook('top') ? $this->renderBar() : '';
    }

    public function hookDisplayAfterBodyOpeningTag($params)
    {
        return $this->shouldRenderInHook('afterbody') ? $this->renderBar() : '';
    }
}
