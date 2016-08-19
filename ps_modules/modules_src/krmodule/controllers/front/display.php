<?php
/**
 * Created by Develjitsu.com.
 * User: Ramón Albertí
 * Date: 18/08/16
 * Time: 17:56
 */

class krmoduledisplayModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->setTemplate('display.tpl');
    }
}