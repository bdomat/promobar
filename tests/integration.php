<?php
// This file is excluded from the installable ZIP. Disposable CI store only.
if (PHP_SAPI !== 'cli' || empty($argv[1])) { exit(1); }
define('PROMOBAR_BDCENTRAL_DISABLED', true);
define('_PS_ADMIN_DIR_', rtrim($argv[1], '/') . '/admin-dev');
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/admin-dev/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
require rtrim($argv[1], '/') . '/config/config.inc.php';
class IntegrationEmployee extends Employee { public function isLoggedBack() { return true; } }
$context = Context::getContext();
$context->employee = new IntegrationEmployee(1);
$context->shop = new Shop(1);
$context->language = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
Shop::setContext(Shop::CONTEXT_SHOP, 1);
require_once rtrim($argv[1], '/') . '/app/AppKernel.php';
$kernelClass = version_compare(_PS_VERSION_, '9.0.0', '>=') ? 'AdminKernel' : 'AppKernel';
$kernel = new $kernelClass('prod', false);
$kernel->boot();
$context->controller = new AdminController();
$module = Module::getInstanceByName('promobar');
if (!$module || !$module->install()) { throw new RuntimeException('Module installation failed'); }
Configuration::updateValue('BDPO_LAST_PING', time(), false, null, 1);
$html = $module->getContent();
if (strpos($html, 'bedom_form_token') === false) { throw new RuntimeException('Configuration form did not render'); }
$before = Configuration::get('PROMOBAR_BG_COLOR', null, null, 1);
if (!$module->upgradeCompatibility()) { throw new RuntimeException('Upgrade failed'); }
if (Configuration::get('PROMOBAR_BG_COLOR', null, null, 1) !== $before) { throw new RuntimeException('Upgrade changed settings'); }
$html = $module->hookDisplayAfterBodyOpeningTag([]);
if (strpos($html, 'id="promobar"') === false) { throw new RuntimeException('Front template did not render'); }
if (!$module->uninstall()) { throw new RuntimeException('Uninstall failed'); }
echo "PASS integration: " . _PS_VERSION_ . " / PHP " . PHP_VERSION . "\n";
