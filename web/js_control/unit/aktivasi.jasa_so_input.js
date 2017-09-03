$(function() {
    // init jenis jasa select 2
    $("#inputKode").select2();

    // apply harga to rupiah format
    maskRupiah("inputBiaya");

    // control form input
    $("#form-jasa-so").submit(function() {
        var inputVal = "";
        var formError = false;
        var kode = $("#inputKode").val();

        $("#form-jasa-so input").each(function(index, element) {
            inputVal = $(element).val().trim();
            if (inputVal == "" && !$(element).prop("disabled")) {
                formError = true;
            }
        });

        if (formError) {
            notifLobi("error", "top right", "Form masih ada yang kosong");
            return false;
        }

        var param = $("#form-jasa-so").serialize();
        $.ajax({
            type: "POST",
            url: WEB_URL + "unit/aktivasi/so/jasa/do_input",
            data: param,
            dataType: "JSON",
            success: function(r) {
                if (r.success) {
                    $("#form-jasa-so input, #form-jasa-so button").attr("disabled", true);

                    notifLobi("success", "top right", r.message);
                    setTimeout(function() {
                        window.location = WEB_URL + "unit/aktivasi/so/jasa/pengeluaran";
                    }, 5000);
                } else
                    notifLobi("error", "top right", r.message);
            }
        });
        return false;
    });

    // control jenis jasa change
    $("#inputKode").change(function() {
        var kode = $(this).val();
        var jenis = $("#inputKode :selected").text().replace(kode + ". ", "");
        $("#inputJenis").val(jenis);
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