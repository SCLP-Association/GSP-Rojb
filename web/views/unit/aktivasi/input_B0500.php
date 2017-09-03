<div class="row nopadding btemplate" id="b0template" style="margin-bottom:5px;">
    <div class="col-sm-2"></div>
    <div class="col-sm-6">
        <label class="control-label pull-left" style="margin-right:15px;">
            <input type="radio" style="margin-right:5px;" class="pull-left pulsa" value="PULSA" name="pulsa[0][]" checked>
            PULSA
        </label>

        <label class="control-label pull-left" style="margin-right:15px;">
            <input type="radio" style="margin-right:5px;" class="pull-left pulsa" value="MODEM" name="pulsa[0][]">
            MODEM
        </label>

        <label class="control-label pull-left" style="margin-right:15px;">
            NO. KARTU:
        </label>

        <select id="no_hp" name="no_hp[]" class="form-control pull-left" style="width:220px;">
            <?php foreach ($hp as $idx => $row) : ?>
            <option><?php echo $row->no_kartu; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-sm-1">
    </div>

    <div class="col-sm-2" style="padding:0;">
        <label class="control-label pull-left">&nbsp;&nbsp;&nbsp;&nbsp;RP:&nbsp;&nbsp;</label>
        <input type="text" style="width:110px;" class="form-control price" id="harga" name="harga[]" placeholder="Harga">
    </div>

    <div class="col-sm-1">
        <a href="" class="btn btn-success btn-sm pull-right" id="addrow"><i class="fa fa-fw fa-plus"></i></a>
        <a href="" class="btn btn-danger btn-sm pull-right initdel" style="display:none;" id="deleterow"><i class="fa fa-fw fa-close"></i></a>
    </div>
</div>

<div class="septemplate"></div>