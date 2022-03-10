/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    Mijora
 *  @copyright 2013-2022 Mijora
 *  @license   license.txt
 */
$(document).ready(function() {

   if($('#cod_on').is(':checked'))
      $('.cod-amount-block').show();
   else
   $('.cod-amount-block').hide();
   $('#cod_on, #cod_off').on('change', () => {
      if($('#cod_on').is(':checked'))
         $('.cod-amount-block').show();
      else
         $('.cod-amount-block').hide();
   });

    $('#save-shipment').on('click', () => {
        let id_order = $('#id_order').val();
        let cod = +$("#cod_on").is(':checked');
        let cod_amount = $("#cod_amount").val();
        let insurance = +$("#insurance_on").is(':checked');
        let carry_service = +$("#carry_service_on").is(':checked');
        let doc_return = +$("#doc_return_on").is(':checked');
        let fragile = +$("#fragile_on").is(':checked');
    
        $.ajax({
            url: omniva_admin_order_link,
            cache: false,
            data : {
               id: id_order,
               cod: cod,
               cod_amount: cod_amount,
               insurance: insurance,
               carry_service: carry_service,
               doc_return: doc_return,
               fragile: fragile,
               terminal: $('#terminal').val(),
               submitSaveShipment: '1',
               action: 'saveShipment',
               ajax: '1',
            },
            dataType: "json",
            success : function(res,textStatus,jqXHR)
            {
               try
               {
                  if (res.success)
                  {
                     showSuccessMessage(res.success);
                     document.location.reload();
                  }
                  else
                     showErrorMessage(res.error);
               }
               catch(e)
               {
                  jAlert('Technical error');
               }
            }
         });
    });

    $('#send-shipment').on('click', () => {

      let id_order = $('#id_order').val();
      $.ajax({
          url: omniva_admin_order_link,
          cache: false,
          data : {
             id: id_order,
             submitSendShipment: '1',
             action: 'sendShipment',
             ajax: '1',
          },
          dataType: "json",
          success : function(res,textStatus,jqXHR)
          {
             try
             {
                if (res.success)
                {
                  showSuccessMessage(res.success);
                  document.location.reload();
                }
                else
                   showErrorMessage(res.error);
             }
             catch(e)
             {
                jAlert('Technical error');
             }
          }
       });
  });

    $('#cancel-order').on('click', () => {

        let id_order = $('#id_order').val();
        $.ajax({
            url: omniva_admin_order_link,
            cache: false,
            data : {
              id: id_order,
              submitCancelOrder: '1',
              action: 'cancelOrder',
              ajax: '1',
            },
            dataType: "json",
            success : function(res,textStatus,jqXHR)
            {
              try
              {
                 if (res.success)
                 {
                   showSuccessMessage(res.success);
                   document.location.reload();
                 }
                 else
                    showErrorMessage(res.error);
              }
              catch(e)
              {
                 jAlert('Technical error');
              }
            }
        });
    });

    function createParcelField()
    {
        $('.add_nested_fields_link').on('click', e => {
            e.preventDefault();
            let parcelRow = $('.parcel-row').last().clone();
            cleanParcelRow(parcelRow);
            parcelRow.insertAfter($('.parcel-row').last());
            $('.add_nested_fields_link').unbind('click');
            $('.remove_nested_fields_link').unbind('click');
            createParcelField();
            deleteParcelField();
        });
    }

    function deleteParcelField()
    {
        $('.remove_nested_fields_link').on('click', e => {
            e.preventDefault();
            if(!$(e.target).closest('.parcel-row').hasClass('first'))
            {
                $(e.target).closest('.parcel-row').remove();
            }
        });
    }

    function cleanParcelRow(parcelRow)
    {
        parcelRow.removeClass('first');
        parcelRow.find('input').val('');

        // form new parcel row identifier
        if(parcelRow.hasClass('new-parcel'))
        {
            let id = parcelRow.find("[id^='order_items_attributes_x_']").attr('id').split('_').slice(-1)[0];
            parcelRow.data('new-parcel', parseInt(id) + 1);
        }
        else
        {
            parcelRow.data('new-parcel', 1);
            parcelRow.addClass('new-parcel');
        }
        let identifier = parcelRow.data('new-parcel');

        // replace old id's
        parcelRow.find("[id^='order_items_attributes_x_']").attr('id', `order_items_attributes_x_new_${identifier}`);
        parcelRow.find("[id^='order_items_attributes_y_']").attr('id', `order_items_attributes_y_new_${identifier}`);
        parcelRow.find("[id^='order_items_attributes_z_']").attr('id', `order_items_attributes_z_new_${identifier}`);
        parcelRow.find("[id^='order_items_attributes_weight_']").attr('id', `order_items_attributes_weight_new_${identifier}`);

        // replace old input names
        parcelRow.find("[name$='[x]']").attr('name', `parcel[new_${identifier}][x]`);
        parcelRow.find("[name$='[y]']").attr('name', `parcel[new_${identifier}][y]`);
        parcelRow.find("[name$='[z]']").attr('name', `parcel[new_${identifier}][z]`);
        parcelRow.find("[name$='[weight]']").attr('name', `parcel[new_${identifier}][weight]`);
    }

    createParcelField();
    deleteParcelField();
});