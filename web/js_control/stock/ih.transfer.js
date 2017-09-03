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

        window.location.href = WEB_URL+"stock/ih/transfer?key="+keysearch+"&from_date="+fromdate+"&to_date="+todate;
        //console.log(daterange, datesplit, datesplit1, datesplit2, fromdate, todate, keysearch);
    });

    // control btnInput (open modal)
    $("#btnInput").click(function(e) {
        var param = {
            type: "IH-TRANSFER",
            token: TOKEN
        };

        $.ajax({
            method: "POST",
            dataType: "JSON",
            url: API_URL + "util/autonumber/create",
            data: JSON.stringify(param),
            success: function (r) {
                if (r.success)
                    $("#inputMNoBa").val(r.result.auto_number);
            }
        });
    });

    // control input material 
    $("#btnInputMaterial").click(function(e) {
        var kodeRegSumber = $("#inputMRegionalSumber").val();
        var kodeRegTujuan = $("#inputMRegionalTujuan").val();      

        if (kodeRegSumber != kodeRegTujuan) {
            $("#modalMaterial").modal("show");
        } else
            notifLobi("warning", "top right", "Regional sumber dan tujuan tidak boleh sama!");  
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

    // control nilai beli material change
    // $(".material_nilai_beli").keyup(function() {

    //     var nilai_beli = ($(this).val().length == 0) ? 0 : parseInt($(this).val());
    //     if (nilai_beli === parseInt(nilai_beli, 10)) {

    //         //var kode = $(this).attr("id").replace("khs-", "");

    //         //console.log(kode, qty, harga, total);
    //         if (nilai_beli > 0) {
    //             $(this).parent().parent().css("background", "#ADD8E6");
    //             $(this).parent().parent().find("input[type=checkbox]").prop("checked", true);
    //         } else if (qty == 0) {
    //             //$(this).parent().parent().css("background", "white");
    //             //$(this).parent().parent().find("input[type=checkbox]").prop("checked", false);
    //         }
    //     }

    //     totalNilaiBeli = 0;
    //     $(".material_nilai_beli").each(function(index, el) {
    //         var nilai_beli = $(el).val().replace(/\./g,'');
    //         if (nilai_beli != "0" && nilai_beli != "") {
    //             totalNilaiBeli += parseInt(nilai_beli);
    //         }

    //         $("#jml_nilai_material").val(toRupiah(totalNilaiBeli));
    //     });
    // });

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

        // totalNilaiBeli = 0;
        // $(".material_nilai_beli").each(function(index, el) {
        //     var nilai_beli = $(el).val().replace(/\./g,'');
        //     if (nilai_beli != "0" && nilai_beli != "") {
        //         totalNilaiBeli += parseInt(nilai_beli);
        //     }

        //     $("#jml_nilai_material").val(toRupiah(totalNilaiBeli));
        // });
    });

    // control button reset masterial
    $("#btnResetMaterial").click(function() {
        $("#tbl-material").find("tr").css("background", "white");
        $("#tbl-material").find("input[type=checkbox]").prop("checked", false);
        //$("#jml_material, #jml_nilai_material").val(toRupiah(0));
        //$(".material_qty, .material_nilai_beli").val(0);
        $("#jml_material").val(toRupiah(0));
        $(".material_qty").val(0);
    });

    // control btn material simpan
    $("#btnSimpanMaterial").click(function(e) {
        e.preventDefault();
        var jmlMaterial = $("#jml_material").val().trim();
        //var jmlNilaiBeli = $("#jml_nilai_material").val().trim().replace(/\./g, "");
        //var errorNilaiBeli = false;

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

        // console.log(errorNilaiBeli);

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

            var data = {
                no_ba: $("#inputMNoBa").val(),
                tgl: $("#inputMTgl").val(),
                regional_sumber: $("#inputMRegionalSumber").val().toUpperCase(),
                regional_tujuan: $("#inputMRegionalTujuan").val().toUpperCase(),
                user: $("#inputMUser").val().toUpperCase(),
                penerima: $("#inputMPenerima").val().toUpperCase(),
                detail: arrDetail,
                token: TOKEN
            };

            console.log(data);
            $.ajax({
                url: API_URL + "stock/ih/transfer/create",
                method: "POST",
                dataType: "JSON",
                data: JSON.stringify(data),
                success: function (r) {
                    //console.log(r);
                    if (r.success) {
                        notifLobi("success", "top right", r.message);
                        
                        $("#btnResetMaterial").trigger("click");

                        $("#modalPembelianMaterial, #modalMaterial").modal("hide");

                        //var noSo = $("#inputMNoSo").val().replace("/", "-");
                        //var noSoText = $("#linkNoSo-"+noSo).text();
                        //$("#linkNoSo-"+noSo).parent().html(noSoText);
                        //$("#pkb-"+noSo).html($("#inputMNoPkb").val().toUpperCase());

                        setTimeout(function() {
                            window.location = WEB_URL + "stock/pembelian/material/pusat";
                        }, 5000);
                    } else {
                        notifLobi("error", "top right", r.message);
                    }
                }
            });
        }
    });
});