$("#daterange").daterangepicker({
    locale: {
      format: "DD/MM/YYYY"
    }
});

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

        window.location.href = WEB_URL+"unit/pm/material?key="+keysearch+"&from_date="+fromdate+"&to_date="+todate;
        //console.log(daterange, datesplit, datesplit1, datesplit2, fromdate, todate, keysearch);
    });

    // control open modal button
    $("#btnInput").click(function(e) {
        // load no bukti
        var param = {
            type: "PM-PENGGUNAAN-MATERIAL",
            token: TOKEN
        };

        $.ajax({
            method: "POST",
            url: API_URL + "util/autonumber/create",
            dataType: "JSON",
            data: JSON.stringify(param),
            success: function (r) {
                if (r.success)
                    $("#inputMNoBukti").val(r.result.auto_number);
            }
        });
    });

    // control radion check jenis
    $(".cb-jenis").click(function(e) {
        var jenis = $(this).val();
        if (jenis == "OSP") {
            $("#form-box-tikor, #form-box-lokasi").show();
            $("#form-box-pop").hide();
        } else {
            $("#form-box-tikor, #form-box-lokasi").hide();
            $("#form-box-pop").show();
        }
    });

    // control btnInputMaterial
    $("#btnInputMaterial").click(function(e) {
        //e.preventDefault();
        var jenis = $("input[name=jenis]:checked").val();
        if (jenis == "OSP") {
            var tikor = $("#inputMTikor").val().trim();
            var lokasi = $("#inputMLokasi").val().trim();
            
            if (tikor.length == 0) {
                $("#inputMTikor").parent().parent().addClass("has-error");
                $("#inputMTikor").attr("placeholder", "TIKOR TIDAK BOLEH KOSONG");
                return false;
            }

            if (lokasi.length == 0) {
                $("#inputMLokasi").parent().parent().addClass("has-error");
                $("#inputMLokasi").attr("placeholder", "LOKASI TIDAK BOLEH KOSONG");
                return false
            }
        } 

        $("#modalMaterial").modal("show");
    });

    /* ---------------------- MATERIAL MODAL CONTROL --------------------- */
    // control material qty change
    $(".material_qty").keyup(function() {
        var qty = ($(this).val().length == 0) ? 0 : parseInt($(this).val());
        if (qty === parseInt(qty, 10)) {

            //var kode = $(this).attr("id").replace("khs-", "");

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
            //var checkedHarga = parseInt($(elTr).find(".khs_harga").val());
            //$(elTr).find(".material_nilai_beli").val(toRupiah(1));
        } else {
            $(elQty).val(0);
            $(elTr).css("background", "white");
            $(elTr).find(".material_nilai_beli").val(toRupiah(0));
        }

        totalMaterial = 0;
        $(".material_qty").each(function(index, el) {
            var qty = $(el).val();
            if (qty != "0" && qty != "") {
                totalMaterial += parseInt(qty);
            }

            $("#jml_material").val(totalMaterial);
        });

        totalNilaiBeli = 0;
        $(".material_nilai_beli").each(function(index, el) {
            var nilai_beli = $(el).val().replace(/\./g,'');
            if (nilai_beli != "0" && nilai_beli != "") {
                totalNilaiBeli += parseInt(nilai_beli);
            }

            $("#jml_nilai_material").val(toRupiah(totalNilaiBeli));
        });
    });

    // control button reset masterial
    $("#btnResetMaterial").click(function() {
        $("#tbl-material").find("tr").css("background", "white");
        $("#tbl-material").find("input[type=checkbox]").prop("checked", false);
        $("#jml_material, #jml_nilai_material").val(toRupiah(0));
        $(".material_qty, .material_nilai_beli").val(0);
    });

    // control btn material simpan
    $("#btnSimpanMaterial").click(function(e) {
        e.preventDefault();
        var jmlMaterial = $("#jml_material").val().trim();
        var errorNilaiBeli = false;

        // periksa apakah salah satu nilai beli ada yang kosoong
        // $(".material-check").each(function(index, el) {
        //     var isChecked = $(this).is(":checked");
        //     //console.log("checked : " + isChecked);  
        //     if (isChecked) {
        //         var nilaiBeli = $(this).parent().parent().parent().parent().find(".material_nilai_beli").val().replace(/\./g, "");   
        //         //console.log("loop : " + nilaiBeli);             
        //         if (nilaiBeli == "" || nilaiBeli == "0")
        //             errorNilaiBeli = true;
        //     }
        // });

        console.log(errorNilaiBeli);

        if (jmlMaterial == "0" || jmlMaterial == "") {
            notifLobi("error", "top right", "Total kuantiti material masih kosong");
        } else {

            var arrDetail = [];
            $(".material-check").each(function(index, el) {
                var isChecked = $(this).is(":checked");
                var elTr = $(this).parent().parent().parent().parent();
                if (isChecked) {
                    // masukan ke detail serpo pembelian material
                    var kodeMaterial = $(elTr).find(".data-kode-material").text();
                    var jenisMaterial = $(elTr).find(".data-jenis-material").text();
                    var satuanMaterial = $(elTr).find(".data-satuan-material").text();
                    var qtyMaterial = $(elTr).find(".material_qty").val();
                    //var nilaiBeli = $(elTr).find(".material_nilai_beli").val().replace(/\./g, "");
                    arrDetail.push({
                        kode_material: kodeMaterial,
                        jenis_material: jenisMaterial,
                        satuan_material: satuanMaterial,
                        qty: qtyMaterial
                        //nilai_beli: nilaiBeli
                    });
                }
            });

            var jenis = $("input[name=jenis]:checked").val();
            var pop = $("#inputMPop").val();
            var tikor = $("#inputMTikor").val();
            var lokasi = $("#inputMLokasi").val();
            var deskripsi = "";
            if (jenis == "ISP") {
                deskripsi = jenis + ", " + pop;
            } else if (jenis == "OSP") {
                deskripsi = jenis + ", " + tikor + ", " + lokasi;
            }

            var data = {
                no_bukti: $("#inputMNoBukti").val(),
                tgl: $("#inputMTgl").val(),
                kode_regional: $("#inputMKodeRegional").val(),
                jenis: jenis,
                pop: pop,
                tikor: tikor,
                lokasi: lokasi,
                deskripsi: deskripsi,
                detail: arrDetail,
                token: TOKEN
            };

            //console.log(data);
            $.ajax({
                url: API_URL + "pm/material/input",
                method: "POST",
                dataType: "JSON",
                data: JSON.stringify(data),
                success: function (r) {
                    //console.log(r);
                    if (r.success) {
                        notifLobi("success", "top right", r.message);
                        
                        $("#btnResetMaterial").trigger("click");

                        $("#modalPenggunaanMaterial, #modalMaterial").modal("hide");

                        //var noSo = $("#inputMNoSo").val().replace("/", "-");
                        //var noSoText = $("#linkNoSo-"+noSo).text();
                        //$("#linkNoSo-"+noSo).parent().html(noSoText);
                        //$("#pkb-"+noSo).html($("#inputMNoPkb").val().toUpperCase());

                        setTimeout(function() {
                            window.location = WEB_URL + "unit/pm/material";
                        }, 5000);
                    } else {
                        notifLobi("error", "top right", r.message);
                    }
                }
            });
        }
    });
});