$("#daterange").daterangepicker({
    locale: {
      format: "DD/MM/YYYY"
    }
});

$(function() {
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

        window.location.href = WEB_URL+"unit/aktivasi/so/pkt/detail?key="+keysearch+"&from_date="+fromdate+"&to_date="+todate;
        //console.log(daterange, datesplit, datesplit1, datesplit2, fromdate, todate, keysearch);
    });

    // control button upload
    $("#btnUpload").click(function(e) {
        e.preventDefault();
        var row = $("#tbl-pkt-detail").datagrid("getSelected");

        if (row != null) {
            var file_gr = row.dummyHiddenPktGrFile.trim();

            if (file_gr.length > 0)
                notifLobi("warning", "top right", "File GR sudah ada!");
            else {
                // check dulu apakah pkb masih mengalami kerugian, jika ya harus input kerugian dulu
                var param = {
                    no_pkb: row.dummyHiddenNoPkb.trim(),
                    token: TOKEN
                }

                $.ajax({
                    method: "POST",
                    url: API_URL + "aktivasi/so/pkt/is_pkb_rugi",
                    dataType: "JSON",
                    data: JSON.stringify(param),
                    success: function (r) {
                        if (r.success) {
                            if (!r.result.kerugian) {
                                $("#inputMNoPkt").val(row.dummyHiddenNoPkt);
                                $("#inputMNoSo").val(row.dummyHiddenNoSo);
                                $("#modalUploadGr").modal("show");
                            } else
                                notifLobi("warning", "top right", "PKB mengalami kerugian, silahkan input kerugian dahulu!");
                        } else
                            notifLobi("warning", "top right", "Error check kerugian!");
                    }
                });
            }
        } else 
            notifLobi("warning", "top right", "Pilih salah satu PKT!");
    });

    // control button hapus
    $("#btnHapus").click(function(e) {
        e.preventDefault();
        var row = $("#tbl-pkt-detail").datagrid("getSelected");
        var file_gr = row.dummyHiddenPktGrFile.trim();

        if (row != null) {
            if (file_gr.length == 0)
                notifLobi("warning", "top right", "File GR belum ada!");
            else {
                $.ajax({
                    method: "POST",
                    dataType: "JSON",
                    url: WEB_URL + "unit/aktivasi/so/pkt/hapus_gr",
                    data: { no_so: row.dummyHiddenNoSo, file_gr: file_gr },
                    success: function (r) {
                        console.log(r);
                        if (r.success) {
                            notifLobi("success", "top right", r.message);
                            setTimeout(function() {
                                window.location = WEB_URL + "unit/aktivasi/so/pkt/detail";
                            }, 5000);
                        } else 
                            notifLobi("error", "top right", r.message);

                    }
                });
            }
        } else 
            notifLobi("warning", "top right", "Pilih salah satu PKT!");
    });
});