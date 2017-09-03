var row;
var noPkb;
var noSO;
var noPkt;
var lastQtyOut;
var lastReturQty;
var returPage;

$(function() {
    // control button Qout
    $("#btnQout").click(function(e) {
        e.preventDefault();
        row = $("#dgRequest").datagrid("getSelected");

        noPkb = row.hiddenPkb.trim();
        noPkt = row.hiddenPkt.trim();
        noSO = row.hiddenNoSo.trim();
        dataMaterial = JSON.parse(row.hiddenData.trim());

        if (noPkt.length == 0) {
            var dataTable = '';
            for (i = 0; i < dataMaterial.length; i++) {
                if (dataMaterial[i].qty - dataMaterial[i].qty_out > 0)
                {
                    dataTable += '<tr>'+
                        '<td class="text-center"><span class="kode">'+dataMaterial[i].kode_material+'</span></td>'+
                        '<td><span class="jenis">'+dataMaterial[i].jenis_material+'</span></td>'+
                        '<td class="text-center"><input type="text" class="form-control qout text-center" onkeypress="return event.charCode >= 48 && event.charCode <= 57"/></td>'+
                    '</tr>';
                }
            }
            $("#tb-material-tmp tbody").html(dataTable);

            var param = {
                type: "QOUT-PENGELUARAN-MATERIAL",
                token: TOKEN
            };

            $.ajax({
                method: "POST",
                dataType: "JSON",
                url: API_URL + "util/autonumber/create",
                data: JSON.stringify(param),
                success: function (r) {
                    if (r.success)
                        $("#inputMNoBukti").val(r.result.auto_number);
                }
            });

            $("#modalQout").modal("show");
        } else
            notifLobi("error", "top right", noPkb + " Proses PKT sudah dilakukan");
    });

    // control simpan btnSimpanQout
    $("#btnSimpanQout").click(function (e) {
        e.preventDefault();
        var totQtyOut = 0;
        $(".qout").each( function (index, element) {
            var qout = ($(element).val().trim().length == 0) ? 0 : parseInt($(element).val().trim());
            totQtyOut += qout;
        });

        if (totQtyOut == 0)
            notifLobi("error", "top right", "Total Qty Out masih nol");
        else if ($("#inputMPenerima").val().trim().length == 0)
            notifLobi("error", "top right", "Nama penerima harus diisi");
        else {
            var detail = [];

            var kodeMaterial = $(".kode");
            var jenisMaterial = $(".jenis");
            $(".qout").each( function (index, element) {
                var qout = ($(element).val().trim().length == 0) ? 0 : parseInt($(element).val().trim());

                detail.push({
                    kode_material: $(kodeMaterial[index]).text(),
                    jenis_material: $(jenisMaterial[index]).text(),
                    qty_out: qout
                });
            });

            var param = {
                no_so: noSO,
                no_bukti: $("#inputMNoBukti").val(),
                no_dokumen: noPkb,
                tgl: $("#inputMTgl").val(),
                penerima: $("#inputMPenerima").val(),
                detail: detail,
                token: TOKEN
            };

            $.ajax({
                method: "POST",
                dataType: "JSON",
                url: API_URL + "stock/pengeluaran/material/input",
                data: JSON.stringify(param),
                success: function (r) {
                    if (r.success) {
                        $("#modalQout").modal("hide");
                        notifLobi("success", "top right", r.message);
                        setTimeout(function() {
                            window.location = WEB_URL + "stock/pengeluaran/material";
                        }, 5000);
                    } else {
                         notifLobi("error", "top right", r.message);
                    }
                }
            });

            //console.log(param);
        }
    });

     // --------------------------------- RETUR MATERIAL --------------------------------- //
    function onChangeReturMaterial() {
        $("#tbl-retur tbody").html("");
        returPage = 1;
        // get available qty out
        var kodeMaterial = $("#cbReturMaterial").val();
        var param = {
            pkb_no_bukti: noPkb,
            kode_material: kodeMaterial,
            page: returPage,
            token: TOKEN
        };

        $.ajax({
            method: "POST",
            url: API_URL + "stock/available_qout",
            dataType: "JSON",
            data: JSON.stringify(param),
            success: function (r) {
                //console.log(r);
                if (r.success) {
                    lastQtyOut = r.result.qty_out;
                    lastReturQty = r.result.qty_out_stock;

                    var htmlString = '<tr>'+
                        '<td>'+r.result.no_bukti+'</td>'+
                        '<td style="text-align:center;">'+r.result.qty_out_stock+'</td>'+
                        '<td style="text-align:center;">'+
                            '<input type="number" class="total-retur" value="1" style="text-align:center;width:80%;" min="1" max="'+r.result.qty_out_stock+'" class="qty_retur" onkeypress="return event.charCode >= 48 && event.charCode <= 57"/></td>'+
                        '<td><a href="" class="btn btn-default btn-sm add-retur"><i class="fa fa-fw fa-plus"></i><span><span></a></td>'+
                    '</tr>';

                    $("#tbl-retur tbody").append(htmlString);

                    $(".total-retur").on("keyup keypress blur change", function() {
                        var qtyReturInput = parseInt($(this).val());
                        var qtyReturMax = $(this).prop("max");
                        if (qtyReturInput > qtyReturMax)
                            $(this).val("1");
                    });

                    $(".add-retur").bind("click", function(e) {
                        e.preventDefault();
                        var totalRetur = $(".total-retur").length;
                        var lastRetur = $(".total-retur")[totalRetur - 1];

                        var lastReturVal = ($(lastRetur).val().length == 0) ? 0 : $(lastRetur).val();
                        var currentQtyRetur = parseInt(lastReturVal);

                        if (currentQtyRetur > lastReturQty)
                            notifLobi("error", "top right", "Stock qty_out lebih besar dari permintaan retur");
                        else if (currentQtyRetur < lastReturQty)
                            notifLobi("error", "top right", "Habiskan dulu stock qty_out");
                        else {
                            $(".total-retur").prop("readonly", true);
                            returPage++;
                            var param = {
                                pkb_no_bukti: noPkb,
                                kode_material: kodeMaterial,
                                page: returPage,
                                token: TOKEN
                            };

                             $.ajax({
                                method: "POST",
                                url: API_URL + "stock/available_qout",
                                dataType: "JSON",
                                data: JSON.stringify(param),
                                success: function (r2) {
                                    if (r2.success) {
                                        lastQtyOut = r2.result.qty_out;
                                        lastReturQty = r2.result.qty_out_stock;

                                        var htmlString = '<tr>'+
                                            '<td>'+r2.result.no_bukti+'</td>'+
                                            '<td style="text-align:center;">'+r2.result.qty_out_stock+'</td>'+
                                            '<td style="text-align:center;">'+
                                                '<input type="number" value="1" class="total-retur" style="text-align:center;width:80%;" min="1" max="'+r2.result.qty_out_stock+'" class="qty_retur" onkeypress="return event.charCode >= 48 && event.charCode <= 57"/></td>'+
                                            '<td><a href="" class="btn btn-danger btn-sm remove-retur"><i class="fa fa-fw fa-close"></i><span><span></a></td>'+
                                        '</tr>';

                                        $("#tbl-retur tbody").append(htmlString);

                                        $(".total-retur").on("keyup keypress blur change", function() {
                                            var qtyReturInput = parseInt($(this).val());
                                            var qtyReturMax = $(this).prop("max");
                                            if (qtyReturInput > qtyReturMax)
                                                $(this).val("1");
                                        });

                                        $(".remove-retur").bind("click", function(e) {
                                            e.preventDefault();
                                            $(".total-retur").prop("readonly", false);
                                            $(this).parent().parent().remove();

                                            lastQtyOut = r.result.qty_out;
                                            lastReturQty = r.result.qty_out_stock;
                                            returPage--;
                                        });
                                    } else 
                                        notifLobi("error", "top right", "Qty retur "+kodeMaterial+" tidak tersedia lagi");
                                }
                             });
                        }
                    });
                }
                else {
                    var htmlString = '<tr>'+
                        '<td colspan="4" style="text-align:center;">QTY Retur '+kodeMaterial+' tidak tersedia</td>'+
                    '</tr>';

                    $("#tbl-retur tbody").append(htmlString);
                }
                    
            }
        });
    }

    // change material
    $("#cbReturMaterial").change(function() {
        onChangeReturMaterial();
    });

    // control btn Retur
    $("#btnRetur").click(function(e) {
        e.preventDefault();
        
        row = $("#dgRequest").datagrid("getSelected");

        noPkb = row.hiddenPkb.trim();
        noSO = row.hiddenNoSo.trim();
        dataMaterial = JSON.parse(row.hiddenData.trim());

        returPage = 1;
        // get no_bukti retur
        var param = {
            type: "BA-RETUR",
            token: TOKEN
        };
        $.ajax({
            method: "POST",
            url: API_URL + "util/autonumber/create",
            dataType: "JSON",
            data: JSON.stringify(param),
            success: function (r) {
                if (r.success) {
                    $("#inputMNoBuktiRetur").val(r.result.auto_number);
                    onChangeReturMaterial();
                }
            }
        });

        var totalMaterial = dataMaterial.length;
        $("#cbReturMaterial").html("");
        for (i = 0; i < totalMaterial; i++) {
            $("#cbReturMaterial").append("<option value='"+dataMaterial[i].kode_material+"' data-jenis='"+dataMaterial[i].jenis_material+"'>"+dataMaterial[i].kode_material+" "+dataMaterial[i].jenis_material+"</option>");
        }

        $("#modalRetur").modal("show");
        //console.log(dataMaterial);
    });

    // control btn Simpan Retur
    $("#btnSimpanRetur").click(function(e) {
        e.preventDefault();

        var peretur = $("#inputMPeretur").val().trim().toUpperCase();
        var qtyRetur = 0;
        var detail = [];

        $(".total-retur").each(function(index, el) {
            qtyRetur += parseInt($(el).val());
            detail.push({
                no_bukti_qout: $(el).parent().prev().prev().html(),
                qty_retur: $(el).val()
            });
        });

        if (peretur.length == 0)
            notifLobi("error", "top right", "Nama peretur harus diisi");
        else if (qtyRetur == 0)
            notifLobi("error", "top right", "Qty retur harus diisi");
        else {
            var param = {
                no_so: noSO,
                no_bukti_retur: $("#inputMNoBuktiRetur").val(),
                no_bukti_pkb: noPkb,
                tgl: $("#inputMTglRetur").val(),
                peretur: peretur,
                kode_material: $("#cbReturMaterial option:selected").val(),
                jenis_material: $("#cbReturMaterial option:selected").attr("data-jenis"),
                detail: detail,
                token: TOKEN
            }

            $.ajax({
                method: "POST",
                url: API_URL + "stock/qty_retur_input",
                dataType: "JSON",
                data: JSON.stringify(param),
                success: function (r) {
                    console.log(r);
                    if (r.success) {
                        notifLobi("success", "top right", r.message);
                        $("#modalRetur").modal("hide");
                        setTimeout(function() {
                            window.location = WEB_URL + "stock/pengeluaran/material/request";
                        }, 5000);
                    }
                }
            });

            //console.log(param);
        }
    });

    // --------------------------------- KERUGIAN MATERIAL --------------------------------- //
    function onChangeKerugianMaterial() {
        $("#tbl-kerugian tbody").html("");
        returPage = 1;
        // get available qty out
        var kodeMaterial = $("#cbKerugianMaterial").val();
        var param = {
            pkb_no_bukti: noPkb,
            kode_material: kodeMaterial,
            page: returPage,
            token: TOKEN
        };

        $.ajax({
            method: "POST",
            url: API_URL + "stock/available_qout",
            dataType: "JSON",
            data: JSON.stringify(param),
            success: function (r) {
                //console.log(r);
                if (r.success) {
                    lastQtyOut = r.result.qty_out;
                    lastReturQty = r.result.qty_out_stock;

                    var htmlString = '<tr>'+
                        '<td>'+r.result.no_bukti+'</td>'+
                        '<td style="text-align:center;">'+r.result.qty_out_stock+'</td>'+
                        '<td style="text-align:center;">'+
                            '<input type="number" class="total-kerugian qty_loss" value="1" style="text-align:center;width:80%;" min="1" max="'+r.result.qty_out_stock+'" onkeypress="return event.charCode >= 48 && event.charCode <= 57"/></td>'+
                        '<td><a href="" class="btn btn-default btn-sm add-kerugian"><i class="fa fa-fw fa-plus"></i><span><span></a></td>'+
                    '</tr>';

                    $("#tbl-kerugian tbody").append(htmlString);

                    $(".total-kerugian").on("keyup keypress blur change", function() {
                        var qtyReturInput = parseInt($(this).val());
                        var qtyReturMax = $(this).prop("max");
                        if (qtyReturInput > qtyReturMax)
                            $(this).val("1");
                    });

                    $(".add-kerugian").bind("click", function(e) {
                        e.preventDefault();
                        var totalRetur = $(".total-kerugian").length;
                        var lastRetur = $(".total-kerugian")[totalRetur - 1];

                        var lastReturVal = ($(lastRetur).val().length == 0) ? 0 : $(lastRetur).val();
                        var currentQtyRetur = parseInt(lastReturVal);

                        var qty_loss = parseInt($("#cbKerugianMaterial option:selected").attr("data-rugi"));
                        
                        // check dulu qty loss nya apakah sudah maksimal
                        var totalReqLoss = 0;
                        $(".qty_loss").each(function(index, el) {
                            totalReqLoss += parseInt($(el).val());
                        });

                        if (totalReqLoss >= qty_loss)
                            notifLobi("error", "top right", "Total input loss lebih besar dari kerugian");
                        else if (currentQtyRetur > lastReturQty)
                            notifLobi("error", "top right", "Stock qty_out lebih besar dari kerugian");
                        else if (currentQtyRetur < lastReturQty)
                            notifLobi("error", "top right", "Habiskan dulu stock qty_out");
                        else {
                            $(".total-kerugian").prop("readonly", true);
                            returPage++;
                            var param = {
                                pkb_no_bukti: noPkb,
                                kode_material: kodeMaterial,
                                page: returPage,
                                token: TOKEN
                            };

                             $.ajax({
                                method: "POST",
                                url: API_URL + "stock/available_qout",
                                dataType: "JSON",
                                data: JSON.stringify(param),
                                success: function (r2) {
                                    if (r2.success) {
                                        lastQtyOut = r2.result.qty_out;
                                        lastReturQty = r2.result.qty_out_stock;

                                        var htmlString = '<tr>'+
                                            '<td>'+r2.result.no_bukti+'</td>'+
                                            '<td style="text-align:center;">'+r2.result.qty_out_stock+'</td>'+
                                            '<td style="text-align:center;">'+
                                                '<input type="number" value="1" class="total-kerugian qty_loss" style="text-align:center;width:80%;" min="1" max="'+r2.result.qty_out_stock+'" onkeypress="return event.charCode >= 48 && event.charCode <= 57"/></td>'+
                                            '<td><a href="" class="btn btn-danger btn-sm remove-kerugian"><i class="fa fa-fw fa-close"></i><span><span></a></td>'+
                                        '</tr>';

                                        $("#tbl-kerugian tbody").append(htmlString);

                                        $(".total-kerugian").on("keyup keypress blur change", function() {
                                            var qtyReturInput = parseInt($(this).val());
                                            var qtyReturMax = $(this).prop("max");
                                            if (qtyReturInput > qtyReturMax)
                                                $(this).val("1");
                                        });

                                        $(".remove-kerugian").bind("click", function(e) {
                                            e.preventDefault();
                                            $(".total-kerugian").prop("readonly", false);
                                            $(this).parent().parent().remove();

                                            lastQtyOut = r.result.qty_out;
                                            lastReturQty = r.result.qty_out_stock;
                                            returPage--;
                                        });
                                    } else 
                                        notifLobi("error", "top right", "Qty kerugian "+kodeMaterial+" tidak tersedia lagi");
                                }
                             });
                        }
                    });
                }
                else {
                    var htmlString = '<tr>'+
                        '<td colspan="4" style="text-align:center;">QTY Retur '+kodeMaterial+' tidak tersedia</td>'+
                    '</tr>';

                    $("#tbl-retur tbody").append(htmlString);
                }
                    
            }
        });
    }

    // control change kerugian material
    $("#cbKerugianMaterial").change(function() {
        onChangeKerugianMaterial();
    });

    // control btn kerugion
    $("#btnKerugian").click(function(e) {
        e.preventDefault();

        row = $("#dgRequest").datagrid("getSelected");

        noPkb = row.hiddenPkb.trim();
        noSO = row.hiddenNoSo.trim();
        noPkt = row.hiddenPkt.trim();

        // check apakah pkt sudah dilakukan
        if (noPkt.length > 0) {
            // check dulu apakah pkb ini mengalami kerugian di pkt
            var param = {
                no_pkb: noPkb,
                token: TOKEN
            };

            $.ajax({
                method: "POST",
                dataType: "JSON",
                data: JSON.stringify(param),
                url: API_URL + "aktivasi/so/pkt/is_pkb_rugi",
                success: function (r) {
                    //console.log(r);
                    if (r.result.kerugian) {
                        // get list material yang mengalami kerugian
                        var totalMaterial = r.result.data.length;
                        $("#cbKerugianMaterial").html("");
                        for (i = 0; i < totalMaterial; i++) {
                            $("#cbKerugianMaterial").append("<option value='"+r.result.data[i].kode_material+"' data-rugi='"+r.result.data[i].qty_loss+"' data-jenis='"+r.result.data[i].jenis_material+"'>"+r.result.data[i].kode_material+" "+r.result.data[i].jenis_material+"</option>");
                        }

                        // apply get available qty_out
                        onChangeKerugianMaterial();

                        // open modal list material yg bisa dimasukan kepada kerugian
                        $("#modalKerugian").modal("show");

                        // load no BA-KERUGIAN
                        var paramNoBa = {
                            type: "BA-KERUGIAN",
                            token: TOKEN
                        }

                        $.ajax({
                            method: "POST",
                            dataType: "JSON",
                            data: JSON.stringify(paramNoBa),
                            url: API_URL + "util/autonumber/create",
                            success: function (r) {
                                if (r.success)
                                    $("#inputMNoBuktiKerugian").val(r.result.auto_number);
                            }
                        });
                    } else 
                        notifLobi("error", "top right", r.message);
                }
            });
        } else 
            notifLobi("error", "top right", "Proses PKT belum dilakukan");

    });

    // control btn simpan kerugian
    $("#btnSimpanKerugian").click(function(e) {
        e.preventDefault();

        var noBaps = $("#inputMNoBaps").val().trim().toUpperCase();
        var qtyLoss = 0;
        var detail = [];

        var qty_loss = parseInt($("#cbKerugianMaterial option:selected").attr("data-rugi"));
                        
        // check dulu qty loss nya apakah sudah maksimal
        var totalReqLoss = 0;
        $(".qty_loss").each(function(index, el) {
            totalReqLoss += parseInt($(el).val());
        });

        $(".total-kerugian").each(function(index, el) {
            qtyLoss += parseInt($(el).val());
            detail.push({
                no_bukti_qout: $(el).parent().prev().prev().html(),
                qty_loss: $(el).val()
            });
        });

        if (totalReqLoss < qty_loss)
            notifLobi("error", "top right", "Total input loss "+totalReqLoss+"  masih kurang dari kalkulasi kerugian "+qty_loss);
        else if (totalReqLoss > qty_loss)
            notifLobi("error", "top right", "Total input loss "+totalReqLoss+"  lebih besar dari kalkulasi kerugian "+qty_loss);
        else if (noBaps.length == 0)
            notifLobi("error", "top right", "No. Baps harus diisi");
        else if (qtyLoss == 0)
            notifLobi("error", "top right", "Qty loss harus diisi");
        else {

            var param = {
                no_so: noSO,
                no_bukti_kerugian: $("#inputMNoBuktiKerugian").val(),
                no_bukti_pkb: noPkb,
                tgl: $("#inputMTglRetur").val(),
                no_baps: noBaps,
                kode_material: $("#cbKerugianMaterial option:selected").val(),
                jenis_material: $("#cbKerugianMaterial option:selected").attr("data-jenis"),
                detail: detail,
                token: TOKEN
            }

            $.ajax({
                method: "POST",
                url: API_URL + "stock/qty_loss_input",
                dataType: "JSON",
                data: JSON.stringify(param),
                success: function (r) {
                    console.log(r);
                    if (r.success) {
                        notifLobi("success", "top right", r.message);
                        $("#modalKerugian").modal("hide");
                        setTimeout(function() {
                            window.location = WEB_URL + "stock/pengeluaran/material/request";
                        }, 5000);
                    }
                }
            });

            //console.log(param);
        }
    });
});