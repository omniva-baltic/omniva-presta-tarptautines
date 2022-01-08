<?php

require_once "AdminOmnivaIntBaseController.php";

class AdminOmnivaIntOrderController extends AdminOmnivaIntBaseController
{

    public function __construct()
    {
        parent::__construct();

        $this->list_no_link = true;
        $this->className = 'OmnivaIntOrder';
        $this->_orderBy = 'id_shipment';
        $this->table = 'omniva_int_order';
        $this->identifier = 'id_order';
        $this->tpl_folder = 'override/';

        $this->_select = ' CONCAT(c.firstname, " ", c.lastname) as customer_name,
                            osl.`name` AS `order_state`,
                            (SELECT GROUP_CONCAT(op.tracking_number SEPARATOR ", ") FROM `' . _DB_PREFIX_ .'omniva_int_parcel` op WHERE op.`id_order` = a.`id_shipment` AND op.`tracking_number` != "") as tracking_numbers,
                            om.manifest_number AS manifest_number,
                            om.date_add as manifest_date';

        $this->_join = '
            LEFT JOIN ' . _DB_PREFIX_ . 'orders o ON (o.id_order = a.id_shipment)
            LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON (c.id_customer = o.id_customer)
            LEFT JOIN ' . _DB_PREFIX_ . 'omniva_int_manifest om ON (om.manifest_number = a.cart_id)
            LEFT JOIN ' . _DB_PREFIX_ . 'order_state os ON (o.current_state = os.id_order_state)
            LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id . ')';

        $this->_error = [
            1 => $this->module->l('Could not get manifest data.'),
        ];
        if(Tools::getValue('manifest_error'))
        {
            $this->getErrorWithManifestNumber(Tools::getValue('manifest_error'));
        }
    }

    public function getErrorWithManifestNumber($cart_id)
    {
        $this->_error[2] = $this->module->l(sprintf('Manifest with number %s already exists', $cart_id));

    }

    public function init()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->module->l('Select shop');
        } else {
            $this->orderList();
        }
        parent::init();
    }

    protected function orderList()
    {
        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        $order_states = [];
        foreach ($statuses as $status) {
            $order_states[$status['id_order_state']] = $status['name'];
        }

        $this->fields_list = array(
            'id_shipment' => array(
                'title' => $this->module->l('ID'),
                'align' => 'text-center',
                'filter_key' => 'id_shipment'
            ),
            'customer_name' => array(
                'type' => 'text',
                'title' => $this->module->l('Customer'),
                'align' => 'center',
                'havingFilter' => true,
            ),
            'order_state' => array(
                'title' => $this->module->l('Order Status'),
                'type' => 'select',
                'color' => 'color',
                'list' => $order_states,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname',
            ),
            'date_add' => array(
                'type' => 'datetime',
                'title' => $this->module->l('Order Date'),
                'align' => 'center',
                'filter_key' => 'a!date_add',
            ),
            'service_code' => array(
                'type' => 'text',
                'title' => $this->module->l('Service'),
                'align' => 'center',
            ),
            'tracking_numbers' => array(
                'type' => 'text',
                'title' => $this->module->l('Tracking numbers'),
                'align' => 'center',
                'havingFilter' => true,
            ),
            'manifest_number' => array(
                'type' => 'text',
                'title' => $this->module->l('Manifest ID'),
                'align' => 'center',
                'filter_key' => 'om!manifest_number'
            ),
            'manifest_date' => array(
                'type' => 'datetime',
                'title' => $this->module->l('Manifest date'),
                'align' => 'center',
                'filter_key' => 'om!date_add',
            ),
        );
        $this->identifier = 'id_shipment';
        $this->actions = array('printManifest', 'printLabels');
    }

    public function ajaxProcessSaveShipment()
    {
        if (Tools::isSubmit('submitSaveShipment')) {
            $cod = (int) Tools::getValue('cod');
            $cod_amount = (float) Tools::getValue('cod_amount');
            $insurance = (int) Tools::getValue('insurance');
            $carry_service = (int) Tools::getValue('carry_service');
            $doc_return = (int) Tools::getValue('doc_return');
            $fragile = (int) Tools::getValue('fragile');

            $this->identifier = 'id_order';
            $omnivaOrder = $this->loadObject();
            $order = new Order($omnivaOrder->id);
            if($omnivaOrder && Validate::isLoadedObject($omnivaOrder) && Validate::isLoadedObject($order))
            {
                if(Tools::getValue('terminal'))
                {
                    $id_terminal = (int) Tools::getValue('terminal');
                    $cartTerminal = new OmnivaIntCartTerminal($order->id_cart);
                    if(Validate::isLoadedObject($cartTerminal))
                    {
                        $cartTerminal->id_terminal = $id_terminal;
                        $cartTerminal->update();
                    }
                }
                $omnivaOrder->cod = $cod;
                $omnivaOrder->cod_amount = $cod_amount;
                $omnivaOrder->insurance = $insurance;
                $omnivaOrder->carry_service = $carry_service;
                $omnivaOrder->doc_return = $doc_return;
                $omnivaOrder->fragile = $fragile;
                die($omnivaOrder->update() ? json_encode(['success' => $this->module->l('Omniva order info updated successfully.')]) : 
                                             json_encode(['error' => $this->module->l('Couldn\'t update Omniva order info.')]));
            }
            die(json_encode(['error' => $this->module->l('Couldn\'t load Omniva order info.')]));
        }
    }

    public function ajaxProcessSendShipment()
    {
        $this->identifier = 'id_order';
        if (Tools::isSubmit('submitSendShipment')) {
            $omnivaOrder = $this->loadObject();
            $order = new Order($omnivaOrder->id);
            $cart = new Cart($order->id_cart);
            if($omnivaOrder && Validate::isLoadedObject($omnivaOrder) && Validate::isLoadedObject($order) && Validate::isLoadedObject($cart))
            {
                $entityBuilder = new OmnivaIntEntityBuilder();
                $order = $entityBuilder->buildOrder($order);
                $response = $this->module->api->generateOrder($order);
                $omnivaOrder->setFieldsToUpdate([
                    'shipment_id' => true,
                    'cart_id' => true,
                ]);
                if($response && isset($response->shipment_id, $response->cart_id))
                {
                    $omnivaOrder->shipment_id = $response->shipment_id;
                    $omnivaOrder->cart_id = $response->cart_id;
                    die($omnivaOrder->update() ? json_encode(['success' => $this->module->l('Omniva successfully generated shipment.')]) : 
                    json_encode(['error' => $this->module->l('Couldn\'t update Omniva order.')]));
                }
                else
                {
                    die(json_encode(['error' => $this->module->l('Failed to receive a response from API.')]));
                }
            }
            die(json_encode(['error' => $this->module->l('Couldn\'t load Omniva order info.')]));
        }
    }

    public function initToolbar()
    {
        $this->toolbar_btn['bogus'] = [
            'href' => '#',
            'desc' => $this->module->l('Back to list'),
        ];
    }

    public function processPrintLabels()
    {
        $this->identifier = 'id_order';
        if (Tools::isSubmit('submitPrintLabels') || $this->action == 'printLabels') {
            $omnivaOrder = $this->loadObject();
            $orderTrackingInfo = $this->module->api->getLabel($omnivaOrder->shipment_id);

            if($orderTrackingInfo && isset($orderTrackingInfo->base64pdf))
            {
                $pdf = base64_decode($orderTrackingInfo->base64pdf);

                $pdfFile = tempnam(sys_get_temp_dir(), 'data');
                $file = fopen($pdfFile, 'w');
                fwrite($file, $pdf);
                fclose($file);

                header("Content-Type: application/pdf;");
                header('Content-Transfer-Encoding: binary');
                if(Tools::getValue('downloadLabels'))
                    header('Content-Disposition: attachment; filename=labels_' . $omnivaOrder->shipment_id . '.pdf');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($pdfFile));

                ob_clean();
                flush();
                readfile($pdfFile);

                unlink($pdfFile);
                die(['success' => $this->module->l('Labels printed successfully.')]);
            }
            else
            {
                die(json_encode(['error' => $this->module->l('Failed to get labels from API. Please try again later.')]));
            }
        }
    }

    public function ajaxProcessCancelOrder()
    {
        $this->identifier = 'id_order';
        if (Tools::isSubmit('submitCancelOrder')) {
            $omnivaOrder = $this->loadObject();
            $cancelResponse = $this->module->api->cancelOrder($omnivaOrder->shipment_id);

            if($cancelResponse && $cancelResponse->status == 'deleted')
            {
                $parcels = OmnivaIntParcel::getParcelsByOrderId($omnivaOrder->id);
                $result = true;
                foreach($parcels as $id_parcel)
                {
                    $parcelObj = new OmnivaIntParcel($id_parcel);
                    $parcelObj->tracking_number = '';
                    $parcelObj->setFieldsToUpdate(['tracking_number' => true]);
                    $result &= $parcelObj->update();

                    $omnivaOrder->shipment_id = '';
                    $omnivaOrder->cart_id = '';
                    $result &= $omnivaOrder->update();

                }
                if($result)
                    die(json_encode(['success' => $this->module->l('Shipment cancelled succesfully.')]));
                
            }
            die(json_encode(['error' => $this->module->l('Failed to cancel shipment.')]));
        }
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['latest_manifest'] = [
            'href' => self::$currentIndex . '&latest_manifest=1&token=' . $this->token,
            'desc' => $this->module->l('Generate Latest Manifest'),
            'imgclass' => 'export',
        ];
        parent::initPageHeaderToolbar();
    }

    public function postProcess()
    {
        parent::postProcess();
        if(Tools::getValue('latest_manifest'))
        {
            $this->generateManifest();
        }
    }

    public function generateManifest()
    {
        $manifestInfo = $this->module->api->generateManifestLatest();
        if($manifestInfo && $manifestInfo->cart_id && $manifestInfo->manifest)
        {
            if(OmnivaIntManifest::getManifestByNumber($manifestInfo->cart_id))
            {
                $this->redirect_after = self::$currentIndex . '&error=2&token=' . $this->token . '&manifest_error=' . $manifestInfo->cart_id;
                return;
            }
            $omnivaManifest = new OmnivaIntManifest();
            $omnivaManifest->id_shop = $this->context->shop->id;
            $omnivaManifest->manifest_number = $manifestInfo->cart_id;
            if($omnivaManifest->add())
            {
                $this->redirect_after = self::$currentIndex . '&conf=4&token=' . $this->token;
                return;
            }
        }
        $this->redirect_after = self::$currentIndex . '&error=1&token=' . $this->token;
    }

    public function displayPrintManifestLink($token, $id, $name = null)
    {
        $omnivaOrder = new OmnivaIntOrder($id); 
        $manifestExists = OmnivaIntManifest::checkManifestExists($omnivaOrder->cart_id);
        if(!$manifestExists)
            return false;
        if (!array_key_exists('Print Manifest', self::$cache_lang)) {
            self::$cache_lang['Print Manifest'] = $this->module->l('Print Manifest');
        }
        $this->context->smarty->assign(array(
            'href' => self::$currentIndex . '&action=printManifest&token=' . $this->token . '&id_order=' . $id,
            'action' => $this->module->l('Print Manifest'),
            'id' => $id,
            'blank' => 'true',
            'icon' => 'print'
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/list_action.tpl');
    }

    public function displayPrintLabelsLink($token, $id, $name = null)
    {
        $omnivaOrder = new OmnivaIntOrder($id); 
        $untrackedParcelsCount = OmnivaIntParcel::getCountUntrackedParcelsByOrderId($id);

        if($untrackedParcelsCount > 0)
            return false;
        if (!array_key_exists('Print Labels', self::$cache_lang)) {
            self::$cache_lang['Print Labels'] = $this->module->l('Print Labels');
        }
        $this->context->smarty->assign(array(
            'href' => self::$currentIndex . '&action=printLabels&token=' . $this->token . '&id_order=' . $id,
            'action' => $this->module->l('Print Labels'),
            'id' => $id,
            'blank' => 'true',
            'icon' => 'print'
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/list_action.tpl');
    }

    public function processPrintManifest()
    {
        $this->identifier = 'id_order';
        $this->loadObject();
        $manifestExists = OmnivaIntManifest::checkManifestExists($this->object->cart_id);
        if(!$manifestExists)
            Tools::redirectAdmin(self::$currentIndex . '&error=1&token=' . $this->token);

        $manifestInfo = $this->module->api->generateManifest($this->object->cart_id);
        if($manifestInfo && $manifestInfo->cart_id && $manifestInfo->manifest)
        {
            $pdf = base64_decode($manifestInfo->manifest);

            $pdfFile = tempnam(sys_get_temp_dir(), 'data');
            $file = fopen($pdfFile, 'w');
            fwrite($file, $pdf);
            fclose($file);

            header("Content-Type: application/pdf; name=manifest_" . $this->object->cart_id . ".pdf");
            header("Content-Type: application/pdf;");
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($pdfFile));

            ob_clean();
            flush();
            readfile($pdfFile);
            unlink($pdfFile);

        }
    }
}