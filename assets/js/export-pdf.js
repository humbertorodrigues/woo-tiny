jQuery(function ($) {
    $(document).on('click', '#exportPdf', function (e) {
        var pdf = new jsPDF('p', 'pt', 'a4');
        var button = $(this);
        pdf.addHTML($('#pdfContent')[0], function () {
            var filename = button.data('filename') ?? 'relatorio-canais' + '.pdf';
            pdf.save(filename);
        });
    });

    $(document).on('submit', 'form#posts-filter', function (e) {
        var formdata = new FormData($(this).closest('form')[0]);
        if (formdata.get('action') == 'export_pdf') {
            var pdf = new jsPDF('l', 'pt', 'a4');
            pdf.addHTML($('.wp-list-table')[0], function () {
                var filename = 'pedidos' + '.pdf';
                pdf.save(filename);
            });
            e.preventDefault();
        }
    });
});
