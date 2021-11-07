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
                var take_action = $(this).data('action');   // add or remove
                var product_id = $(this).data('product_id');    // product_id
                var variation_id = $(this).data('variationid');// variation_id
                var menu_day = $(this).data('menu_day');// menu_day
                var variation = $(this).data('variation');  // variation array

                var currentQty = 0;
                var quantity = (take_action == 'add') ? 1 : -1;
                var current_qty_node = $(this).parent().find('.atc-current-quantity');
                
                if (current_qty_node.text() !== '') {
                    currentQty = parseInt(current_qty_node.text());
                }

                var total_qty_in_cart_node = $('#overlay-' + product_id + '-' + menu_day + ' .qty-in-cart');
                var total_qty_in_cart = total_qty_in_cart_node.text();

                var id = (product_type == 'simple') ? product_id : variation_id;
                
                $.ajax({
                    type: 'POST',
                    url: config_custom.ajax_url,
                    dataType: 'json',
                    data: {
                        'action': 'adjust_cart',
                        'product_id': id,
                        'menu_day': menu_day,
                        'quantity': quantity,
                        'take_action': take_action
                    },
                    success: function (response) {
                        $('.custom-add-to-cart').removeClass('disabled');
                        $('.custom-add-to-cart').css('color', '#EE6948');

                        if (take_action === 'add') {
                            currentQty++;
                            total_qty_in_cart++;
                        }
                        else {
                            currentQty--;
                            total_qty_in_cart--;

                            if (currentQty <= 0) currentQty = 0;
                            if (total_qty_in_cart <= 0) total_qty_in_cart = 0;      
                        }

                        if (total_qty_in_cart < 1) {
                            total_qty_in_cart_node.parent().addClass(' not-in-cart');
                        } 
                        else {
                            total_qty_in_cart_node.parent().removeClass('not-in-cart');
                        }

                        total_qty_in_cart_node.text(total_qty_in_cart);
                        current_qty_node.text(currentQty);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus);
                    }
                });
            });
        });

        $.each($('.nf-popup'), function (index, value) {
            var modal_id = $(this).data('modal_id');

            $(this).click(function (event) {
                event.preventDefault();
                $('#nf-modal-' + modal_id).toggleClass('show-modal');
                $('body').addClass('noScroll');
            });

            $('.close-button').click(function (event) {
                event.preventDefault();
                $('body').removeClass('noScroll');
                $(this).parent().parent().removeClass('show-modal');
            });

            $('#nf-modal-' + modal_id).click(function (event) {
                event.preventDefault();
                $('#modal-' + modal_id).removeClass('show-modal');
                $('body').removeClass('noScroll');
            });

        });

        $.each($('.title-popup-link'), function (index, value) {
            var modal_id = $(this).data('modal_id');

            $(this).click(function (event) {
                event.preventDefault();
                $('#title-modal-' + modal_id).toggleClass('show-modal');
                $('body').addClass('noScroll');
            });
    
            $('#title-modal-' + modal_id + ' .close-button').click(function (event) {
                event.preventDefault();
                $('body').removeClass('noScroll');
                $('#title-modal-' + modal_id).removeClass('show-modal');
            });
    
            $('#title-modal-' + modal_id).click(function (event) {
                event.preventDefault();
                $('#title-modal-' + modal_id).removeClass('show-modal');
                $('body').removeClass('noScroll');
            });
    
        });
    });
})( jQuery );
