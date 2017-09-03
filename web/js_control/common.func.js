function notifLobi(type, position, msg) {
    Lobibox.notify(type, {
        size: "mini",
        icon: false,
        title: type,
        position: position,
        soundPath: WEB_URL + "themes/plugins/lobibox/dist/sounds/",
        msg: msg
    });
}

function maskRupiah(idElement) {
    var arrElem = idElement.split(",");
    var loop = arrElem.length;
    for (i = 0; i < loop; i++) {
        $("#" + arrElem[i] + ", ." + arrElem[i]).maskMoney({
            thousands: ".", 
            decimal: ",", 
            precision: 0, 
            allowZero: false, 
            suffix: "", 
            prefix: ""
        });
    }
}

function toRupiah(jumlah) {
    var titik = ".";
    var nilai = new String(jumlah);
    var pecah = [];

    while (nilai.length > 3) {
        var asd = nilai.substr(nilai.length - 3);
        pecah.unshift(asd);
        nilai = nilai.substr(0, nilai.length - 3);
    }

    if (nilai.length > 0) 
        pecah.unshift(nilai);

    nilai = pecah.join(titik);
    return nilai;
}

function guid() {
  function s4() {
    return Math.floor((1 + Math.random()) * 0x10000)
      .toString(16)
      .substring(1);
  }
  return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
    s4() + '-' + s4() + s4() + s4();
}

