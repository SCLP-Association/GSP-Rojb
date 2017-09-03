$(function() {

    // control show/hide new add form
    $("#btnBaru").click(function(e) {
        e.preventDefault();

        if ($("#btnBaru span").text() == "BARU") {

            $("#btnSimpan").removeAttr("disabled");
            $("#btnSimpan").attr("data-action", "new");
            $("#btnBaru i").removeClass("fa-plus").addClass("fa-ban");
            $("#btnBaru span").text("BATAL");
            //$("#box-form").slideDown("fast");
            $("#box-form-title").text("BUAT PELANGGAN BARU");
            $("#inputNama").removeAttr("readonly");
        } else {
            $("#inputNama, #inputAlamat").val("");
            $("#btnSimpan").attr("disabled", "true");
            $("#btnBaru i").removeClass("fa-ban").addClass("fa-plus");
            $("#btnBaru span").text("BARU");
            //$("#box-form").slideUp("fast");
            $("#box-form-title").text("FORM PELANGGAN");            
            $("#inputNama").attr("readonly", "true");
        }
    });

    // control insert new data
    $("#btnSimpan").click(function(e) {
        
        if ($("#btnSimpan").attr("disabled") != "disabled") {

            var btnAction = $("#btnSimpan").attr("data-action");

            if (btnAction == "new") {
                var param = {
                    nama: $("#inputNama").val(),
                    alamat: $("#inputAlamat").val(),
                    token: TOKEN
                };

                $.ajax({
                    url: API_URL + "referensi/pelanggan/add",
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
                                '<td></td>'+
                                '<td>'+r.result.nama+'</td>'+
                                '<td><span id="text-'+r.result.id+'">'+r.result.alamat+'</span></td>'+
                                '<td style="text-align:right;">'+
                                    '<button class="btn btn-default btn-xs btnRefEdit" data-kode="'+r.result.id+'" data-nama="'+r.result.nama+'" data-jenis="'+r.result.alamat+'"><i class="fa fa-edit"></i></button>'+
                                    '<button class="btn btn-default btn-xs btnRefDelete" data-kode="'+r.result.id+'"><i class="fa fa-trash-o"></i></button>'+
                                '</td>'+
                            '</tr>';
                            $("#tb-ref").append(newRow);

                            bindEdit();                            
                            bindDelete();
                        }
                    }
                });
            } else if (btnAction == "edit") {
                var param = {
                    nama: $("#inputNama").val(),
                    alamat: $("#inputAlamat").val(),
                    token: TOKEN
                };

                $.ajax({
                    url: API_URL + "referensi/pelanggan/edit/" + $("#inputId").val(),
                    method: "POST",
                    dataType: "JSON",
                    data: JSON.stringify(param),
                    success: function(r) {

                        if (!r.success)
                            notifLobi("error", "top right", r.message);
                        else {
                            $("#btnBaru").trigger("click");
                            notifLobi("success", "top right", r.message);                          

                            $("#nama-"+r.result.id).text(r.result.nama);
                            $("#text-"+r.result.id).text(r.result.alamat);
                            $("[data-kode="+r.result.id+"]").attr("data-nama", r.result.nama);
                            $("[data-kode="+r.result.id+"]").attr("data-jenis", r.result.alamat);
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
            window.location.href = WEB_URL+"kansi/referensi/pelanggan?key="+$("#keySearch").val();
    });

    // control button clear search
    $("#btnClearSearch").click(function(e) {
        e.preventDefault();
        window.location.href = WEB_URL+"kansi/referensi/pelanggan";
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

        $("#inputId").val($(this).attr("data-kode"));
        $("#inputNama").val($(this).attr("data-nama"));
        $("#inputAlamat").val($(this).attr("data-jenis"));

        $("#inputNama").removeAttr("readonly");
        
        $("#box-form-title").text("EDIT PELANGGAN");
    });
}

bindEdit();

// control delete button 
function bindDelete() {
    $(".btnRefDelete").bind("click", function(e) {
        e.preventDefault();
        var element = $(this);
        var id = element.attr("data-kode");
        var nama = $("#nama-"+id).text();
        $.confirm({
            title: "Konfirmasi",
            content: "<div style='text-align:center'>Apakah pelanggan "+nama+" akan dihapus?</div>",
            buttons: {
                batal: function () {
                },
                hapus: function () {
                    var param = {
                        token: TOKEN
                    };

                    $.ajax({
                        url: API_URL + "referensi/pelanggan/delete/" + id,
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