jQuery(document).ready(function($) {
    $('#rapgk_add_auction').on('click', function() {
        const index = $('#rapgk_auctions_wrapper table tbody tr').length;
        const auctionHTML = `
            <tr class="rapgk_auction">
                <td><input type="text" class="datetimepicker" name="rapgk_auctions[${index}][start]" required></td>
                <td><input type="text" class="datetimepicker" name="rapgk_auctions[${index}][end]" required></td>
                <td><input type="text" class="datetimepicker" name="rapgk_auctions[${index}][date]" required></td>
                <td><input type="number" step="0.01" name="rapgk_auctions[${index}][real_price]" required></td>
                <td><input type="number" step="0.01" name="rapgk_auctions[${index}][min_price]" required></td>
                <td><button type="button" class="rapgk_remove_auction">Remove</button></td>
            </tr>`;
        $('#rapgk_auctions_wrapper table tbody ').append(auctionHTML);
        $('#rapgk_auctions_wrapper .datetimepicker').datetimepicker({
            dateFormat: 'yy-mm-dd',  // Formato de la fecha: YYYY-mm-dd
            timeFormat: 'HH:mm'      // Formato de la hora: HH:ii (24 horas)
        });
    });

    $('#rapgk_auctions_wrapper').on('click', '.rapgk_remove_auction', function() {
        $(this).closest('.rapgk_auction').remove();
    });

    $('#rapgk_auctions_wrapper .datetimepicker').datetimepicker({
        dateFormat: 'yy-mm-dd',  // Formato de la fecha: YYYY-mm-dd
        timeFormat: 'HH:mm'      // Formato de la hora: HH:ii (24 horas)
    });
});