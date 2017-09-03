$(function() {

    // control show/hide new add form
    $("#btnBaru").click(function(e) {
        e.preventDefault();

        if ($("#btnBaru span").text() == "BARU") {
            $("#inputRole").removeAttr("readonly");
            $("#inputRole").removeAttr("disabled");
            $("#btnSimpan").removeAttr("disabled");
            $("#btnSimpan").attr("data-action", "new");
            $("#btnBaru i").removeClass("fa-plus").addClass("fa-ban");
            $("#btnBaru span").text("BATAL");
            //$("#box-form").slideDown("fast");
            $("#box-form-title").text("BUAT USER BARU");
        } else {
            $("#inputRole").attr("readonly", "true");
            $("#inputRole").attr("disabled", "true");
            $("#inputKode, #inputUsername, #inputPassword, #inputName, #inputEmail").val("");
            $("#btnSimpan").attr("disabled", "true");
            $("#btnBaru i").removeClass("fa-ban").addClass("fa-plus");
            $("#btnBaru span").text("BARU");
            //$("#box-form").slideUp("fast");
            $("#box-form-title").text("FORM DAFTAR USER");
        }
    });

    // control insert new data
    $("#btnSimpan").click(function(e) {
        
        if ($("#btnSimpan").attr("disabled") != "disabled") {

            var btnAction = $("#btnSimpan").attr("data-action");

            if (btnAction == "new") {
                var param = {
                    username: $("#inputUsername").val(),
                    password: $("#inputPassword").val(),
                    full_name: $("#inputName").val(),
                    email: $("#inputEmail").val(),
                    role: $("#inputRole").val(),
                    regional: $("#inputRole").val(),
                    token: TOKEN
                };

                $.ajax({
                    url: API_URL + "user/create",
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
                                '<td style="text-align:center;">'+r.result.role+'</td>'+
                                '<td><span id="text-'+r.result.id+'">'+r.result.username+'</span></td>'+
                                '<td><span id="email-'+r.result.id+'">'+r.result.email+'</span></td>'+
                                '<td><span id="password-'+r.result.id+'">'+r.result.dpassword+'</span></td>'+
                                '<td style="text-align:right;">'+
                                    '<button class="btn btn-default btn-xs btnRefEdit" data-kode="'+r.result.id+'" data-jenis="'+r.result.username+'" data-password="'+r.result.dpassword+'" data-role="'+r.result.regional+'" data-email="'+r.result.email+'" data-fullname="'+r.result.full_name+'"><i class="fa fa-edit"></i></button>'+
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
                    username: $("#inputUsername").val(),
                    password: $("#inputPassword").val(),
                    email: $("#inputEmail").val(),
                    full_name: $("#inputName").val(),
                    role: $("#inputRole").val(),
                    regional: $("#inputRole").val(),
                    token: TOKEN
                };

                $.ajax({
                    url: API_URL + "user/edit/" + $("#inputId").val(),
                    method: "POST",
                    dataType: "JSON",
                    data: JSON.stringify(param),
                    success: function(r) {

                        if (!r.success)
                            notifLobi("error", "top right", r.message);
                        else {
                            $("#btnBaru").trigger("click");
                            notifLobi("success", "top right", r.message);                          

                            $("#text-"+r.result.id).text(r.result.username);
                            $("#email-"+r.result.id).text(r.result.email);
                            $("#password-"+r.result.id).text(r.result.dpassword);
                            $("[data-kode="+r.result.id+"]").attr("data-jenis", r.result.username);
                            $("[data-kode="+r.result.id+"]").attr("data-password", r.result.dpassword);
                            $("[data-kode="+r.result.id+"]").attr("data-role", r.result.regional);
                            $("[data-kode="+r.result.id+"]").attr("data-email", r.result.email);
                            $("[data-kode="+r.result.id+"]").attr("data-fullname", r.result.full_name);
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
            window.location.href = WEB_URL+"kansi/referensi/daftar_user?key="+$("#keySearch").val();
    });

    // control button clear search
    $("#btnClearSearch").click(function(e) {
        e.preventDefault();
        window.location.href = WEB_URL+"kansi/referensi/daftar_user";
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

        $("#inputRole").removeAttr("disabled");
        $("#inputRole").removeAttr("readonly");
        $("#inputRole").val($(this).attr("data-role"));
        //$("#inputRole option[value='"+$(this).attr("data-role")+"']").prop("selected", true);

        $("#inputId").val($(this).attr("data-kode"));
        $("#inputUsername").val($(this).attr("data-jenis"));
        $("#inputEmail").val($(this).attr("data-email"));
        $("#inputName").val($(this).attr("data-fullname"));
        $("#inputPassword").val($(this).attr("data-password"));
        
        $("#box-form-title").text("EDIT USER");
    });
}

bindEdit();

// control delete button 
function bindDelete() {
    $(".btnRefDelete").bind("click", function(e) {
        e.preventDefault();
        var element = $(this);
        var kode = element.attr("data-kode");
        var username = element.attr("data-username");
        $.confirm({
            title: "Konfirmasi",
            content: "<div style='text-align:center'>Apakah user "+username+" akan dihapus?</div>",
            buttons: {
                batal: function () {
                },
                hapus: function () {
                    var param = {
                        token: TOKEN
                    };

                    $.ajax({
                        url: API_URL + "user/delete/" + kode,
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