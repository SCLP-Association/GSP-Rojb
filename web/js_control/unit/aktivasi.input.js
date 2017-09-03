$(function() {
    // init jenis biaya select 2
    $("#inputKode").select2();

    // control form input
    $("#form-aktivasi").submit(function() {
        var inputVal = "";
        var formError = false;
        var kode = $("#inputKode").val();

        $("#form-aktivasi input").each(function(index, element) {
            inputVal = $(element).val().trim();
            if (inputVal == "" && !$(element).prop("disabled")) {
                formError = true;
            }
        });

        if (formError) {
            notifLobi("error", "top right", "Form masih ada yang kosong");
            return false;
        }

        isMinimumPrice = true;
        if (kode == "B1700") {
            $(".price").each(function(index, element) {
                var price = parseInt($(element).val().replace(/\./g,''));
                if (price < 3000000)
                    isMinimumPrice = false;
            });
        }

        if (!isMinimumPrice) {
            notifLobi("error", "top right", "Harga beli minimal 3 juta rupiah (diluar ppn)");
            return false;
        }
        
        var param = $("#form-aktivasi").serialize();
        $.ajax({
            type: "POST",
            url: WEB_URL + "unit/aktivasi/do_input",
            data: param,
            dataType: "JSON",
            success: function(r) {
                if (r.success) {
                    $('#inputKode').val("B0100").change();
                    // load no bukti aktivasi baru, no kuitansi baru, tgl baru
                    $.ajax({
                        type: "POST",
                        dataType: "JSON",
                        url: WEB_URL + "unit/aktivasi/load_no_bukti_baru",
                        success: function(rc) {
                            $("#inputNobukti").val(rc.no_bukti);
                            $("#inputMNoKuitansi").val(rc.no_kuitansi);
                            $("#inputTanggal").val(rc.tgl);
                            $("#inputTanggalView").val(rc.tgl_view);
                            $("#inputMTglKuitansi").val(rc.tgl);
                            $("#inputMTglKuitansiView").val(rc.tgl_view);
                        }
                    });

                    notifLobi("success", "top right", r.message);
                } else
                    notifLobi("error", "top right", r.message);
            }
        });
        return false;
    });

    // init first biaya
    setTimeout(function() {
        $('#inputKode').val("B0100").change();
    }, 300);

    // control jenis biaya on change
    $("#inputKode").change(function() {
        var kode = $(this).val();
        var jenis = $("#inputKode :selected").text().replace(kode + ". ", "");
        $("#inputJenis").val(jenis);

        // get tipe template biaya
        $.get(WEB_URL + "unit/aktivasi/input_load_template/"+kode, function(data) {

            $("#template-biaya").html(data);

            $(".price").maskMoney({thousands:'.', decimal:',', precision: 0, allowZero: false, suffix: '', prefix: ''});
            $(".price").css("text-align", "right");
            
            if (kode == "B1100") {
                $(".parkir").bind("click", function() {
                    var parkTipe = $(this).val();
                    if (parkTipe == "E-TOL") {
                        $(this).parent().parent().find(".etol_field").show();
                        $(this).parent().parent().find(".tol_field").hide();
                        $(this).parent().parent().find(".tol_field input").attr("disabled", "true");
                    }

                    if (parkTipe == "TOL") {
                        $(this).parent().parent().find(".etol_field").hide();
                        $(this).parent().parent().find(".tol_field").show();
                        $(this).parent().parent().find(".tol_field input").removeAttr("disabled");
                    }

                    if (parkTipe == "PARKIR") {
                        $(this).parent().parent().find(".etol_field").hide();
                        $(this).parent().parent().find(".tol_field").hide();
                        $(this).parent().parent().find(".tol_field input").attr("disabled", "true");
                    }
                });
            }

            setTimeout(function() {
                $("#addrow").unbind("click");

                $("#addrow").bind("click", function(e) {
                    e.preventDefault();

                    if (kode == "B0200") topValue = $("[name=kendaraan\\[0\\]\\[\\]]:checked").val();
                    if (kode == "B0500") topValue = $("[name=pulsa\\[0\\]\\[\\]]:checked").val();
                    if (kode == "B0600") topValue = $("[name=listrik\\[0\\]\\[\\]]:checked").val();
                    if (kode == "B1100") topValue = $("[name=parkir\\[0\\]\\[\\]]:checked").val();

                    newrow = $("#b0template").clone();
                    newid = guid();             

                    $(newrow).attr("id", "b0template" + newid);

                    if ($(".btemplate").length == 1)
                        previd = "";

                    $($("#b0template" + previd)).after(newrow);

                    $(newrow).find("#addrow").remove();

                    $("#b0template" + newid + " input").val("");
                    
                    $(".price").maskMoney({thousands:'.', decimal:',', precision: 0, allowZero: false, suffix: '', prefix: ''});
                    $(".price").css("text-align", "right");

                    if (kode == "B0200") {
                        var radioElem = $(newrow).find(".kendaraan");
                        $(radioElem[0]).val("MOBIL").prop("checked", "true");
                        $(radioElem[1]).val("MOTOR");
                        $(newrow).find(".kendaraan").attr("name", "kendaraan[" + newid + "][]");

                        setTimeout(function() {
                            $("[name=kendaraan\\[0\\]\\[\\]]").filter('[value="'+topValue+'"]').prop("checked", true);
                        }, 100);
                    }

                    if (kode == "B0500") {
                        var radioElem = $(newrow).find(".pulsa");
                        $(radioElem[0]).val("PULSA");
                        $(radioElem[1]).val("MODEM");
                        $(newrow).find(".pulsa").attr("name", "pulsa[" + newid + "][]");

                        setTimeout(function() {
                            $("[name=pulsa\\[0\\]\\[\\]]").filter('[value="'+topValue+'"]').prop("checked", true);
                        }, 100);
                    }

                    if (kode == "B0600") {
                        var radioElem = $(newrow).find(".listrik");
                        $(radioElem[0]).val("LISTRIK");
                        $(radioElem[1]).val("AIR");
                        $(newrow).find(".listrik").attr("name", "listrik[" + newid + "][]");
                        setTimeout(function() {
                            $("[name=listrik\\[0\\]\\[\\]]").filter('[value="'+topValue+'"]').prop("checked", true);
                        }, 100);
                    }

                    $(".parkir").unbind("click");
                    if (kode == "B1100") {
                        var radioElem = $(newrow).find(".parkir");
                        $(radioElem[0]).val("E-TOL");
                        $(radioElem[1]).val("TOL");
                        $(radioElem[2]).val("PARKIR");
                        $(newrow).find(".parkir").attr("name", "parkir[" + newid + "][]");

                        // bind click to radio button
                        $(".parkir").unbind("click");
                        $(".parkir").bind("click", function() {
                            var parkTipe = $(this).val();
                            if (parkTipe == "E-TOL") {
                                $(this).parent().parent().find(".etol_field").show();
                                $(this).parent().parent().find(".tol_field").hide();
                                $(this).parent().parent().find(".tol_field input").attr("disabled", "true");
                            }

                            if (parkTipe == "TOL") {
                                $(this).parent().parent().find(".etol_field").hide();
                                $(this).parent().parent().find(".tol_field").show();
                                $(this).parent().parent().find(".tol_field input").removeAttr("disabled");
                            }

                            if (parkTipe == "PARKIR") {
                                $(this).parent().parent().find(".etol_field").hide();
                                $(this).parent().parent().find(".tol_field").hide();
                                $(this).parent().parent().find(".tol_field input").attr("disabled", "true");
                            }
                        });

                        setTimeout(function() {
                            $("[name=parkir\\[0\\]\\[\\]]").filter('[value="'+topValue+'"]').prop("checked", true);
                        }, 100);
                    }

                    // bind delete row
                    $(newrow).find("#deleterow").attr("id", "deleterow-" + newid);
                    $("#deleterow-" + newid).show();
                    $("#deleterow-" + newid).bind("click", function() {
                        $(this).parent().parent().remove();
                        var total = $(".btemplate").length;

                        if (total == 1) {
                            previd = "";
                        } else {
                            var lastElement = $(".btemplate")[total - 1];
                            previd = $(lastElement).find(".initdel").attr("id").replace("deleterow-", "");
                        }
                        return false;
                    });

                    previd = newid;
                    return false;
                });
            }, 500);
        });
    });

    // control btn simpan kuitansi
    $("#btnSimpanKuitansi").click(function() {
        var error = false;
        if ($("#inputMPenerimaKuitansi").val().length == 0) {
            $("#inputMPenerimaKuitansi").parent().parent().addClass("has-error");
            $("#inputMPenerimaKuitansi").attr("placeholder", "PENERIMA HARUS DIISI");
            error = true;
        }

        if ($("#inputMIdKuitansi").val().length == 0) {
            $("#inputMIdKuitansi").parent().parent().addClass("has-error");
            $("#inputMIdKuitansi").attr("placeholder", "ID HARUS DIISI");
            error = true;
        }

        if (!error) {
            $("#inputNoKuitansi").val($("#inputMNoKuitansi").val());
            $("#inputTglKuitansi").val($("#inputMTglKuitansi").val());
            $("#inputAdminKuitansi").val($("#inputMAdminKuitansi").val());
            $("#inputPenerimaKuitansi").val($("#inputMPenerimaKuitansi").val().toUpperCase());
            $("#inputIdKuitansi").val($("#inputMIdKuitansi").val().toUpperCase());
            $("#modalKuitansi").modal("hide");
            notifLobi("info", "top right", "Kuitansi tersimpan sementara!");

            $("#btnStatusKuitansi").removeClass("btn-warning").addClass("btn-default");
            $("#btnStatusKuitansi span").text("KUITANSI SEMENTARA SUDAH DIBUAT");
            $("#btnStatusKuitansi i").removeClass("fa-ban").addClass("fa-check");
        }
    });

    // control on close modal kuitansi
    $("#modalKuitansi").on('hidden.bs.modal', function () {
        $("#inputMIdKuitansi, #inputMPenerimaKuitansi").parent().parent().removeClass("has-error");
        $("#inputMIdKuitansi").attr("placeholder", "ID");
        $("#inputMPenerimaKuitansi").attr("placeholder", "PENERIMA");
    });

    // control batalkan kuitansi
    $("#btnCancelKuitansi").click(function() {
        $("#inputNoKuitansi, #inputTglKuitansi, #inputAdminKuitansi, #inputPenerimaKuitansi, #inputIdKuitansi").val("0");
        notifLobi("warning", "top right", "Kuitansi dibatalkan!");

        $("#inputMIdKuitansi, #inputMPenerimaKuitansi").val("");
        $("#btnStatusKuitansi").removeClass("btn-default").addClass("btn-warning");
        $("#btnStatusKuitansi span").text("KUITANSI BELUM DIBUAT");
        $("#btnStatusKuitansi i").removeClass("fa-check").addClass("fa-ban");
    });
});