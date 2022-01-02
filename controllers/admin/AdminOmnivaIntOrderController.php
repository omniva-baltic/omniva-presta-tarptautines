<?php

require_once "AdminOmnivaIntBaseController.php";
require_once __DIR__ . "/../../classes/models/OmnivaIntOrder.php";

class AdminOmnivaIntOrderController extends AdminOmnivaIntBaseController
{

    public function __construct()
    {
        parent::__construct();

        $this->list_no_link = true;
        $this->className = 'OmnivaIntOrder';
        $this->table = 'omniva_int_order';
        $this->identifier = 'id_order';
    }

    public function ajaxProcessSaveShipment()
    {
        if ($this->access('edit') != '1') {
            throw new PrestaShopException($this->trans('You do not have permission to edit this.', [], 'Admin.Notifications.Error'));
        }

        if (Tools::isSubmit('submitSaveShipment')) {
            $cod = (int) Tools::getValue('cod');
            $insurance = (int) Tools::getValue('insurance');
            $carry_service = (int) Tools::getValue('carry_service');
            $doc_return = (int) Tools::getValue('doc_return');
            $fragile = (int) Tools::getValue('fragile');

            $omnivaOrder = $this->loadObject();
            if($omnivaOrder && Validate::isLoadedObject($omnivaOrder))
            {
                $omnivaOrder->cod = $cod;
                $omnivaOrder->insurance = $insurance;
                $omnivaOrder->carry_service = $carry_service;
                $omnivaOrder->doc_return = $doc_return;
                $omnivaOrder->fragile = $doc_return;
                die($omnivaOrder->update() ? json_encode(['success' => $this->module->l('Omniva order info updated successfully.')]) : 
                                             json_encode(['error' => $this->module->l('Couldn\'t update Omniva order info.')]));
            }
            die(json_encode(['error' => $this->module->l('Couldn\'t load Omniva order info.')]));
        }
    }
}