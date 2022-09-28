<?php

require_once "AdminOmnivaIntBaseController.php";

use OmnivaApi\Exception\OmnivaApiException;

class AdminOmnivaIntOrderController extends AdminOmnivaIntBaseController
{

    public function __construct()
    {
        parent::__construct();

        $this->list_no_link = true;
        $this->title_icon = 'icon-items';
        $this->className = 'OmnivaIntOrder';
        $this->_orderBy = 'id';
        $this->table = 'omniva_int_order';
        $this->identifier = 'id';
        $this->tpl_folder = 'override/';

        $this->_select = ' CONCAT(c.firstname, " ", c.lastname) as customer_name,
                            osl.`name` AS `order_state`,
                            (SELECT GROUP_CONCAT(op.tracking_number SEPARATOR ", ") 
                                FROM `' . _DB_PREFIX_ .'omniva_int_parcel` op 
                                WHERE op.`id_order` = a.`id` AND op.`tracking_number` != "") as tracking_numbers,
                            om.manifest_number AS manifest_number,
                            om.date_add as manifest_date';

        $this->_join = '
            LEFT JOIN ' . _DB_PREFIX_ . 'orders o ON (o.id_order = a.id)
            LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON (c.id_customer = o.id_customer)
            LEFT JOIN ' . _DB_PREFIX_ . 'omniva_int_manifest om ON (om.manifest_number = a.cart_id)
            LEFT JOIN ' . _DB_PREFIX_ . 'order_state os ON (o.current_state = os.id_order_state)
            LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id . ')';

        if(Tools::getValue('manifest_error'))
        {
            $this->getErrorWithManifestNumber(Tools::getValue('manifest_error'));
        }
    }

    public function getErrorWithManifestNumber($cart_id)
    {        
        // Invoking getModuleTranslation directly here to get access to sprintf params.
        $this->_error[1] = Translate::getModuleTranslation($this->module, 'Could not get manifest data for manifest %s. Please check that all your orders have tracking numbers and try again later.', $this->module->name, [$cart_id]);
        $this->_error[2] = Translate::getModuleTranslation($this->module, 'Manifest with number %s already exists', $this->module->name, [$cart_id]);
        $this->_error[3] = $this->module->l('This operation requires API token. Please check your settings.');
    }

    public function init()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->module->l('Select shop');
        } else {
            $this->refreshLabelStatus();
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

        $this->fields_list = [
            'id' => [
                'title' => $this->module->l('ID'),
                'align' => 'text-center',
                'filter_key' => 'id'
            ],
            'customer_name' => [
                'type' => 'text',
                'title' => $this->module->l('Customer'),
                'align' => 'center',
                'havingFilter' => true,
            ],
            'order_state' => [
                'title' => $this->module->l('Order Status'),
                'type' => 'select',
                'color' => 'color',
                'list' => $order_states,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname',
            ],
            'date_add' => [
                'type' => 'datetime',
                'title' => $this->module->l('Order Date'),
                'align' => 'center',
                'filter_key' => 'a!date_add',
            ],
            'service_code' => [
                'type' => 'text',
                'title' => $this->module->l('Service'),
                'align' => 'center',
            ],
            'tracking_numbers' => [
                'type' => 'text',
                'title' => $this->module->l('Tracking numbers'),
                'align' => 'center',
                'havingFilter' => true,
            ],
            'manifest_number' => [
                'type' => 'text',
                'title' => $this->module->l('Manifest ID'),
                'align' => 'center',
                'filter_key' => 'om!manifest_number'
            ],
            'manifest_date' => [
                'type' => 'datetime',
                'title' => $this->module->l('Manifest date'),
                'align' => 'center',
                'filter_key' => 'om!date_add',
            ],
        ];
        $this->actions = ['printManifest', 'printLabels', 'generateManifest'];
        $this->bulk_actions = [
            'generateLabels' => array(
                'text' => $this->module->l('Generate Labels'),
                'icon' => 'icon-save'
            ),
        ];
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

    public function processUpdateParcels()
    {
        $parcels = Tools::getValue('parcel');
        $omnivaOrder = $this->loadObject();
        $this->redirect_after = $_SERVER['HTTP_REFERER'];

        // Check if parcel was removed (does not exist in array of current parcels)
        // But get them here, or else it will delete the new ones
        $parcelIds = array_map(function ($parcel) {
            return $parcel['id'];
        }, OmnivaIntParcel::getParcelsByOrderId($omnivaOrder->id));

        $oldParcelsSubmitted = [];
        foreach($parcels as $key => $parcel)
        {
            if(strpos($key, 'new') !== false)
            {
                $omnivaParcel = new OmnivaIntParcel();
                $omnivaParcel->id_order = $omnivaOrder->id;
                $omnivaParcel->amount = 1;
                $omnivaParcel->width = (float) $parcel['y'];
                $omnivaParcel->length = (float) $parcel['x'];
                $omnivaParcel->height = (float) $parcel['z'];
                $omnivaParcel->weight = (float) $parcel['weight'];
                $omnivaParcel->add();
            }
            else
            {
                $omnivaParcel = new OmnivaIntParcel((int) $key);

                if(Validate::isLoadedObject($omnivaParcel))
                {
                    $oldParcelsSubmitted[] = $key;
                    $omnivaParcel->width = (float) $parcel['y'];
                    $omnivaParcel->length = (float) $parcel['x'];
                    $omnivaParcel->height = (float) $parcel['z'];
                    $omnivaParcel->weight = (float) $parcel['weight'];
                    $omnivaParcel->update();
                }
            }
        }

        $deletedParcels = array_diff($parcelIds, $oldParcelsSubmitted);
        if(!empty($deletedParcels))
        {
            foreach ($deletedParcels as $id_parcel)
            {
                $omnivaParcel = new OmnivaIntParcel($id_parcel);
                $omnivaParcel->delete();
            }
        }
    }

    public function ajaxProcessSendShipment()
    {
        if(!Configuration::get('OMNIVA_TOKEN'))
            die(json_encode(['error' => $this->module->l('This operation requires API token. Please check your settings.')]));
        if (Tools::isSubmit('submitSendShipment')) {
            $omnivaOrder = $this->loadObject();
            $order = new Order($omnivaOrder->id);
            $cart = new Cart($order->id_cart);
            if($omnivaOrder && Validate::isLoadedObject($omnivaOrder) && Validate::isLoadedObject($order) && Validate::isLoadedObject($cart))
            {
                $entityBuilder = new OmnivaIntEntityBuilder($this->api);
                $order = $entityBuilder->buildOrder($order);

                if(!$order)
                    die(json_encode(['error' => $this->module->l('Could not build order.')]));
                try {
                    $response = $this->api->generateOrder($order);
                }
                catch (OmnivaApiException $e)
                {
                    die(json_encode(['error' => $e->getMessage()]));
                }
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
        if(!Configuration::get('OMNIVA_TOKEN'))
            die(json_encode(['error' => $this->module->l('This operation requires API token. Please check your settings.')]));
        if (Tools::isSubmit('submitPrintLabels') || $this->action == 'printLabels') {
            $omnivaOrder = $this->loadObject();
            try {
                $orderTrackingInfo = $this->api->getLabel($omnivaOrder->shipment_id);
            }
            catch(Exception $e)
            {
                $this->errors[] = $this->module->l('Label is not ready yet. Please, check back again later.');
                return false;
            }

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
        if(!Configuration::get('OMNIVA_TOKEN'))
            die(json_encode(['error' => $this->module->l('This operation requires API token. Please check your settings.')]));
        if (Tools::isSubmit('submitCancelOrder')) {
            $omnivaOrder = $this->loadObject();
            try {
                $cancelResponse = $this->api->cancelOrder($omnivaOrder->shipment_id);
            }
            catch (OmnivaApiException $e)
            {
                die(json_encode(['error' => $e->getMessage()]));
            }
            if($cancelResponse && $cancelResponse->status == 'deleted')
            {
                $parcels = OmnivaIntParcel::getParcelsByOrderId($omnivaOrder->id);
                $result = true;
                foreach($parcels as $parcel)
                {
                    $parcelObj = new OmnivaIntParcel($parcel['id']);
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
        if(Configuration::get('OMNIVA_TOKEN'))
        {
            $this->page_header_toolbar_btn['latest_manifest'] = [
                'href' => self::$currentIndex . '&latest_manifest=1&token=' . $this->token,
                'desc' => $this->module->l('Generate Latest Manifest'),
                'imgclass' => 'export',
            ];
        }
        parent::initPageHeaderToolbar();
    }

    public function postProcess()
    {
        parent::postProcess();
        $error = Tools::getValue('error');
        if(version_compare(_PS_VERSION_, '1.7', '<') && $error && isset($this->_error[$error]))
        {
            $this->errors[] = $this->_error[$error];
        }
        if(Tools::getValue('latest_manifest') || Tools::getValue('id_manifest'))
        {
            $this->generateManifest();
        }
        if(Tools::isSubmit('submitBulkgenerateLabelsomniva_int_order'))
        {
            $this->bulkSendShipments();
        }
    }

    private function bulkSendShipments()
    {
        $order_ids = Tools::getValue('omniva_int_orderBox');
        if(empty($order_ids))
        {
            $this->errors[] = $this->module->l('No order ID\'s were provided.');
            return false;
        }

        foreach($order_ids as $id_order)
        {
            $omnivaOrder = new OmnivaIntOrder($id_order);
            $order = new Order($omnivaOrder->id);
            $cart = new Cart($order->id_cart);
            if($omnivaOrder && Validate::isLoadedObject($omnivaOrder) && Validate::isLoadedObject($order) && Validate::isLoadedObject($cart))
            {
                $entityBuilder = new OmnivaIntEntityBuilder();
                $order = $entityBuilder->buildOrder($order);
                
                if(!$order)
                {
                    $this->errors[] = Translate::getModuleTranslation($this->module, 'Could not build request for order %s.', $this->module->name, [$id_order]);
                    continue;
                }

                try {
                    $response = $this->api->generateOrder($order);
                }
                catch (OmnivaApiException $e)
                {
                    $this->errors[] = $e->getMessage();
                    continue;
                }
                $omnivaOrder->setFieldsToUpdate([
                    'shipment_id' => true,
                    'cart_id' => true,
                ]);
                if($response && isset($response->shipment_id, $response->cart_id))
                {
                    $omnivaOrder->shipment_id = $response->shipment_id;
                    $omnivaOrder->cart_id = $response->cart_id;
                    if(!$omnivaOrder->update())
                    {
                        $this->errors[] = $this->module->l('Couldn\'t update Omniva order.');
                    }
                }
                else
                {
                    $this->errors[] = $this->module->l('Failed to receive a response from API.');
                    return false;
                }
            }
        }
        if(empty($this->errors))
            $this->confirmations[] = $this->module->l('Successfully sent shipment data for selected orders');
    }

    public function generateManifest()
    {
        if(!Configuration::get('OMNIVA_TOKEN'))
        {
            $this->redirect_after = self::$currentIndex . '&error=3&token=' . $this->token;
            return;
        }
        $id_manifest = Tools::getValue('id_manifest');
        try {
            if($id_manifest)
                $manifestInfo = $this->api->generateManifest($id_manifest);
             else
                $manifestInfo = $this->api->generateManifestLatest();
        }
        catch (OmnivaApiException $e)
        {
            $this->errors[] = $e->getMessage();
            return false;
        }
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
        $this->redirect_after = self::$currentIndex . '&error=1&token=' . $this->token . '&manifest_error=' . $manifestInfo->cart_id;
    }

    public function displayPrintManifestLink($token, $id, $name = null)
    {
        $omnivaOrder = new OmnivaIntOrder($id); 
        $manifestExists = OmnivaIntManifest::checkManifestExists($omnivaOrder->cart_id);
        if(!$manifestExists || !Configuration::get('OMNIVA_TOKEN'))
            return false;
        if (!array_key_exists('Print Manifest', self::$cache_lang)) {
            self::$cache_lang['Print Manifest'] = $this->module->l('Print Manifest');
        }
        $this->context->smarty->assign([
            'href' => self::$currentIndex . '&action=printManifest&token=' . $this->token . '&id=' . $id,
            'action' => $this->module->l('Print Manifest'),
            'id' => $id,
            'blank' => 'true',
            'icon' => 'print'
        ]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/list_action.tpl');
    }

    public function displayPrintLabelsLink($token, $id, $name = null)
    {
        $untrackedParcelsCount = OmnivaIntParcel::getCountUntrackedParcelsByOrderId($id);

        if($untrackedParcelsCount > 0 || !Configuration::get('OMNIVA_TOKEN'))
            return false;
        if (!array_key_exists('Print Labels', self::$cache_lang)) {
            self::$cache_lang['Print Labels'] = $this->module->l('Print Labels');
        }
        $this->context->smarty->assign([
            'href' => self::$currentIndex . '&action=printLabels&token=' . $this->token . '&id=' . $id,
            'action' => $this->module->l('Print Labels'),
            'id' => $id,
            'blank' => true,
            'icon' => 'print'
        ]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/list_action.tpl');
    }

    public function displayGenerateManifestLink($token, $id, $name = null)
    {
        $omnivaOrder = new OmnivaIntOrder($id);

        $manifestExists = OmnivaIntManifest::checkManifestExists($omnivaOrder->cart_id);
        if($manifestExists || !$omnivaOrder->cart_id || !Configuration::get('OMNIVA_TOKEN'))
            return false;
        if (!array_key_exists('Generate Manifest', self::$cache_lang)) {
            self::$cache_lang['Generate Manifest'] = $this->module->l('Generate Manifest');
        }
        $this->context->smarty->assign([
            'href' => self::$currentIndex . '&action=generateManifest&token=' . $this->token . '&id_manifest=' . $omnivaOrder->cart_id,
            'action' => $this->module->l('Generate Manifest'),
            'id' => $id,
            'blank' => false,
            'icon' => 'list'
        ]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/list_action.tpl');
    }

    public function processPrintManifest()
    {
        if(!Configuration::get('OMNIVA_TOKEN'))
        {
            $this->redirect_after = self::$currentIndex . '&error=3&token=' . $this->token;
            return;
        }
        $this->loadObject();
        $manifestExists = OmnivaIntManifest::checkManifestExists($this->object->cart_id);
        if(!$manifestExists)
            Tools::redirectAdmin(self::$currentIndex . '&error=1&token=' . $this->token);


        try {
            $manifestInfo = $this->api->generateManifest($this->object->cart_id);
        }
        catch (OmnivaApiException $e)
        {
            $this->errors[] = $e->getMessage();
            return false;
        }
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
        else
        {
            Tools::redirectAdmin(self::$currentIndex . '&error=1&token=' . $this->token);
        }
    }

    private function refreshLabelStatus()
    {
        $ordersWithNoManifest = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('oo.id')
                ->from('omniva_int_order', 'oo')
                ->leftJoin('omniva_int_manifest', 'om', 'om.manifest_number = oo.cart_id')
                ->where('om.manifest_number IS NULL AND oo.shipment_id != ""')
        );
        if(!empty($ordersWithNoManifest))
        {
            foreach($ordersWithNoManifest as $order)
            {
                if(isset($order['id']))
                {
                    $id_order = (int) $order['id'];
                    $omnivaOrder = new OmnivaIntOrder($id_order);
                    $omnivaOrderParcels = OmnivaIntParcel::getParcelsByOrderId($id_order);
    
                    $untrackedParcelsCount = OmnivaIntParcel::getCountUntrackedParcelsByOrderId($id_order);
                    if($omnivaOrder->shipment_id && $untrackedParcelsCount > 0 && Configuration::get('OMNIVA_TOKEN'))
                    {
                        // Just catch the exception, because it is thrown, if order is not yet ready, i.e gives error "Your order is being generated, please try again later"
                        try {
                            $api = $this->module->helper->getApi();
                            $orderTrackingInfo = $api->getLabel($omnivaOrder->shipment_id);
                        
                            if($orderTrackingInfo && isset($orderTrackingInfo->tracking_numbers))
                            {
                                $this->module->changeOrderStatus($id_order, Configuration::get(OmnivaInternational::$_order_states['order_state_ready']['key']));
                                foreach($omnivaOrderParcels as $key => $parcel)
                                {
                                    $omnivaParcel = new OmnivaIntParcel($parcel['id']);
                                    $omnivaParcel->setFieldsToUpdate(['tracking_number' => true]);
                                    $omnivaParcel->tracking_number = $orderTrackingInfo->tracking_numbers[$key];
                                    $omnivaParcel->update();
                                }
                            }
                            else
                            {
                                $this->module->changeOrderStatus($id_order, Configuration::get(OmnivaInternational::$_order_states['order_state_error']['key']));
                            }
                            // for debugging
                        } catch (Exception $e) {
                            sleep(0);
                        }
                    }
                }
            }
        }
    }
}