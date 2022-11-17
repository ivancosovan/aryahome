<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);

class eutils_yago extends CModule{
    
    var $MODULE_ID = 'eutils.yago';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $PARTNER_NAME;
	public $PARTNER_URI;
    
    public function __construct(){
    
        if(file_exists(__DIR__."/version.php")){

            $arModuleVersion = array();

            include_once(__DIR__."/version.php");
            
            $this->MODULE_VERSION      = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        $this->MODULE_NAME          = Loc::getMessage("EUTILS_YAGO_NAME");
        $this->MODULE_DESCRIPTION   = Loc::getMessage("EUTILS_YAGO_DESCRIPTION");
        $this->PARTNER_NAME         = Loc::getMessage("EUTILS_YAGO_PARTNER_NAME");
        $this->PARTNER_URI          = Loc::getMessage("EUTILS_YAGO_PARTNER_URI");
        
        return false;
    }
    
    public function DoInstall() {

		global $APPLICATION;
		
        //Add order props
		include(dirname(__FILE__)."/include/order_props.php");
		include(dirname(__FILE__)."/include/iblock.php");
		
        if(CheckVersion(ModuleManager::getVersion("main"), "14.00.00")){

            $this->InstallFiles();
//             $this->InstallDB();

            ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallEvents();
        }else{

            $APPLICATION->ThrowException(
                    Loc::getMessage("EUTILS_YAGO_INSTALL_ERROR_VERSION")
            );
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("EUTILS_YAGO_INSTALL_TITLE")." \"".Loc::getMessage("EUTILS_YAGO_NAME")."\"",
            __DIR__."/step.php"
        );

        return false;
  
	}
	
	public function InstallFiles(){
        CopyDirFiles(
        __DIR__."/assets/php-scripts",
            Application::getDocumentRoot()."/statusy-dostavki/",
        true,
        true
        );
    }
    
    public function InstallDB(){

        return false;
    }
    
    public function InstallEvents(){
        RegisterModuleDependences('sale', 'OnSaleStatusOrderChange', 'eutils.yago', 'YaGo', 'OnSaleStatusOrderChange');
        return false;
    }
    
    public function DoUninstall(){

        global $APPLICATION;
        //Delete order props
		include(dirname(__FILE__)."/include/del_order_props.php");
		include(dirname(__FILE__)."/include/del_ibloсk.php");
        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("EUTILS_YAGO_UNINSTALL_TITLE")." \"".Loc::getMessage("EUTILS_YAGO_NAME")."\"",
                __DIR__."/unstep.php"
        );

        return false;
    }
    
    public function UnInstallFiles(){

        \Bitrix\Main\IO\File::deleteFile(
            Application::getDocumentRoot()."/statusy-dostavki/ajax-cancel.php"
        );

        return false;
    }
    
    public function UnInstallDB(){

        Option::delete($this->MODULE_ID);

        return false;
    }
    
    public function UnInstallEvents(){
        UnRegisterModuleDependences('sale', 'OnSaleStatusOrderChange', 'eutils.yago', 'YaGo', 'OnSaleStatusOrderChange');
        return false;
    }

}
