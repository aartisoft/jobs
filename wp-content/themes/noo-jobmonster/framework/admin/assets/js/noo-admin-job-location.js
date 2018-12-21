jQuery(document).ready(function ($) {

    var lat = $('#map-lat').val();
    var lon = $('#map-lon').val();

    var input_address = $('.term-name-wrap #name');

    if ($('.term-name-wrap #tag-name').length) {
        input_address = $('.term-name-wrap #tag-name');
    }

    $('#jm_location_term_map').locationpicker({
        location: {
            latitude: lat,
            longitude: lon,
        },
        radius: 0,
        inputBinding: {
            latitudeInput: $('#map-lat'),
            longitudeInput: $('#map-lon'),
            locationNameInput: input_address
        },
        enableAutocomplete: true,
        enableAutocompleteBlur: true,
    });

    function noo_mb_map_field() {
        var field = $('.noo-mb-job-location');
        var lat = field.data('lat');
        var lon = field.data('lon');

        field.locationpicker({
            location: {
                latitude: lat,
                longitude: lon,
            },
            radius: 0,
            zoom: 18,
            inputBinding: {
                latitudeInput: $('.noo-mb-lat'),
                longitudeInput: $('.noo-mb-lon'),
                locationNameInput: $('.noo-mb-location-address')
            },
            enableAutocomplete: true,
            enableAutocompleteBlur: true,
        });
    }

    noo_mb_map_field();

});

