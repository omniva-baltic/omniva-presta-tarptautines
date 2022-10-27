<?php

namespace OmnivaInt\Controller\Admin;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use OmnivaApi\Exception\OmnivaApiException;
use setasign\Fpdi\Fpdi;


class OmnivaIntBulkActionController extends FrameworkBundleAdminController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function bulkGenerateLabels(Request $request)
    {
        $orders = $request->request->get('order_orders_bulk');
        if(empty($orders))
        {
            $this->flashErrors(["No order ID\'s were provided."]);
            return $this->redirectToRoute('admin_orders_index');
        }
        $moduleRepository = $this->get('prestashop.core.admin.module.repository');
        $module = $moduleRepository->getModule('omnivainternational');
        $module_legacy = $module->getInstance();

        $api = $module_legacy->helper->getApi();
        $errors = [];
        foreach($orders as $id_order)
        {
            $omnivaOrder = new \OmnivaIntOrder($id_order);
            $order = new \Order($omnivaOrder->id);
            $cart = new \Cart($order->id_cart);
            if($omnivaOrder && \Validate::isLoadedObject($omnivaOrder) && \Validate::isLoadedObject($order) && \Validate::isLoadedObject($cart))
            {
                $entityBuilder = new \OmnivaIntEntityBuilder($module_legacy);
                $order = $entityBuilder->buildOrder($order);
                
                if(!$order)
                {
                    $errors[] = \Translate::getModuleTranslation($module_legacy, 'Could not build request for order %s.', $module_legacy->name, [$id_order]);
                    continue;
                }

                try {
                    $response = $api->generateOrder($order);
                }
                catch (OmnivaApiException $e)
                {
                    $message = $e->getMessage() . ". Order: #" . $id_order;
                    $errors[] = \Translate::getModuleTranslation($module_legacy, $message, $module_legacy->displayName);
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
                        $errors[] = \Translate::getModuleTranslation($module_legacy, 'Couldn\'t update Omniva order: #%s.', $module_legacy->name, [$id_order]);
                    }
                    else
                    {
                        $this->addFlash('success', \Translate::getModuleTranslation($module_legacy, 'Successfully sent shipment for order #%s.', $module_legacy->name, [$id_order]));
                    }
                }
                else
                {
                    $errors[] = \Translate::getModuleTranslation($module_legacy, 'Failed to receive a response from API. Order: #%s.', $module_legacy->name, [$id_order]);
                }
            }
            else
            {
                $errors[] = \Translate::getModuleTranslation($module_legacy, 'Can generate shipment for Order: #%s. Not Omniva International order.', $module_legacy->name, [$id_order]);
            }
        }
        if(empty($errors))
            $this->addFlash('success', $module_legacy->l('Successfully sent shipment data for all selected orders'));
        else
            $this->flashErrors($errors);

        return $this->redirectToRoute('admin_orders_index');
    }


    /**
     * @param Request $request
     *
     * @return Response
     */
    public function bulkPrintLabels(Request $request)
    {
        $orders = $request->request->get('order_orders_bulk');
        if(empty($orders))
        {
            $this->flashErrors(["No order ID\'s were provided."]);
            return $this->redirectToRoute('admin_orders_index');
        }
        $moduleRepository = $this->get('prestashop.core.admin.module.repository');
        $module = $moduleRepository->getModule('omnivainternational');
        $module_legacy = $module->getInstance();

        $api = $module_legacy->helper->getApi();
        $pdfs = [];

        $errors = [];
        foreach($orders as $id_order)
        {
            $omnivaOrder = new \OmnivaIntOrder($id_order);
            if(!\Validate::isLoadedObject($omnivaOrder))
            {
                $errors[] = \Translate::getModuleTranslation($module_legacy, 'Can generate shipment for Order: #%s. Not Omniva International order.', $module_legacy->name, [$id_order]);
                continue;
            }

            try {
                $orderTrackingInfo = $api->getLabel($omnivaOrder->shipment_id);
            }
            catch(\Exception $e)
            {
                $errors[] = \Translate::getModuleTranslation($module_legacy, 'Order #%s. Got exception %s.', $module_legacy->name, [$id_order, $e->getMessage()]);
                continue;
            }
    
            if($orderTrackingInfo && isset($orderTrackingInfo->base64pdf))
            {
                $pdf = $orderTrackingInfo->base64pdf;
                $pdfs[] = $pdf;
            }
            else
            {
                $errors[] = \Translate::getModuleTranslation($module_legacy, 'Failed to get labels from API for order #%s. Please try again later.', $module_legacy->name, [$id_order]);
            }
        }

        if(!empty($pdfs))
        {
            $this->mergePdf($pdfs);
        }
        else
        {
            $this->flashErrors([\Translate::getModuleTranslation($module_legacy, 'Failed to get labels from API for all selected orders. Please try again later.', $module_legacy->name)]);
        }
        $this->flashErrors($errors);
        return $this->redirectToRoute('admin_orders_index');
    }

    private function mergePdf($pdfs) {
        $pageCount = 0;
        // initiate FPDI
        $pdf = new Fpdi();

        foreach ($pdfs as $data) {
            $name = tempnam("/tmp", "tmppdf");
            $handle = fopen($name, "w");
            fwrite($handle, base64_decode($data));
            fclose($handle);

            $pageCount = $pdf->setSourceFile($name);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                
                $pdf->AddPage('P');
                
                $pdf->useTemplate($templateId, ['adjustPageSize' => true]);
            }
        }
        $pdf->Output('I');
    }
}