$(function() {

    // control show/hide new add form
    $("#btnBaru").click(function(e) {
        e.preventDefault();

        if ($("#btnBaru span").text() == "BARU") {
            $("#inputNo").removeAttr("readonly");
            $("#btnSimpan").removeAttr("disabled");
            $("#btnSimpan").attr("data-action", "new");
            $("#btnBaru i").removeClass("fa-plus").addClass("fa-ban");
            $("#btnBaru span").text("BATAL");
            //$("#box-form").slideDown("fast");
            $("#box-form-title").text("BUAT NO. HP BARU");
        } else {
            $("#inputNo").attr("readonly", "true");
            $("#inputNo").val("");
            $("#btnSimpan").attr("disabled", "true");
            $("#btnBaru i").removeClass("fa-ban").addClass("fa-plus");
            $("#btnBaru span").text("BARU");
            //$("#box-form").slideUp("fast");
            $("#box-form-title").text("FORM NO. KARTU HP");
        }
    });

    // control insert new data
    $("#btnSimpan").click(function(e) {
        
        if ($("#btnSimpan").attr("disabled") != "disabled") {

            var btnAction = $("#btnSimpan").attr("data-action");

            if (btnAction == "new") {
                var param = {
                    no_kartu: $("#inputNo").val(),
                    token: TOKEN
                };

                $.ajax({
                    url: API_URL + "referensi/hp/add",
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
                                '<td style="text-align:center"><span id="text-'+r.result.no_kartu+'">'+r.result.no_kartu+'</span></td>'+
                                '<td></td>'+
                                '<td style="text-align:right;">'+
                                    '<button class="btn btn-default btn-xs btnRefEdit" data-kode="'+r.result.no_kartu+'"><i class="fa fa-edit"></i></button>'+
                                    '<button class="btn btn-default btn-xs btnRefDelete" data-kode="'+r.result.no_kartu+'"><i class="fa fa-trash-o"></i></button>'+
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
                    new_no_kartu: $("#inputNo").val(),
                    token: TOKEN
                };

                var oldeno = $("#inputOldno").val();

                $.ajax({
                    url: API_URL + "referensi/hp/edit/" + oldeno,
                    method: "POST",
                    dataType: "JSON",
                    data: JSON.stringify(param),
                    success: function(r) {

                        if (!r.success)
                            notifLobi("error", "top right", r.message);
                        else {
                            $("#btnBaru").trigger("click");
                            notifLobi("success", "top right", r.message);                          

                            $("#text-"+oldeno).text(r.result.no_kartu);
                            $("#text-"+oldeno).attr("id", "text-"+r.result.no_kartu);
                            $("[data-kode="+oldeno+"]").attr("data-kode", r.result.no_kartu);
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
            window.location.href = WEB_URL+"kansi/referensi/hp?key="+$("#keySearch").val();
    });

    // control button clear search
    $("#btnClearSearch").click(function(e) {
        e.preventDefault();
        window.location.href = WEB_URL+"kansi/referensi/hp";
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

        $("#inputNo").removeAttr("readonly");
        $("#inputNo").val($(this).attr("data-kode"));
        $("#inputOldno").val($(this).attr("data-kode"));
        
        $("#box-form-title").text("EDIT NO. HP");
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
            content: "<div style='text-align:center'>Apakah no hp "+kode+" akan dihapus?</div>",
            buttons: {
                batal: function () {
                },
                hapus: function () {
                    var param = {
                        token: TOKEN
                    };

                    $.ajax({
                        url: API_URL + "referensi/hp/delete/" + kode,
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