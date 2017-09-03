var selectedPkb;

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

        window.location.href = WEB_URL+"unit/aktivasi/so/pkt?key="+keysearch+"&from_date="+fromdate+"&to_date="+todate;
        //console.log(daterange, datesplit, datesplit1, datesplit2, fromdate, todate, keysearch);
    });

    $(".linkPkb").click(function(e) {
        e.preventDefault();
        // var no_so = $(this).parent().prev().text().trim();
        // $("#inputMNoSo").val(no_so);

        // $("#modalPkt").modal("show");
        var no_so = $(this).attr("data-so");
        var no_pkb = $(this).text().trim();
        var param = {
            key: no_so,
            search_by: "no_so",
            order: "no_so",
            sort: "asc",
            from_date: "",
            to_date: "",
            token: TOKEN
        };

        $.ajax({
            method: "POST",
            dataType: "JSON",
            url: API_URL + "aktivasi/so/search_by/1/1",
            data: JSON.stringify(param),
            success: function (r) {
                //console.log(r);
                if (r.success) {
                    var listkhs = r.result.data[0].khs;
                    var listmaterial = r.result.data[0].material;

                    var totalHargaKhs = 0;
                    for (i = 0; i < listkhs.length; i++) {
                        // fill pkb khs table
                        $("#khs-pkb-"+listkhs[i].kode_khs).val(listkhs[i].qty);
                        $("#tr-pkb-khs-"+listkhs[i].kode_khs).css("background", "yellow");
                        $("#tr-pkb-khs-"+listkhs[i].kode_khs).find(".khs-pkb-check").prop("checked", true);
                        $("#total-harga-khs-pkb-"+listkhs[i].kode_khs).val(toRupiah(listkhs[i].total));
                        $("#harga-khs-pkb-"+listkhs[i].kode_khs).val(listkhs[i].total);
                        totalHargaKhs += parseInt(listkhs[i].total);
                    }
                    $("#jml_pkb_khs").val(toRupiah(totalHargaKhs));

                    var totalQtyMaterial = 0;
                    for (i = 0; i < listmaterial.length; i++) {
                        // fill pkb material table
                        $("#material-pkb-"+listmaterial[i].kode_material).val(listmaterial[i].qty);
                        $("#tr-pkb-material-"+listmaterial[i].kode_material).css("background", "#ADD8E6");
                        $("#tr-pkb-material-"+listmaterial[i].kode_material).find(".material-pkb-check").prop("checked", true);    
                        totalQtyMaterial += parseInt(listmaterial[i].qty);                    
                    }
                    $("#jml_pkb_material").val(totalQtyMaterial);
                }
            }
        });

        $("#labelNoPkb, #labelNoPkb2").text(no_pkb);
        $("#modalPkbMaterial").modal("show");
    });

    /* ---------------------- TABLE CONTROL --------------------- */
    // control btn buat pkt di table
    $(".btn-buat-pkt").click(function(e) {
        e.preventDefault();
        $("#btnResetPkbKhs, #btnResetPkbMaterial").trigger("click");
        var no_so = $(this).attr("data-so");
        var pkb_no_bukti = $(this).parent().parent().find(".classNoPkb").text();
        var pkt_no_bukti = $(this).parent().parent().find(".pkt_no_bukti").text();
        selectedPkb = pkb_no_bukti;

        if (pkt_no_bukti.trim().length != 0)
            notifLobi("warning", "top right", "PKT sudah dibuat");
        else if (pkb_no_bukti.trim().length == 0)
            notifLobi("warning", "top right", "PKB belum tersedia"); 
        else if (no_so == undefined)
            notifLobi("warning", "top right", "Pilih salah satu PKB");
        else if (no_so != undefined) {
            $("#inputMNoSo").val(no_so);
            $("#modalPkt").modal("show");
        }
    });

    /* ---------------------- MATERIAL PKB MODAL CONTROL --------------------- */
    // control material qty change
    $(".material_pkb_qty").keyup(function() {
        var qty = ($(this).val().length == 0) ? 0 : parseInt($(this).val());
        if (qty === parseInt(qty, 10)) {

            //var kode = $(this).attr("id").replace("material-pkb-", "");

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
        $(".material_pkb_qty").each(function(index, el) {
            var qty = $(el).val();
            if (qty != "0" && qty != "") {
                totalMaterial += parseInt(qty);
            }

            $("#jml_pkb_material").val(totalMaterial);
        });
    });

    // control material checkbox click
    $(".material-pkb-check").click(function() {
        var isChecked = $(this).is(":checked");
        var elTr = $(this).parent().parent().parent().parent();

        // find element qty
        var elQty = $(elTr).find(".material_pkb_qty");
        var elQtyValue = $(elQty).val();
        var valQty = (elQtyValue.length == 0) ? 0 : parseInt(elQtyValue);

        if (isChecked) {
            $(elQty).val(1);
            $(elTr).css("background", "#ADD8E6");
            //var checkedHarga = parseInt($(elTr).find(".khs_harga").val());
            //$(elTr).find(".total-harga-khs").val(toRupiah(checkedHarga));
        } else {
            $(elQty).val(0);
            $(elTr).css("background", "white");
            //$(elTr).find(".total-harga-khs").val(toRupiah(0));
        }

        totalMaterial = 0;
        $(".material_pkb_qty").each(function(index, el) {
            var qty = $(el).val();
            if (qty != "0" && qty != "") {
                totalMaterial += parseInt(qty);
            }

            $("#jml_pkb_material").val(totalMaterial);
        });
    });

    // control button reset masterial
    $("#btnResetPkbMaterial").click(function() {
        $("#tbl-material-pkb").find("tr").css("background", "white");
        $("#tbl-material-pkb").find("input[type=checkbox]").prop("checked", false);
        $("#jml_pkb_material").val(toRupiah(0));
        $(".material_pkb_qty").val(0);
    });

    // control btn material simpan
    $("#btnSimpanPkbMaterial").click(function(e) {
        e.preventDefault();

        var jmlMaterial = $("#jml_pkb_material").val();
        if (jmlMaterial == "0" || jmlMaterial == "") {
            notifLobi("error", "top right", "Total Material PKB masih kosong");
        } else {
            $("#modalPkbKhs").modal("show");
        }
    });

    /* ---------------------- KHS PKB MODAL CONTROL --------------------- */
    // control khs qty change
    $(".khs_pkb_qty").keyup(function(e) {

        var qty = ($(this).val().length == 0) ? 0 : parseInt($(this).val());
        if (qty === parseInt(qty, 10)) {

            var kode = $(this).attr("id").replace("khs-pkb-", "");
            var harga = parseInt($("#harga-khs-pkb-"+kode).val());

            var total = toRupiah(harga * qty);

            //console.log(kode, qty, harga, total);
            $("#total-harga-khs-pkb-"+kode).val(total);
            if (qty > 0) {
                $(this).parent().parent().css("background", "yellow");
                $(this).parent().parent().find("input[type=checkbox]").prop("checked", true);
            } else if (qty == 0) {
                $(this).parent().parent().css("background", "white");
                $(this).parent().parent().find("input[type=checkbox]").prop("checked", false);
            }
        }

        var totalKhs = 0;
        $(".total-harga-khs-pkb").each(function(index, el) {
            var harga = $(el).val();
            if (harga != "0") {
                var totalHarga = parseInt($(el).val().replace(/\./g, ""));
                totalKhs += totalHarga;
            }

            $("#jml_pkb_khs").val(toRupiah(totalKhs));
            //console.log(totalKhs);
        });
    });

    // control khs checkbox click
    $(".khs-pkb-check").click(function() {
        var isChecked = $(this).is(":checked");
        var elTr = $(this).parent().parent().parent().parent();

        // find element qty
        var elQty = $(elTr).find(".khs_pkb_qty");
        var elQtyValue = $(elQty).val();
        var valQty = (elQtyValue.length == 0) ? 0 : parseInt(elQtyValue);

        // find element harga
        var elHarga = $(elTr).find(".khs_pkb_harga");
        var elHargaValue = $(elHarga).val();

        if (isChecked) {
            $(elQty).val(1);
            $(elTr).css("background", "yellow");
            var checkedHarga = parseInt($(elTr).find(".khs_pkb_harga").val());
            $(elTr).find(".total-harga-khs-pkb").val(toRupiah(checkedHarga));
        } else {
            $(elQty).val(0);
            $(elTr).css("background", "white");
            $(elTr).find(".total-harga-khs-pkb").val(toRupiah(0));
        }

        totalKhs = 0;
        $(".total-harga-khs-pkb").each(function(index, el) {
            var harga = $(el).val();
            if (harga != "0") {
                var totalHarga = parseInt($(el).val().replace(/\./g, ""));
                totalKhs += totalHarga;
            }

            $("#jml_pkb_khs").val(toRupiah(totalKhs));
        });
    });

    // control button reset khs
    $("#btnResetPkbKhs").click(function() {
        $("#tbl-khs-pkb").find("tr").css("background", "white");
        $("#tbl-khs-pkb").find("input[type=checkbox]").prop("checked", false);
        $("#jml_pkb_khs").val(toRupiah(0));
        $(".khs_pkb_qty, .total-harga-khs-pkb").val(0);
    });

    // control button submit khs
    $("#btnSimpanPkbKhs").click(function(e) {
        e.preventDefault();
        var jmlKhs = $("#jml_pkb_khs").val();
        if (jmlKhs == "0" || jmlKhs == "") {
            notifLobi("error", "top right", "Total KHS PKB masih kosong");
        } else {
            // get inserted khs
            var arrKhs = [];
            var qtyKhs = $(".khs_pkb_qty");
            var totalKhs = 0;
            $(".total-harga-khs-pkb").each(function(index, el) {
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

                    totalKhs += parseInt($(this).val().replace(/\./g, ""));
                }
            });

            // get inserted material
            var arrMaterial = [];
            var qtyMaterial = $(".material_pkb_qty");
            $(".material_pkb_qty").each(function(index, el) {
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
                pkb_no_bukti: $("#labelNoPkb").text().trim().toUpperCase(),
                // info khs
                item: arrKhs,
                // info material
                material: arrMaterial,
                token: TOKEN
            };

            //console.log(data, arrKhs, arrMaterial);
            
            $.ajax({
                url: API_URL + "aktivasi/so/pkb/edit",
                method: "POST",
                dataType: "JSON",
                data: JSON.stringify(data),
                success: function (r) {
                    //console.log(r);
                    if (r.success) {
                        notifLobi("success", "top right", r.message);
                        $("#btnResetPkbKhs, #btnResetPkbMaterial").trigger("click");
                        $("#modalPkbKhs, #modalPkbMaterial").modal("hide");
                        // $("#btnResetKhs").trigger("click");
                        // $("#btnResetMaterial").trigger("click");

                        // $("#inputMPelaksana").val("");

                        // $("#modalPkt, #modalKhs, #modalMaterial").modal("hide");

                        // var noSo = $("#inputMNoSo").val().replace("/", "-");
                        // $("#pkt-"+noSo+" a").text($("#inputMNoPkt").val().toUpperCase());
                        // $("#pkt-"+noSo).parent().next().text($("#inputMTglPktView").val().toUpperCase());
                        // $("#pkt-"+noSo).parent().next().next().text(toRupiah(totalKhs));

                        // var noPkb = $("#pkb-"+noSo+" a").text().trim();
                        // $("#pkb-"+noSo).html(noPkb);

                        // count total khs
                        // var newTotalKhsPkt = 0;
                        // $(".nilai-pkt").each(function(index, el) {
                        //     var nilaiTemp = parseInt($(el).text().replace(/\./g, ""));
                        //     newTotalKhsPkt += nilaiTemp;
                        // });

                        // $("#jmlKhsPkt").text(toRupiah(newTotalKhsPkt));
                    } else {
                        notifLobi("error", "top right", r.message);
                    }
                }
            });
            
        }
    });

    /* ---------------------- PKT MODAL CONTROL --------------------- */
    $("#btnBuatPkt").click(function() {
        var noPkt = $("#inputMNoPkt").val().trim();
        if (noPkt.length == 0) {
            $("#fg-noPkt").addClass("has-error");
            $("#inputMNoPkt").attr("placeholder", "NO. PKT TIDAK BOLEH KOSONG");
        } else {
            $("#fg-noPkt").removeClass("has-error");
            $("#inputMNoPkt").attr("placeholder", "NO. PKT");
            $("#labelNoPkt, #labelNoPkt2").text($("#inputMNoPkt").val().toUpperCase());

            // load material pkb di untuk pkt
            //console.log(noPkt, selectedPkb);

            var param = {
                key: selectedPkb,
                search_by: "pkb_no_bukti",
                order: "no_so",
                sort: "asc",
                from_date: "",
                to_date: "",
                token: TOKEN
            };
            
            $.ajax({
                method: "POST",
                dataType: "JSON",
                data: JSON.stringify(param),
                url: API_URL + "aktivasi/so/search_by/1/100",
                success: function (r) {
                    
                    $(".tbl-material-pkt tr").hide();
                    var loop = r.result.data[0].material.length;
                    console.log(r, loop);
                    for (i = 0; i < loop; i++) {
                        var kodeMatPkb = r.result.data[0].material[i].kode_material;
                        var maxQty = parseInt(r.result.data[0].material[i].qty_out) - parseInt(r.result.data[0].material[i].retur);
                        $(".tbl-material-pkt tr#kode-"+kodeMatPkb).show();
                        $(".tbl-material-pkt #material-"+kodeMatPkb).prop("max", maxQty);

                        $(".tbl-material-pkt #material-"+kodeMatPkb).bind("keyup keypress blur change", function() {
                            
                            var qtyPktInput = parseInt($(this).val());
                            var qtyPktMax = $(this).prop("max");
                            if (qtyPktInput > qtyPktMax) {
                                $(this).val("");
                                totalMaterial = 0;
                                $(".material_qty").each(function(index, el) {
                                    var qty = $(el).val();
                                    if (qty != "0" && qty != "") {
                                        totalMaterial += parseInt(qty);
                                    }

                                    $("#jml_material").val(totalMaterial);
                                });
                            }
                        });
                    }

                    $("#modalMaterial").modal("show");
                }
            });
        }
    });

    $("#btnCancelPkt").click(function() {
        $("#fg-noPkt").removeClass("has-error");
        $("#inputMNoPkt").attr("placeholder", "NO. PKT").val("");
    });

    /* ---------------------- MATERIAL PKT MODAL CONTROL --------------------- */
    // control material qty change
    $(".material_qty").on("change keyup", function() {
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
            notifLobi("error", "top right", "Total Material masih kosong");
        } else {
            $("#modalKhs").modal("show");
        }
    });

    /* ---------------------- KHS PKT MODAL CONTROL --------------------- */
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
            // get inserted khs
            var arrKhs = [];
            var qtyKhs = $(".khs_qty");
            var totalKhs = 0;
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

                    totalKhs += parseInt($(this).val().replace(/\./g, ""));
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
                pkt_no_bukti: $("#inputMNoPkt").val().toUpperCase(),
                pkt_tgl: $("#inputMTglPkt").val(),
                // info khs
                item: arrKhs,
                // info material
                material: arrMaterial,
                token: TOKEN
            };

            //console.log(data, arrKhs, arrMaterial);
            
            $.ajax({
                url: API_URL + "aktivasi/so/pkt/create",
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

                        $("#modalPkt, #modalKhs, #modalMaterial").modal("hide");

                        var noSo = $("#inputMNoSo").val().replace("/", "-");
                        $("#pkt-"+noSo+" a").text($("#inputMNoPkt").val().toUpperCase());
                        $("#pkt-"+noSo).parent().next().text($("#inputMTglPktView").val().toUpperCase());
                        $("#pkt-"+noSo).parent().next().next().text(toRupiah(totalKhs));

                        var noPkb = $("#pkb-"+noSo+" a").text().trim();
                        $("#pkb-"+noSo).html(noPkb);

                        // count total khs
                        var newTotalKhsPkt = 0;
                        $(".nilai-pkt").each(function(index, el) {
                            var nilaiTemp = parseInt($(el).text().replace(/\./g, ""));
                            newTotalKhsPkt += nilaiTemp;
                        });

                        $("#jmlKhsPkt").text(toRupiah(newTotalKhsPkt));
                    } else {
                        notifLobi("error", "top right", r.message);
                    }
                }
            });
            
        }
    });
});