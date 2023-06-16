var omnivaInt_carriers = [];
var omnivaInt_current_carrier = '';

$(document).on('ready', () => {
  omnivaInt_carriers = getOmnivaIntCarriersData();

  for (let i = 0; i < omnivaInt_carriers.length; i++) {
    if ($('#delivery_option_' + omnivaInt_carriers[i].reference).is(':checked')) {
      omnivaInt_current_carrier = omnivaInt_carriers[i].reference;
      loadOmnivaIntMapping();
    }
    $(document).on('click', '#delivery_option_' + omnivaInt_carriers[i].reference, (e) => {
      omnivaInt_current_carrier = e.target.id.replace('delivery_option_', '');
      if($('.tmjs-container').length) {
        removeOmnivaIntMap();
      }
      loadOmnivaIntMapping();
    });
  }
});

function getOmnivaIntCarriersData() {
  let carriers = [];
  let carriers_data = document.getElementsByClassName('omnivaInt_carrier');

  for (let i = 0; i < carriers_data.length; i++) {
    if (!carriers_data[i].hasAttribute('data-reference') || !isInt(carriers_data[i].getAttribute('data-reference'))) {
      continue;
    }
    let carrier = {
      reference : carriers_data[i].getAttribute('data-reference'),
      type : (carriers_data[i].hasAttribute('data-type')) ? carriers_data[i].getAttribute('data-type') : '',
      terminal_type : (carriers_data[i].hasAttribute('data-terminal_type')) ? carriers_data[i].getAttribute('data-terminal_type') : '',
      terminal_radius : (carriers_data[i].hasAttribute('data-terminal_radius')) ? carriers_data[i].getAttribute('data-terminal_radius') : ''
    };
    carriers.push(carrier);
  }

  return carriers;
}

function getOmnivaIntSingleCarrierData(carrier_reference) {
  if (!isInt(carrier_reference)) {
    return false;
  }
  if (omnivaInt_carriers === "undefined" || !omnivaInt_carriers.length) {
    return false;
  }

  for (let i = 0; i < omnivaInt_carriers.length; i++) {
    if (omnivaInt_carriers[i].reference == carrier_reference) {
      return omnivaInt_carriers[i];
    }
  }

  return false;
}

function isInt(value) {
  if (isNaN(value)) {
    return false;
  }
  var x = parseFloat(value);
  return (x | 0) === x;
}

function loadOmnivaIntMapping() {
  let carrier_data = getOmnivaIntSingleCarrierData(omnivaInt_current_carrier);
  if (!carrier_data) return;

  let isModalReady = false;
  var tmjs = new OmnivaIntMapping(omnivaInt_endpoint);
  tmjs
    .sub('terminal-selected', data => {
      $('input[name="order[receiver_attributes][parcel_machine_id]"]').val(data.id);
      $('#order_receiver_attributes_terminal_address').val(data.name + ", " + data.address);
      $('.receiver_parcel_machine_address_filled').text('');
      $('.receiver_parcel_machine_address_filled').append('<div class="d-inline-flex" style="margin-top: 5px;">' +
        '<img class="my-auto mx-0 me-2" src="https://tarptautines.omniva.lt/default_icon_icon.svg" width="25" height="25">' +
        '<h5 class="my-auto mx-0">' + data.address + ", " + data.zip + ", " + data.city + '</h5></div>' +
        '<br><a class="select_parcel_btn select_parcel_href" data-remote="true" href="#">Pakeisti</a>')
      $('.receiver_parcel_machine_address_filled').show();
      $('.receiver_parcel_machine_address_notfilled').hide();

      tmjs.publish('close-map-modal');
    });

  tmjs_country_code = $('#order_receiver_attributes_country_code').val();
  tmjs_identifier = $('#order_receiver_attributes_service_identifier').val();


  tmjs.setImagesPath('https://tarptautines.omniva.lt/');
  tmjs.init({country_code: omnivaInt_current_country, identifier: carrier_data.terminal_type, receiver_address: omnivaInt_postcode});

  window['tmjs'] = tmjs;

  tmjs.setTranslation({
    modal_header: modal_header,
    terminal_list_header: terminal_list_header,
    seach_header: seach_header,
    search_btn: search_btn,
    modal_open_btn: modal_open_btn,
    geolocation_btn: geolocation_btn,
    your_position: your_position,
    nothing_found: nothing_found,
    no_cities_found: no_cities_found,
    geolocation_not_supported: geolocation_not_supported,

    // Unused strings
    search_placeholder: 'Įrašykite savo pašto kodą/miestą',
    workhours_header: 'Darbo valandos',
    contacts_header: 'Kontaktai',
    no_pickup_points: 'Paštomatas nepasirinktas',
    select_btn: 'Pasirinkite',
    back_to_list_btn: 'Atstatyti paiešką',
    no_information: 'Nėra informacijos'
  })

  tmjs.sub('tmjs-ready', function(t) {
    t.map.ZOOM_SELECTED = 8;
    isModalReady = true;
    $('.spinner-border').hide();
    $('.select_parcel_btn').removeClass('disabled').html('Pasirinkti paštomatą');
  });

  $(document).on('click', '.select_parcel_btn', function(e) {
    e.preventDefault();
    if (!isModalReady) {
      return;
    }
    tmjs.publish('open-map-modal');
    coords = {lng: $('.receiver_coords').attr('value-x'), lat: $('.receiver_coords').attr('value-y')};
    if (coords != undefined) {
      tmjs.map.addReferencePosition(coords);
      tmjs.dom.renderTerminalList(tmjs.map.addDistance(coords), true)
    }
  })
}

function removeOmnivaIntMap() {
  if (typeof tmjs !== 'undefined') {
    document.getElementById(tmjs.containerId).remove();
    document.getElementById(tmjs.containerId + "_modal").remove();
    window['tmjs'] = null;
  }
}
