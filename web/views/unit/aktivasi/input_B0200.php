<div class="row nopadding btemplate" id="b0template" style="margin-bottom:5px;">
    <div class="col-sm-2"></div>
    <div class="col-sm-6">
        <label class="control-label pull-left" style="margin-right:15px;">
            <input type="radio" style="margin-right:5px;" class="pull-left kendaraan" value="MOBIL" name="kendaraan[0][]" checked>
            MOBIL
        </label>

        <label class="control-label pull-left" style="margin-right:15px;">
            <input type="radio" style="margin-right:5px;" class="pull-left kendaraan" value="MOTOR" name="kendaraan[0][]">
            MOTOR
        </label>

        <label class="control-label pull-left" style="margin-right:15px;">
            NO. PLAT:
        </label>

        <select id="no_plat" name="no_plat[]" class="form-control pull-left" style="width:230px;">
            <?php foreach ($no_plat as $idx => $row) : ?>
            <option><?php echo $row->no_plat; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-sm-3" style="padding:0;">
        <label class="control-label pull-left">LTR:&nbsp;&nbsp;</label>
        <input type="text" style="width:50px;text-align:center;" class="form-control pull-left" id="qty" name="ltr[]" placeholder="Ltr" onkeypress='return event.charCode >= 48 && event.charCode <= 57'>
        <label class="control-label pull-left">&nbsp;&nbsp;&nbsp;&nbsp;RP:&nbsp;&nbsp;</label>
        <input type="text" style="width:110px;" class="form-control price" id="harga" name="harga[]" placeholder="Harga">
    </div>

    <div class="col-sm-1">
        <a href="" class="btn btn-success btn-sm pull-right" id="addrow"><i class="fa fa-fw fa-plus"></i></a>
        <a href="" class="btn btn-danger btn-sm pull-right initdel" style="display:none;" id="deleterow"><i class="fa fa-fw fa-close"></i></a>
    </div>
</div>

<div class="septemplate"></div>