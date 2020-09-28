<?php
class mymoduledisplayModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->setTemplate('module:contact/views/templates/front/displayData.tpl');
    }
}
