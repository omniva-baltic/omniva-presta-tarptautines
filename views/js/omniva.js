$(document).on('ready', () => {
  if(typeof(omnivaint_terminal_reference) !== "undefined" && $(`#delivery_option_${omnivaint_terminal_reference}`).is(':checked'))
  {
    loadTerminalMapping();
  }
  if(typeof(omnivaint_terminal_reference) !== "undefined")
  {
    $(`#delivery_option_${omnivaint_terminal_reference}`).on('click', () => {
      if($('.tmjs-container').length == 0)
        loadTerminalMapping();
    });
  }
});
function loadTerminalMapping() {
  let isModalReady = false;
  var tmjs = new TerminalMapping('https://tarptautines.mijora.lt/api/v1');
  tmjs
    .sub('terminal-selected', data => {
      $('input[name="order[receiver_attributes][parcel_machine_id]"]').val(data.id);
      $('#order_receiver_attributes_terminal_address').val(data.name + ", " + data.address);
      $('.receiver_parcel_machine_address_filled').text('');
      $('.receiver_parcel_machine_address_filled').append('<div class="d-inline-flex" style="margin-top: 5px;">' +
        '<img class="my-auto mx-0 me-2" src="https://tarptautines.mijora.lt/default_icon_icon.svg" width="25" height="25">' +
        '<h5 class="my-auto mx-0">' + data.address + ", " + data.zip + ", " + data.city + '</h5></div>' +
        '<br><a class="select_parcel_btn select_parcel_href" data-remote="true" href="#">Pakeisti</a>')
      $('.receiver_parcel_machine_address_filled').show();
      $('.receiver_parcel_machine_address_notfilled').hide();

      tmjs.publish('close-map-modal');
    });

  tmjs_country_code = $('#order_receiver_attributes_country_code').val();
  tmjs_identifier = $('#order_receiver_attributes_service_identifier').val();


  tmjs.setImagesPath('https://tarptautines.mijora.lt/');
  tmjs.init({country_code: 'PL', identifier: 'w2s_inpost', receiver_address: omniva_postcode});

  window['tmjs'] = tmjs;

  tmjs.setTranslation({
    modal_header: 'Paštomatų žemėlapis',
    terminal_list_header: 'Paštomatų sąrašas',
    seach_header: 'Ieškoti',
    search_btn: 'Ieškoti',
    modal_open_btn: 'Pasirinkti paštomatą',
    geolocation_btn: 'Naudoti mano lokaciją',
    your_position: 'Distance calculated from this point',
    nothing_found: 'Rezultatų nerasta',
    no_cities_found: 'Nerastas nei vienas miestas',
    geolocation_not_supported: 'Geolokacija nepalaikoma',

    // Unused strings
    search_placeholder: 'Įrašykite savo pašto kodą/miestą',
    workhours_header: 'Darbo valandos',
    contacts_header: 'Kontaktai',
    select_pickup_point: 'Pasirinkite paštomatą',
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
