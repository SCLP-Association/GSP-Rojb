<div class="row nopadding btemplate" id="b0template" style="margin-bottom:5px;">
    <div class="col-sm-2"></div>
    <div class="col-sm-6">
        <label class="control-label pull-left" style="margin-right:15px;">
            <input type="radio" style="margin-right:5px;" class="pull-left listrik" value="LISTRIK" name="listrik[0][]">
            LISTRIK
        </label>

        <label class="control-label pull-left" style="margin-right:15px;">
            <input type="radio" style="margin-right:5px;" class="pull-left listrik" value="AIR" name="listrik[0][]">
            AIR
        </label>

        <label class="control-label pull-left" style="margin-right:15px;">
            REGIONAL:
        </label>

        <select id="regional" name="regional[]" class="form-control pull-left" style="width:230px;">
            <?php foreach ($regional as $idx => $row) : ?>
            <option><?php echo $row->wilayah; ?></option>
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