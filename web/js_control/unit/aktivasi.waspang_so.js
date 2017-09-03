var noSo;
var existNoBa;

function clickNoBa() {
    var no_ba = $(this).text().replace("/", "_");
    window.open(WEB_URL + "unit/aktivasi/so/waspang/ba/" + no_ba, "Cetak BA Waspang", "height=650,width=850");
    return false;
}

$(function() {
    // click button modal waspang
    $(".linkWaspang").click(function() {
        // load no ba
        var param = {
            type: "AKTIVASI-SO-BA-WASPANG",
            token: TOKEN
        };

        $.ajax({
            method: "POST",
            url: API_URL + "util/autonumber/create",
            dataType: "JSON",
            data: JSON.stringify(param),
            success: function (r) {
                if (r.success)
                    $("#inputMNoBa").val(r.result.auto_number);
            }
        });

        var dataMaterial = JSON.parse($(this).parent().parent().find(".dataMaterial").val());

        $("#table-material-waspang tr").not(':first').remove();

        // create table material
        var newRowTable = "";
        for (i = 0; i < dataMaterial.length; i++) {
            newRowTable += "<tr>";
            newRowTable += "<td>"+dataMaterial[i].kode_material+" "+dataMaterial[i].jenis_material+"</td>";
            newRowTable += "<td style='text-align:center;'>"+dataMaterial[i].qty+"</td>";
            newRowTable += "</tr>";
        }

        $("#table-material-waspang").append(newRowTable);

        var noPkb = $(this).text();
        noSo = $(this).parent().parent().find(".noSo").text().trim();
        existNoBa = $(this).parent().parent().find(".existNoBa").text().trim();

        $("#spanNoPkb").text(noPkb);
        $("#modalWaspang").modal("show");
    });

    // click button print ba waspang
    $("#btnSimpan").click(function(e) {
        e.preventDefault();
        var no_ba = $("#inputMNoBa").val().replace("/", "_");
        // if no ba not exist created ba
        if (existNoBa.length < 2) {
            var param = {
                no_so: noSo,
                waspang_no_ba: $("#inputMNoBa").val(),
                waspang_tgl: $("#inputMTglWaspang").val(),
                waspang_nama: $("#inputMNama").val(),
                token: TOKEN
            };

            $.ajax({
                url: API_URL + "aktivasi/so/waspang/create",
                method: "POST",
                dataType: "JSON",
                data: JSON.stringify(param),
                success: function (r) {
                    //console.log(r);
                    if (r.success) {
                        notifLobi("success", "top right", r.message);

                        //var noBaTemp = '<a href="">'+$("#inputMNoBa").val()+'</a>';
                        $( "span:contains("+noSo+")" ).parent().parent().find(".existNoBa a").text($("#inputMNoBa").val());
                        $( "span:contains("+noSo+")" ).parent().parent().find(".existName").text($("#inputMNama").val());
                        $( "span:contains("+noSo+")" ).parent().parent().find(".existTgl").text($("#inputMTglWaspangView").val());

                        var noPkb = $( "span:contains("+noSo+")" ).parent().parent().find(".linkWaspang").text();
                        $( "span:contains("+noSo+")" ).parent().next().html(noPkb);
                        
                        //console.log("noPkb: " + noPkb);

                        $("#modalWaspang").modal("hide");
                    } else 
                        notifLobi("error", "top right", r.message);
                }
            });
        } else 
            window.open(WEB_URL + "unit/aktivasi/so/waspang/ba/" + no_ba, "Cetak BA Waspang", "height=650,width=850");

        //console.log($(this).text());
    });

    // click link no ba waspang 
    $(".existNoBa a").click(function(e) {
        e.preventDefault();
        var no_ba = $(this).text().replace("/", "_");
        window.open(WEB_URL + "unit/aktivasi/so/waspang/ba/" + no_ba, "Cetak BA Waspang", "height=650,width=850");
        return false;
    });
});