$("#daterange").daterangepicker({
    locale: {
      format: "DD/MM/YYYY"
    },
    singleDatePicker: true,
    showDropdowns: true
});

$(function() {
    // control search
    $("#btnSearch").click(function(e) {
        e.preventDefault();
        var daterange = $("#daterange").val().split(" ").join("");
        var datesplit = daterange.split("/");

        var posisidate = datesplit[2] + "-" + datesplit[1] + "-" + datesplit[0];

        window.location.href = WEB_URL+"kansi/wip_hpp/aktivasi?posisi_date="+posisidate;
    });

    // control button invoice
    $("#btnInvoice").click(function() {
        var row = $("#dg").datagrid("getSelected");
        if (row != null) {
            var gr_file = row.dummyGr;

            if (gr_file.trim().length > 0) {
                // open modal invoice input
            } else
                notifLobi("error", "top right", "Gr file tersedia!");
        } else
            notifLobi("error", "top right", "Pilih salah satu wip!");
    });
});