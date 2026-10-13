<?php
// Isolated regression tests. No store credentials or real network requests are used.
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
define('_PS_VERSION_', getenv('PS_VERSION') ?: '9.2.0');
define('PROMOBAR_BDCENTRAL_DISABLED', true);
define('__PS_BASE_URI__', '/');
class TestEmployee { public $id = 1; public $logged = true; public function isLoggedBack() { return $this->logged; } }
class TestSmarty { public $values = []; public function assign($values) { $this->values = array_merge($this->values, $values); } public function fetch($path) { return $path; } }
class TestController { public function getLanguages() { return Language::getLanguages(); } public function addJS($s) {} public function addCSS($s) {} }
class TestLink { public $withToken; public function getAdminLink($controller, $withToken = true, $route = [], $params = []) { $this->withToken = $withToken; return 'https://shop.example/admin/index.php?controller='.$controller.'&id_order=12&token=SECRET&_token=SECRET2'; } }
class Module {
    public $id = 1, $context, $permission = true, $hooks = [], $version, $name, $tab, $author, $need_instance, $bootstrap, $ps_versions_compliancy, $displayName, $description, $confirmUninstall, $identifier = '', $table = '', $_path = '/', $local_path;
    public function __construct() {
        $this->context = (object) ['employee' => new TestEmployee(), 'shop' => new Shop(1), 'language' => (object) ['id' => 1], 'smarty' => new TestSmarty(), 'controller' => new TestController(), 'link' => new TestLink()];
        $this->local_path = dirname(__DIR__) . '/';
    }
    public function l($s) { return $s; }
    public function getPermission($action, $employee = null) { return $this->permission; }
    public function registerHook($name) { $this->hooks[$name] = true; return true; }
    public function unregisterHook($name) { unset($this->hooks[$name]); return true; }
    public function isRegisteredInHook($name) { return !empty($this->hooks[$name]); }
    public function displayError($s) { return 'ERROR: '.$s; }
    public function displayWarning($s) { return 'WARNING: '.$s; }
    public function displayConfirmation($s) { return 'OK: '.$s; }
    public function display($file, $tpl) { return $tpl; }
    public function install() { return true; }
    public function uninstall() { return true; }
}
class Shop { const CONTEXT_SHOP = 1; public static $scope = 1; public $id, $name = 'Test Shop', $domain = 'shop.example'; public function __construct($id) { $this->id = $id; } public static function getContext() { return self::$scope; } public static function getShops($active = true, $group = null, $ids = false) { return [1, 2]; } }
class Language { public static function getLanguages($active = true) { return [['id_lang' => 1, 'iso_code' => 'en'], ['id_lang' => 2, 'iso_code' => 'fr']]; } }
class Configuration {
    public static $data = [];
    public static function get($key, $lang = null, $group = null, $shop = null) { $v = isset(self::$data[$shop][$key]) ? self::$data[$shop][$key] : false; return $lang && is_array($v) ? (isset($v[$lang]) ? $v[$lang] : false) : $v; }
    public static function updateValue($key, $value, $html = false, $group = null, $shop = null) { self::$data[$shop][$key] = $value; return true; }
    public static function deleteByName($key) { foreach (self::$data as &$shop) { unset($shop[$key]); } return true; }
}
class Tools {
    public static function getValue($key, $default = false) { return isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $default); }
    public static function isSubmit($key) { return isset($_POST[$key]) || isset($_GET[$key]); }
    public static function getAdminTokenLite($tab) { return 'local-test-csrf'; }
    public static function substr($s, $start, $length = null) { return $length === null ? substr($s, $start) : substr($s, $start, $length); }
    public static function strlen($s) { return strlen($s); }
    public static function purifyHTML($s) { return strip_tags($s); }
    public static function getShopDomain($a = false, $b = true) { return 'shop.example'; }
    public static function getShopDomainSsl($a = false, $b = true) { return 'https://shop.example'; }
}
class Validate { public static function isLoadedObject($o) { return is_object($o) && !empty($o->id); } }
class HelperForm {
    public $fields_value = [], $tpl_vars = [];
    public function generateForm($forms) { return ['forms' => $forms, 'values' => $this->tpl_vars['fields_value']]; }
}
function check($value, $message) { global $checks; ++$checks; if (!$value) { throw new RuntimeException($message); } }
function callPrivate($object, $method, array $args = []) { $r = new ReflectionMethod($object, $method); if (PHP_VERSION_ID < 80100) { $r->setAccessible(true); } return $r->invokeArgs($object, $args); }
$checks = 0;

require dirname(__DIR__).'/promobar.php';
$module = new Promobar();

check($module->ps_versions_compliancy['max'] === '9.2.99', 'Compatibility must be bounded');
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['bedom_form_token' => 'local-test-csrf'];
check(callPrivate($module, 'validConfigurationRequest'), 'Valid administrator POST');
$_POST['bedom_form_token'] = ['local-test-csrf'];
check(!callPrivate($module, 'validConfigurationRequest'), 'Reject array token');
$_POST['bedom_form_token'] = 'invalid';
check(!callPrivate($module, 'validConfigurationRequest'), 'Reject invalid token');
$_POST['bedom_form_token'] = 'local-test-csrf';
$_SERVER['REQUEST_METHOD'] = 'GET';
check(!callPrivate($module, 'validConfigurationRequest'), 'Reject GET mutation');
$_SERVER['REQUEST_METHOD'] = 'POST';
$module->permission = false;
check(!callPrivate($module, 'validConfigurationRequest'), 'Reject employee without permission');
$module->permission = true;
$module->context->employee->logged = false;
check(!callPrivate($module, 'validConfigurationRequest'), 'Reject expired employee session');
$module->context->employee->logged = true;
$_POST['bad'] = ['nested' => []];
check(callPrivate($module, 'inputValue', ['bad', 'fallback']) === 'fallback', 'Reject non-scalar field');
$helper = new HelperForm();
$form = callPrivate($module, 'addFormToken', [$helper, [['form' => ['input' => []]]]]);
check($form['values']['bedom_form_token'] === 'local-test-csrf', 'CSRF field value');
check($form['forms'][0]['form']['input'][0]['type'] === 'hidden', 'CSRF field definition');
$_POST = [];
$_GET = [];

$html = callPrivate($module, 'renderMiniMarkup', ['**Save** [Shop](https://shop.example/?a=1&b=2) <img src=x onerror=alert(1)>']);
check(strpos($html, '<strong>Save</strong>') !== false, 'Bold markup');
check(substr_count($html, '<a ') === 1, 'Exactly one link');
check(strpos($html, '&amp;amp;') === false, 'URL not double escaped');
check(strpos($html, '<img') === false, 'Raw HTML escaped');
check(strpos($html, 'rel="noopener noreferrer"') !== false, 'Link opener protection');
foreach (['javascript:alert(1)', 'data:text/html,test', 'https://example.com/" onmouseover="x', "https://example.com/\r\n", 'https://example.com\\@evil.test'] as $url) {
    // Leading/trailing whitespace is intentionally trimmed.
    if (strpos($url, "\r") !== false) { $url = "https://example.com/a\r\nb"; }
    check(callPrivate($module, 'safeUrl', [$url]) === '', 'Unsafe URL rejected');
}
check(callPrivate($module, 'safeColor', ['red; background:url(https://evil.test)', '#111111']) === '#111111', 'CSS injection rejected');
check(callPrivate($module, 'safeDate', ['2026-02-31']) === '', 'Impossible date rejected');
Configuration::updateValue('PROMOBAR_MESSAGE', [1 => 'Keep me', 2 => 'À conserver'], false, null, 1);
Configuration::updateValue('PROMOBAR_BG_COLOR', '#123456', false, null, 2);
$before = Configuration::$data;
check($module->upgradeCompatibility(), 'Upgrade succeeds');
check(Configuration::$data === $before, 'Upgrade preserves all multilingual/shop settings');
Configuration::updateValue('PROMOBAR_ENABLED', 1, false, null, 1);
Configuration::updateValue('PROMOBAR_CTA_URL', 'javascript:alert(1)', false, null, 1);
Configuration::updateValue('PROMOBAR_BG_COLOR', 'red;anything:bad', false, null, 1);
Configuration::updateValue('PROMOBAR_FONT_FAMILY', 'evil;anything:bad', false, null, 1);
callPrivate($module, 'renderBar');
check($module->context->smarty->values['promobar_cta_url'] === '', 'Legacy stored URL revalidated');
check($module->context->smarty->values['promobar_bg'] === '#111111', 'Legacy stored CSS revalidated');
check(strpos($module->context->smarty->values['promobar_font'], 'evil') === false, 'Stored font whitelisted');
$before = Configuration::$data;
$_POST = ['submitPromobar' => '1'];
check(strpos($module->getContent(), 'ERROR:') === 0, 'Missing CSRF rejected before write');
check(Configuration::$data === $before, 'Rejected request cannot modify configuration');
echo 'PASS PromoBar: '.$checks.' checks on PHP '.PHP_VERSION."\n";
