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

        window.location.href = WEB_URL+"kansi/rekapitulasi/biaya_material?posisi_date="+posisidate;
    });
});