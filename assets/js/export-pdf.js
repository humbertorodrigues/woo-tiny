jQuery(document).on('click', '#exportPdf', function (e) {
    var pdf = new jsPDF('p', 'pt', 'a4');
    var button = jQuery(this);
    pdf.addHTML(jQuery('#pdfContent')[0], function () {
        var filename = button.data('filename') ?? 'relatorio-canais' + '.pdf';
        pdf.save(filename);
    });
});
