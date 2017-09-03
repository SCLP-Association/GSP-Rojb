$(function() {

    // control show/hide new add form
    $("#btnBaru").click(function(e) {
        e.preventDefault();

        if ($("#btnBaru span").text() == "BARU") {
            // kode regional input manual
            $("#btnSimpan").removeAttr("disabled");
            $("#btnSimpan").attr("data-action", "new");
            $("#btnBaru i").removeClass("fa-plus").addClass("fa-ban");
            $("#btnBaru span").text("BATAL");
            //$("#box-form").slideDown("fast");
            $("#box-form-title").text("BUAT MATERIAL REGIONAL BARU");
            $("#inputKode").removeAttr("readonly");
        } else {
            $("#inputKode, #inputJenis").val("");
            $("#btnSimpan").attr("disabled", "true");
            $("#btnBaru i").removeClass("fa-ban").addClass("fa-plus");
            $("#btnBaru span").text("BARU");
            //$("#box-form").slideUp("fast");
            $("#box-form-title").text("FORM MATERIAL REGIONAL");
            $("#inputKode").attr("readonly", "true");
        }
    });

    // control insert new data
    $("#btnSimpan").click(function(e) {
        
        if ($("#btnSimpan").attr("disabled") != "disabled") {

            var btnAction = $("#btnSimpan").attr("data-action");

            if (btnAction == "new") {
                var param = {
                    kode: $("#inputKode").val().trim().toUpperCase(),
                    nama: $("#inputNama").val().trim().toUpperCase(),
                    token: TOKEN
                };

                $.ajax({
                    url: API_URL + "referensi/material/regional/add",
                    method: "POST",
                    dataType: "JSON",
                    data: JSON.stringify(param),
                    success: function(r) {

                        if (!r.success)
                            notifLobi("error", "top right", r.message);
                        else {
                            $("#btnBaru").trigger("click");
                            notifLobi("success", "top right", r.message);

                            // append table
                            var newRow = '<tr>'+
                                '<td style="text-align:center;"><span id="kode-'+r.result.kode+'">'+r.result.kode+'</span></td>'+
                                '<td><span id="text-'+r.result.kode+'">'+r.result.nama+'</span></td>'+
                                '<td style="text-align:right;">'+
                                    '<button class="btn btn-default btn-xs btnRefEdit" data-kode="'+r.result.kode+'" data-jenis="'+r.result.wilayah+'"><i class="fa fa-edit"></i></button> '+
                                    '<button class="btn btn-default btn-xs btnRefDelete" data-kode="'+r.result.kode+'"><i class="fa fa-trash-o"></i></button>'+
                                '</td>'+
                            '</tr>';
                            $("#tb-ref").append(newRow);

                            bindEdit();                            
                            bindDelete();
                        }
                    }
                });
            } else if (btnAction == "edit") {

                var old_code = $("#inputKodeHidden").val();
                var param = {
                    new_kode: $("#inputKode").val().trim().toUpperCase(),
                    nama: $("#inputNama").val().trim().toUpperCase(),
                    token: TOKEN
                };

                $.ajax({
                    url: API_URL + "referensi/material/regional/edit/" + $("#inputKodeHidden").val(),
                    method: "POST",
                    dataType: "JSON",
                    data: JSON.stringify(param),
                    success: function(r) {

                        if (!r.success)
                            notifLobi("error", "top right", r.message);
                        else {
                            $("#btnBaru").trigger("click");
                            notifLobi("success", "top right", r.message);                          

                            $("#kode-"+old_code).text(r.result.kode);
                            $("#text-"+old_code).text(r.result.nama);

                            $("#kode-"+old_code).attr("id", "kode-"+r.result.kode);
                            $("#text-"+old_code).attr("id", "text-"+r.result.nama);
                            
                            $("[data-kode="+old_code+"]").attr("data-jenis", r.result.nama);
                            $("[data-kode="+old_code+"]").attr("data-kode", r.result.kode);
                            bindEdit();
                        }
                    }
                });
            }
        }
    });

    // control button search
    $("#btnSearch").click(function(e) {
        e.preventDefault();
        if ($("#keySearch").val().length > 0)
            window.location.href = WEB_URL+"kansi/referensi/material_regional?key="+$("#keySearch").val();
    });

    // control button clear search
    $("#btnClearSearch").click(function(e) {
        e.preventDefault();
        window.location.href = WEB_URL+"kansi/referensi/regional";
    });

    // control key search value enter key
    $("#keySearch").keypress(function(e) {
        if(e.which == 13) 
            $("#btnSearch").trigger("click");
    });
});


// control edit button
function bindEdit() {
    $(".btnRefEdit").bind("click", function(e) {

        e.preventDefault();
        $("html, body").animate({ scrollTop: 0 }, "fast");
        
        $("#btnSimpan").removeAttr("disabled");
        $("#btnSimpan").attr("data-action", "edit");
        $("#btnBaru i").removeClass("fa-plus").addClass("fa-ban");
        $("#btnBaru span").text("BATAL");
        $("#box-form").slideDown("fast");

        $("#inputKode, #inputKodeHidden").val($(this).attr("data-kode"));
        $("#inputNama").val($(this).attr("data-jenis"));
        
        $("#box-form-title").text("EDIT MATERIAL REGIONAL");
        $("#inputKode").removeAttr("readonly");
    });
}

bindEdit();

// control delete button 
function bindDelete() {
    $(".btnRefDelete").bind("click", function(e) {
        e.preventDefault();
        var element = $(this);
        var kode = element.attr("data-kode");
        $.confirm({
            title: "Konfirmasi",
            content: "<div style='text-align:center'>Apakah material regional "+kode+" akan dihapus?</div>",
            buttons: {
                batal: function () {
                },
                hapus: function () {
                    var param = {
                        token: TOKEN
                    };

                    $.ajax({
                        url: API_URL + "referensi/material/regional/delete/" + kode,
                        method: "POST",
                        dataType: "JSON",
                        data: JSON.stringify(param),
                        success: function(r) {
                            if (!r.success)
                                notifLobi("error", "top right", r.message);
                            else {
                                element.parent().parent().remove();
                                notifLobi("success", "top right", r.message);
                            }
                        }
                    });
                }
            }
        });
    });
}

bindDelete();