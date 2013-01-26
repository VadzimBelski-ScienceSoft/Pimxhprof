<?php

class Pimxhprof_IndexController extends Pimcore_Controller_Action_Admin {

    function indexAction() {

        $config = array (
            'base_url'		=> '/plugin/Pimxhprof/Index',
            'pdo'			=> Pimcore_Resource_Mysql::getConnection()->getConnection(),
            'template' => array(
                'base_path' => array(
                    'css' => '/plugins/Pimxhprof/vendors/xhprofio/public/css/',
                    'js' => '/plugins/Pimxhprof/vendors/xhprofio/public/js/',
                )
            )
        );

        require_once __DIR__.'/../vendors/xhprofio/index.php';
    }

}