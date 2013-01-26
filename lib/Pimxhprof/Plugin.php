<?php

if(
    strpos($_SERVER['REQUEST_URI'], '/plugin/Pimxhprof') !== 0 &&
    strpos($_SERVER['REQUEST_URI'], '/admin') !== 0 &&
    !isset($_GET['dc']) &&
    !isset($_GET['pimcore_preview']) &&
    !isset($_GET['pimcore_editmode']) &&
    function_exists('xhprof_enable')
) {
    xhprof_enable(XHPROF_FLAGS_MEMORY | XHPROF_FLAGS_CPU);

    define('PIMXHPROF_MONITOR_ENABLE', true);
} else {
    define('PIMXHPROF_MONITOR_ENABLE', false);
}

/*
if(strpos($_SERVER['REQUEST_URI'], '/plugin/Pimxhprof/public/') === 0) {

    ob_end_clean();

    if(strpos($_SERVER['REQUEST_URI'], '/plugin/Pimxhprof/public/js') === 0) {
        header('Content-Type: text/javascript');
        echo file_get_contents(__DIR__.'/../../vendors/xhprofio/public/js/'.basename($_SERVER['REQUEST_URI']));
        die();
    }

    if(strpos($_SERVER['REQUEST_URI'], '/plugin/Pimxhprof/public/css') === 0) {
        header('Content-Type: text/css');
        echo file_get_contents(__DIR__.'/../../vendors/xhprofio/public/css/'.basename($_SERVER['REQUEST_URI']));
        die();
    }

    if(strpos($_SERVER['REQUEST_URI'], '/plugin/Pimxhprof/public/images') === 0) {
        header('Content-Type: image/png');
        echo file_get_contents(__DIR__.'/../../vendors/xhprofio/public/images/'.basename($_SERVER['REQUEST_URI']));
        die();
    }
}*/


class Pimxhprof_Plugin extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface {

    public static function install() {

        if(!function_exists('xhprof_enable'))
            return "Pimxhprof Plugin could not be installed - Check your xhprof installation ...";

        if(!(Pimcore_Resource_Mysql::getConnection()->getConnection() instanceof \Pdo))
            return "The installation requires the Pdo_Mysql Adapter, sorry.";

        $q = str_replace(':', '', preg_replace("/\s*(?!<\")\/\*[^\*]+\*\/(?!\")\s*/","", file_get_contents(__DIR__.'/../../vendors/xhprofio/setup/database.sql')));

        if(!Pimcore_Resource_Mysql::getConnection()->query($q))
            return "Pimxhprof Plugin could not be installed - Db trouble ...";

        if(!touch(PIMCORE_CONFIGURATION_DIRECTORY.'/.pimxhprof.enable'))
            return 'Pimxhprof Plugin could not be installed - cant create '. PIMCORE_CONFIGURATION_DIRECTORY . '/.pimxhprof.enable';

        return "Pimxhprof Plugin successfully installed.";
    }

    public static function uninstall() {

        // truncate the database, ...
        if(!Pimcore_Resource_Mysql::getConnection()->query(file_get_contents(__DIR__.'/../../vendors/xhprofio/setup/database.sql')))
            return "Pimxhprof Plugin could not be installed - Db trouble ...";

        unlink(PIMCORE_CONFIGURATION_DIRECTORY . '/.pimxhprof.enable');

        if (!self::isInstalled()) {
            $statusMessage = "Pimxhprof Plugin successfully uninstalled.";
        } else {
            $statusMessage = "Pimxhprof Plugin could not be uninstalled";
        }
        return $statusMessage;

    }

    public static function isInstalled() {
       return file_exists(PIMCORE_CONFIGURATION_DIRECTORY . '/.pimxhprof.enable');
    }

    public static function getTranslationFileDirectory() {
        return PIMCORE_PLUGINS_PATH . "/Pimxhprof/texts";
    }

    /**
     *
     * @param string $language
     * @return string path to the translation file relative to plugin direcory
     */
    public static function getTranslationFile($language) {
        if (is_file(PIMCORE_PLUGINS_PATH . "/Pimxhprof/texts/" . $language . ".csv")) {
            return "/Pimxhprof/texts/" . $language . ".csv";
        } else {
            return "/Pimxhprof/texts/en.csv";
        }

    }

    public function preDispatch() {
        if(!PIMXHPROF_MONITOR_ENABLE)
            return;

        register_shutdown_function(function(){

            $xhprof_data	= xhprof_disable();

            if(function_exists('fastcgi_finish_request'))
                fastcgi_finish_request();

            require_once __DIR__.'/../../vendors/xhprofio/xhprof/classes/data.php';

            $xhprof_data_obj = new \ay\xhprof\Data(Pimcore_Resource_Mysql::getConnection()->getConnection());
            $xhprof_data_obj->save($xhprof_data);
        });
    }

}