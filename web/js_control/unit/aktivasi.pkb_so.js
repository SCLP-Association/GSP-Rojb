$("#daterange").daterangepicker({
    locale: {
      format: "DD/MM/YYYY"
    }
});

var totalKhs = 0;

$(function() {
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

        window.location.href = WEB_URL+"unit/aktivasi/so/pkb?key="+keysearch+"&from_date="+fromdate+"&to_date="+todate;
        //console.log(daterange, datesplit, datesplit1, datesplit2, fromdate, todate, keysearch);
    });

    // control linkNoSo click -> open modal create pkb
    $(".linkNoSo").click(function(e) {
        e.preventDefault();
        $("#modalPkb").modal("show");
        var no_so = $(this).text().trim();

        $("#inputMNoSo").val(no_so);

        // get no pkb
        var param = {
            type: "AKTIVASI-SO-PKB",
            no_so: no_so,
            token: TOKEN
        };

        $.ajax({
           method: "POST",
           dataType: "JSON",
           url: API_URL + "util/autonumber/create",
           data: JSON.stringify(param),
           success: function (r) {
               //console.log(r);
               $("#inputMNoPkb").val(r.result.auto_number);
           } 
        });
    });

    // control btn simpan pkb
    $("#btnSimpanPkb").click(function() {
        var noPkb = $("#inputMNoPkb").val();
        
        if ($("#inputMPelaksana").val().length == 0) {
            $("#inputMPelaksana").parent().parent().addClass("has-error");
            $("#inputMPelaksana").attr("placeholder", "PELAKSANA HARUS DIISI");
        } else {
            $("#inputMPelaksana").parent().parent().removeClass("has-error");
            $("#inputMPelaksana").attr("placeholder", "PELAKSANA");
            $("#labelNoPkb, #labelNoPkb2").text(noPkb);
            $("#modalKhs").modal("show");
        }
    });

    /* ---------------------- KHS MODAL CONTROL --------------------- */
    // control khs qty change
    $(".khs_qty").keyup(function(e) {

        var qty = ($(this).val().length == 0) ? 0 : parseInt($(this).val());
        if (qty === parseInt(qty, 10)) {

            var kode = $(this).attr("id").replace("khs-", "");
            var harga = parseInt($("#harga-khs-"+kode).val());

            var total = toRupiah(harga * qty);

            //console.log(kode, qty, harga, total);
            $("#total-harga-khs-"+kode).val(total);
            if (qty > 0) {
                $(this).parent().parent().css("background", "yellow");
                $(this).parent().parent().find("input[type=checkbox]").prop("checked", true);
            } else if (qty == 0) {
                $(this).parent().parent().css("background", "white");
                $(this).parent().parent().find("input[type=checkbox]").prop("checked", false);
            }
        }

        totalKhs = 0;
        $(".total-harga-khs").each(function(index, el) {
            var harga = $(el).val();
            if (harga != "0") {
                var totalHarga = parseInt($(el).val().replace(/\./g, ""));
                totalKhs += totalHarga;
            }

            $("#jml_khs").val(toRupiah(totalKhs));
        });
    });

    // control khs checkbox click
    $(".khs-check").click(function() {
        var isChecked = $(this).is(":checked");
        var elTr = $(this).parent().parent().parent().parent();

        // find element qty
        var elQty = $(elTr).find(".khs_qty");
        var elQtyValue = $(elQty).val();
        var valQty = (elQtyValue.length == 0) ? 0 : parseInt(elQtyValue);

        // find element harga
        var elHarga = $(elTr).find(".khs_harga");
        var elHargaValue = $(elHarga).val();

        if (isChecked) {
            $(elQty).val(1);
            $(elTr).css("background", "yellow");
            var checkedHarga = parseInt($(elTr).find(".khs_harga").val());
            $(elTr).find(".total-harga-khs").val(toRupiah(checkedHarga));
        } else {
            $(elQty).val(0);
            $(elTr).css("background", "white");
            $(elTr).find(".total-harga-khs").val(toRupiah(0));
        }

        totalKhs = 0;
        $(".total-harga-khs").each(function(index, el) {
            var harga = $(el).val();
            if (harga != "0") {
                var totalHarga = parseInt($(el).val().replace(/\./g, ""));
                totalKhs += totalHarga;
            }

            $("#jml_khs").val(toRupiah(totalKhs));
        });
    });

    // control button reset khs
    $("#btnResetKhs").click(function() {
        $("#tbl-khs").find("tr").css("background", "white");
        $("#tbl-khs").find("input[type=checkbox]").prop("checked", false);
        $("#jml_khs").val(toRupiah(0));
        $(".khs_qty, .total-harga-khs").val(0);
    });

    // control button submit khs
    $("#btnSimpanKhs").click(function(e) {
        e.preventDefault();
        var jmlKhs = $("#jml_khs").val();
        if (jmlKhs == "0" || jmlKhs == "") {
            notifLobi("error", "top right", "Total KHS masih kosong");
        } else {
            $("#modalMaterial").modal("show");
        }
    });

    /* ---------------------- MATERIAL MODAL CONTROL --------------------- */
    // control material qty change
    $(".material_qty").keyup(function() {
        var qty = ($(this).val().length == 0) ? 0 : parseInt($(this).val());
        if (qty === parseInt(qty, 10)) {

            var kode = $(this).attr("id").replace("khs-", "");

            //console.log(kode, qty, harga, total);
            if (qty > 0) {
                $(this).parent().parent().css("background", "#ADD8E6");
                $(this).parent().parent().find("input[type=checkbox]").prop("checked", true);
            } else if (qty == 0) {
                $(this).parent().parent().css("background", "white");
                $(this).parent().parent().find("input[type=checkbox]").prop("checked", false);
            }
        }

        totalMaterial = 0;
        $(".material_qty").each(function(index, el) {
            var qty = $(el).val();
            if (qty != "0" && qty != "") {
                totalMaterial += parseInt(qty);
            }

            $("#jml_material").val(totalMaterial);
        });
    });

    // control material checkbox click
    $(".material-check").click(function() {
        var isChecked = $(this).is(":checked");
        var elTr = $(this).parent().parent().parent().parent();

        // find element qty
        var elQty = $(elTr).find(".material_qty");
        var elQtyValue = $(elQty).val();
        var valQty = (elQtyValue.length == 0) ? 0 : parseInt(elQtyValue);

        if (isChecked) {
            $(elQty).val(1);
            $(elTr).css("background", "#ADD8E6");
            var checkedHarga = parseInt($(elTr).find(".khs_harga").val());
            $(elTr).find(".total-harga-khs").val(toRupiah(checkedHarga));
        } else {
            $(elQty).val(0);
            $(elTr).css("background", "white");
            $(elTr).find(".total-harga-khs").val(toRupiah(0));
        }

        totalMaterial = 0;
        $(".material_qty").each(function(index, el) {
            var qty = $(el).val();
            if (qty != "0" && qty != "") {
                totalMaterial += parseInt(qty);
            }

            $("#jml_material").val(totalMaterial);
        });
    });

    // control button reset masterial
    $("#btnResetMaterial").click(function() {
        $("#tbl-material").find("tr").css("background", "white");
        $("#tbl-material").find("input[type=checkbox]").prop("checked", false);
        $("#jml_material").val(toRupiah(0));
        $(".material_qty, .total-harga-material").val(0);
    });

    // control btn material simpan
    $("#btnSimpanMaterial").click(function(e) {
        e.preventDefault();
        var jmlMaterial = $("#jml_material").val();
        if (jmlMaterial == "0" || jmlMaterial == "") {
            notifLobi("error", "top right", "Total kuantiti material masih kosong");
        } else {
            
            // get inserted khs
            var arrKhs = [];
            var qtyKhs = $(".khs_qty");
            $(".total-harga-khs").each(function(index, el) {
                var harga = $(el).val();
                if (harga != "0" && harga != "") {
                    // var qty = $(qtyKhs[index]).val();
                    // var kode = $(qtyKhs[index]).attr("data-kode");
                    // var jenis = $(qtyKhs[index]).attr("data-jenis");
                    // var total = $(this).val().replace(/\./g, "");

                    arrKhs.push({
                        kode_khs: $(qtyKhs[index]).attr("data-kode"),
                        jenis_khs: $(qtyKhs[index]).attr("data-jenis"),
                        qty: $(qtyKhs[index]).val(),
                        total: $(this).val().replace(/\./g, "")
                    });
                }
            });

            // get inserted material
            var arrMaterial = [];
            var qtyMaterial = $(".material_qty");
            $(".material_qty").each(function(index, el) {
                var qty = $(el).val();
                if (qty != "0" && qty != "") {
                    // var qty = $(qtyMaterial[index]).val();
                    // var kode = $(qtyMaterial[index]).attr("data-kode");
                    // var jenis = $(qtyMaterial[index]).attr("data-jenis");

                    arrMaterial.push({
                        kode_material: $(qtyMaterial[index]).attr("data-kode"),
                        jenis_material: $(qtyMaterial[index]).attr("data-jenis"),
                        qty: $(qtyMaterial[index]).val()
                    });
                }
            });

            //console.log(arrKhs, arrMaterial);

            var data = {
                // info pkb
                no_so: $("#inputMNoSo").val(),
                pkb_no_bukti: $("#inputMNoPkb").val().toUpperCase(),
                pkb_tgl: $("#inputMTglPkb").val(),
                pkb_pelaksana: $("#inputinputMPelaksana").val(),
                // info khs
                item: arrKhs,
                // info material
                material: arrMaterial,
                token: TOKEN
            };

            $.ajax({
                url: API_URL + "aktivasi/so/pkb/create",
                method: "POST",
                dataType: "JSON",
                data: JSON.stringify(data),
                success: function (r) {
                    //console.log(r);
                    if (r.success) {
                        notifLobi("success", "top right", r.message);
                        $("#btnResetKhs").trigger("click");
                        $("#btnResetMaterial").trigger("click");

                        $("#inputMPelaksana").val("");

                        $("#modalPkb, #modalKhs, #modalMaterial").modal("hide");

                        var noSo = $("#inputMNoSo").val().replace("/", "-");
                        var noSoText = $("#linkNoSo-"+noSo).text();
                        $("#linkNoSo-"+noSo).parent().html(noSoText);
                        $("#pkb-"+noSo).html($("#inputMNoPkb").val().toUpperCase());
                    } else {
                        notifLobi("error", "top right", r.message);
                    }
                }
            });
        }
    });
});