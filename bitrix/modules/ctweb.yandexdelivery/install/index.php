<?
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

if (class_exists("ctweb_yandexdelivery"))
    return;

Class ctweb_yandexdelivery extends CModule
{
    var $MODULE_ID = "ctweb.yandexdelivery";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $strError = "";

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__)."/version.php");
        $this->MODULE_VERSION      = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME         = GetMessage("CW_YD_INSTALL_NAME");
        $this->MODULE_DESCRIPTION  = GetMessage("CW_YD_INSTALL_DESCRIPTION");
        $this->PARTNER_NAME        = GetMessage("CW_YD_PARTNER_NAME");
        $this->PARTNER_URI         = GetMessage("CW_YD_PARTNER_URI");
    }

    function InstallDB()
    {
        global $DB;
        $this->errors = false;

        ModuleManager::registerModule($this->MODULE_ID);

        $this->errors = $DB->RunSQLBatch(__DIR__ . '/db/'.strtolower($DB->type).'/install.sql');

        // Set default options
		COption::SetOptionString($this->MODULE_ID, "OPTION_NAME", "OPTION_VALUE");

        return true;
    }

    function UnInstallDB()
    {
        global $DB;
        $this->errors = false;

        $this->errors = $DB->RunSQLBatch(__DIR__ . '/db/'.strtolower($DB->type).'/uninstall.sql');

        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles()
    {	
    	CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/assets/js", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $this->MODULE_ID . "/", true, true);
    	CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/assets/css", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/css/" . $this->MODULE_ID . "/", true, true);
    	CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/delivery_ctwebyandexdelivery", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/include/sale_delivery/", true, true);

		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/components')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.')
                        continue;
                    CopyDirFiles($p . '/' . $item, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/' . $item, $ReWrite = True, $Recursive = True);
                }
                closedir($dir);
            }
        }
       
        return true;
    }

    function UnInstallFiles()
    {

        DeleteDirFilesEx("/bitrix/js/" . $this->MODULE_ID . "/");
        DeleteDirFilesEx("/bitrix/css/" . $this->MODULE_ID . "/");
        DeleteDirFilesEx("/bitrix/php_interface/include/sale_delivery/delivery_ctwebyandexdelivery.php");

        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/components')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.' || !is_dir($p0 = $p . '/' . $item))
                        continue;

                    $dir0 = opendir($p0);
                    while (false !== $item0 = readdir($dir0)) {
                        if ($item0 == '..' || $item0 == '.')
                            continue;
                        DeleteDirFilesEx('/bitrix/components/' . $item . '/' . $item0);
                    }
                    closedir($dir0);
                }
                closedir($dir);
            }
        }

        return true;
    }

    function DoInstall()
    {
        $this->InstallDB();
        $this->InstallFiles();
        $this->InstallEvents();
        $GLOBALS["errors"] = $this->errors;
    }

    function DoUninstall()
    {
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        $this->UnInstallDB();
    }
}

?>
