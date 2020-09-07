define([
    'jquery',
    'jquery/ui',
], function ($) {
    'use strict';
    $.widget('mage.email', {
        _create: function () {
            var options = this.options;
            $(document).ready(function () {
                $('.btn-save, .btn-cancel').hide();
                var inputVal;

                $('.btn-edit').click(function () {
                    $(this).closest('tr').find('.btn-edit').hide();
                    $(this).closest('tr').find('.btn-save, .btn-cancel').show();
                    $(this).closest('tr').find('input').removeAttr('disabled').focus();
                    inputVal = $(this).closest('tr').find('input').val();
                });



                $('.btn-save').click(function () {
                    inputVal = $(this).closest('tr').find('input').val();

                    $(this).closest('tr').find('.btn-save, .btn-cancel').hide();
                    $(this).closest('tr').find('.btn-edit').show();
                    $(this).closest('tr').find('input').attr("disabled", true);

                    $.ajax({
                        type: 'POST',
                        url: options.url,
                        data: {email: inputVal, order_id: options.orderId},
                        showLoader: true,
                        cache: false,
                        success:function (response)
                        {}
                    });

                });


                $('.btn-cancel').click(function () {
                    $(this).closest('tr').find('input').val(inputVal);
                    $(this).closest('tr').find('input').attr('disabled', 'disabled');
                    $(this).closest('tr').find('.btn-save, .btn-cancel').hide();
                    $(this).closest('tr').find('.btn-edit').show();
                });

            });
        }
    });
    return $.mage.email;
});
