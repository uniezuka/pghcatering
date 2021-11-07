(function( $ ) {
	'use strict';

    $(document).ready(function() {
        $.each($('.custom-add-to-cart'), function (index, value) {
            $(this).click(function (event) {
                event.preventDefault();
                if ($(this).hasClass('disabled')) {
                    return false; // Do something else in here if required
                }
                $('.custom-add-to-cart').addClass('disabled');
                $('.custom-add-to-cart').css('color', '#ccc');

                var product_type = $(this).data('type');       // product type
                var take_action = $(this).data('function');   // add or remove
                var product_id = $(this).data('product');    // product_id
                var variation_id = $(this).data('variationid');// variation_id
                var menu_day = $(this).data('menu_day');// menu_day
                var variation = $(this).data('variation');  // variation array

                var currentQty = 0;
                var quantity = (take_action == 'add') ? 1 : -1;
                
                if ($('#atc-qty-' + variation_id).text() !== '') {
                    currentQty = parseInt($('#atc-qty-' + variation_id).text());
                }

                var id = (product_type == 'simple') ? product_id : variation_id;
                
                $.ajax({
                    type: 'POST',
                    //url: config_custom.wc_add_to_cart_url,
                    url: config_custom.ajax_url,
                    dataType: 'json',
                    // headers: {
                    //     'X-WC-Store-API-Nonce': config_custom.wc_store_api
                    // },
                    // data: {
                    //     id : id,
                    //     quantity: quantity,
                    //     menu_day: menu_day
                    // },
                    data: {
                        'action': 'woocommerce_add_to_cart',
                        'product_id': id,
                        'menu_day': menu_day,
                        'quantity': quantity
                    },
                    success: function (response) {
                        $('.custom-add-to-cart').removeClass('disabled');
                        $('.custom-add-to-cart').css('color', '#EE6948');

                        if (take_action === 'add') {
                            currentQty++;
                        }
                        else {
                            currentQty--;

                            if (currentQty <= 0) currentQty = 0;       
                        }

                        if (currentQty !== 0) {
                            $('#qty-in-cart-' + product_id).text(currentQty);
                        }

                        if (currentQty < 1) {
                            $('#overlay-' + product_id).addClass(' not-in-cart');
                            $('#atc-label-text-' + product_id).text('Add To Cart ');
                        } else {
                            $('#overlay-' + product_id).removeClass('not-in-cart');
                            $('#atc-label-text-' + product_id).text('Added To Cart ');
                        }

                        $('#atc-qty-' + variation_id).text(currentQty);

                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus);
                    }
                });
            });
        });

        $.each($('.nf-popup'), function (index, value) {
            var product_id = $(this).attr('id');
            product_id = product_id.split('-');
            product_id = product_id[2];

            if ($('#modal-' + product_id).length > 0) {
                $('#nf-popup-' + product_id).removeClass('hidden-by-default');
            }

            $(this).click(function (event) {
                event.preventDefault();
                $('#modal-' + product_id).toggleClass('show-modal');
                $('body').addClass('noScroll');
            });

            $('#close-button-' + product_id).click(function (event) {
                event.preventDefault();
                $('body').removeClass('noScroll');
                $('#modal-' + product_id).removeClass('show-modal');
            });

            $('#modal-' + product_id).click(function (event) {
                event.preventDefault();
                $('#modal-' + product_id).removeClass('show-modal');
                $('body').removeClass('noScroll');
            });

        });

        $.each($('.title-popup-link'), function (index, value) {
            var product_id = $(this).attr('id');
            product_id = product_id.split('-');
            product_id = product_id[2];
    
            $(this).click(function (event) {
                event.preventDefault();
                $('#title-modal-' + product_id).toggleClass('show-modal');
                $('body').addClass('noScroll');
            });
    
            $('#title-close-button-' + product_id).click(function (event) {
                event.preventDefault();
                $('body').removeClass('noScroll');
                $('#title-modal-' + product_id).removeClass('show-modal');
            });
    
            $('#title-modal-' + product_id).click(function (event) {
                event.preventDefault();
                $('#title-modal-' + product_id).removeClass('show-modal');
                $('body').removeClass('noScroll');
            });
    
        });
    });
})( jQuery );
