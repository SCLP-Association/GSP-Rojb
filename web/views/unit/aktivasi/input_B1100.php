<div class="row nopadding btemplate" id="b0template" style="margin-bottom:5px;">
    <div class="col-sm-2"></div>
    <div class="col-sm-7">
        <label class="control-label pull-left" style="margin-right:15px;">
            <input type="radio" style="margin-right:5px;" class="pull-left parkir" value="E-TOL" name="parkir[0][]">
            E-TOLL
        </label>

        <label class="control-label pull-left" style="margin-right:15px;">
            <input type="radio" style="margin-right:5px;" class="pull-left parkir" value="TOL" name="parkir[0][]" checked>
            TOL
        </label>

        <label class="control-label pull-left" style="margin-right:15px;">
            <input type="radio" style="margin-right:5px;" class="pull-left parkir" value="PARKIR" name="parkir[0][]">
            PARKIR
        </label>

        <div class="pull-left etol_field" id="etol_field" style="display:none;">
            <label class="control-label pull-left" style="margin-right:15px;">
                NO:&nbsp;&nbsp;
            </label>
            <select id="no_hp" name="no_etoll[]" class="form-control pull-left" style="width:210px;">
                <?php foreach ($etoll as $idx => $row) : ?>
                <option><?php echo $row->no_kartu; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="pull-left tol_field" id="tol_field">
            <label class="control-label pull-left" style="margin-right:15px;">
                LOK:
            </label>
            <input type="text" style="width:210px;" class="form-control" id="lokasi_tol" name="lokasi_tol[]" placeholder="Lokasi">
        </div>
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