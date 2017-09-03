$("#daterange").daterangepicker({
    locale: {
      format: "DD/MM/YYYY"
    }
});

$(function () { 
    $("[data-mask]").inputmask();

    // control search
    $("#btnSearch").click(function(e) {
        e.preventDefault();
        var daterange = $("#daterange").val().split(" ").join("");
        var datesplit = daterange.split("-");
        var datesplit1 = datesplit[0].split("/");
        var datesplit2 = datesplit[1].split("/");

        var fromdate = datesplit1[2] + "-" + datesplit1[1] + "-" + datesplit1[0];
        var todate = datesplit2[2] + "-" + datesplit2[1] + "-" + datesplit2[0];
        var keysearch = $("#keySearch").val().trim();
        if (keysearch.length == 0)
            keysearch = "_";

        window.location.href = WEB_URL+"unit/aktivasi/so?key="+keysearch+"&from_date="+fromdate+"&to_date="+todate;
        //console.log(daterange, datesplit, datesplit1, datesplit2, fromdate, todate, keysearch);
    });

    // control button save so
    $("#btnSimpan").click(function() {
        var param = {
            no_so: $("#inputMNoSo").val(),
            tgl: $("#inputMTglSo").val(),
            ptl: $("#inputMPtla").val(),
            pekerjaan: $("#inputMPekerjaan").val(),
            token: TOKEN
        };

        $.ajax({
            url: API_URL + "aktivasi/so/create",
            method: "POST",
            dataType: "JSON",
            data: JSON.stringify(param),
            success: function(r) {

                if (!r.success)
                    notifLobi("error", "top right", r.message);
                else {
                    notifLobi("success", "top right", r.message);
                    $("#inputMNoSo, #inputMPekerjaan").val("");
                    $("#modalSo").modal("hide");

                    var newRow = '<tr>'+
                        '<td class="text-center">'+r.result.no_so+'</td>'+
                        '<td class="text-center">'+$("#inputMTglSoView").val()+'</td>'+
                        '<td class="text-center">'+r.result.ptl+'</td>'+
                        '<td>'+r.result.pekerjaan+'</td>'+
                    '</tr>';
                    $("#tb-ref").append(newRow);
                }
            }
        });
    });
});