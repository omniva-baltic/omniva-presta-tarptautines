$(document).ready(function() {

    $('#save-shipment').on('click', () => {
        let id_order = $('#id_order').val();
        let cod = +$("#cod_on").is(':checked');
        let insurance = +$("#insurance_on").is(':checked');
        let carry_service = +$("#carry_service_on").is(':checked');
        let doc_return = +$("#doc_return_on").is(':checked');
        let fragile = +$("#fragile_on").is(':checked');
    
        $.ajax({
            url: omniva_admin_order_link,
            cache: false,
            data : {
               id_order: id_order,
               cod: cod,
               insurance: insurance,
               carry_service: carry_service,
               doc_return: doc_return,
               fragile: fragile,
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
                     showSuccessMessage(res.success);
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
             id_order: id_order,
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
                   showSuccessMessage(res.success);
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
});