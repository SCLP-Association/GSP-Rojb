<div class="row nopadding btemplate" style="margin-bottom:10px;">
    <div class="col-sm-3">
        <label class="control-label pull-left" style="margin-right:15px;">
            <input type="checkbox" style="margin-right:5px;" class="pull-left listrik" value="KENDARAAN" name="kendaraan">
            KENDARAAN
        </label>
    </div>

    <div class="col-sm-1"></div>
</div>

<div class="row nopadding btemplate" style="margin-bottom:5px;">
    <div class="col-sm-2">
        <label class="control-label pull-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NO. PLAT:</label>
    </div>
    <div class="col-sm-3">
        <select class="form-control" id="no_plat" name="no_plat">
        <?php foreach ($no_plat as $idx => $row) : ?>
        <option value="<?php echo $row->no_plat; ?>"><?php echo $row->no_plat; ?></option>
        <?php endforeach; ?>
        </select>
    </div>
    <div class="col-sm-1">
        <label class="control-label pull-left">&nbsp;KET:&nbsp;&nbsp;</label>
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control pull-left" id="keterangan_1" name="keterangan_1" placeholder="Keterangan">
    </div>
</div>

<div class="row nopadding btemplate" style="margin-bottom:15px;">
    <div class="col-sm-4"></div>

    <div class="col-sm-2">
        <label class="control-label pull-left">JASA RP:&nbsp;&nbsp;</label>
    </div>
    <div class="col-sm-2">
        <input type="text" class="form-control pull-left price" id="jasa_rp_1" name="jasa_rp_1" placeholder="0">
    </div>
    <div class="col-sm-2">
        <label class="control-label pull-left">MATERIAL RP:&nbsp;&nbsp;</label>
    </div>
    <div class="col-sm-2">
        <input type="text" class="form-control pull-left price" id="material_rp_1" name="material_rp_1" placeholder="0">
    </div>
</div>

<!-- NEW LINE -->

<div class="row nopadding btemplate" style="margin-bottom:10px;">
    <div class="col-sm-3">
        <label class="control-label pull-left" style="margin-right:15px;">
            <input type="checkbox" style="margin-right:5px;" class="pull-left listrik" value="BANGUNAN" name="bangunan">
            BANGUNAN
        </label>
    </div>

    <div class="col-sm-1"></div>
</div>

<div class="row nopadding btemplate" style="margin-bottom:5px;">
    <div class="col-sm-2">
        <label class="control-label pull-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;REG:</label>
    </div>
    <div class="col-sm-3">
        <select class="form-control" id="regional_1" name="regional_1">
        <?php foreach ($regional as $idx => $row) : ?>
        <option><?php echo $row->wilayah; ?></option>
        <?php endforeach; ?>
        </select>
    </div>
    <div class="col-sm-1">
        <label class="control-label pull-left">&nbsp;KET:&nbsp;&nbsp;</label>
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control pull-left" id="keterangan_2" name="keterangan_2" placeholder="Keterangan">
    </div>
</div>

<div class="row nopadding btemplate" style="margin-bottom:15px;">
    <div class="col-sm-4"></div>

    <div class="col-sm-2">
        <label class="control-label pull-left">JASA RP:&nbsp;&nbsp;</label>
    </div>
    <div class="col-sm-2">
        <input type="text" class="form-control pull-left price" id="jasa_rp_2" name="jasa_rp_2" placeholder="0">
    </div>
    <div class="col-sm-2">
        <label class="control-label pull-left">MATERIAL RP:&nbsp;&nbsp;</label>
    </div>
    <div class="col-sm-2">
        <input type="text" class="form-control pull-left price" id="material_rp_2" name="material_rp_2" placeholder="0">
    </div>
</div>

<!-- NEW LINE -->

<div class="row nopadding btemplate" style="margin-bottom:10px;">
    <div class="col-sm-3">
        <label class="control-label pull-left" style="margin-right:15px;">
            <input type="checkbox" style="margin-right:5px;" class="pull-left listrik" value="LAINNYA" name="lainnya">
            LAINNYA
        </label>
    </div>

    <div class="col-sm-1"></div>
</div>

<div class="row nopadding btemplate" style="margin-bottom:5px;">
    <div class="col-sm-2">
        <label class="control-label pull-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;REG:</label>
    </div>
    <div class="col-sm-3">
        <select class="form-control" id="regional_2" name="regional_2">
        <?php foreach ($regional as $idx => $row) : ?>
        <option><?php echo $row->wilayah; ?></option>
        <?php endforeach; ?>
        </select>
    </div>
    <div class="col-sm-1">
        <label class="control-label pull-left">&nbsp;KET:&nbsp;&nbsp;</label>
    </div>
    <div class="col-sm-6">
        <input type="text" class="form-control pull-left" id="keterangan_3" name="keterangan_3" placeholder="Keterangan">
    </div>
</div>

<div class="row nopadding btemplate" style="margin-bottom:15px;">
    <div class="col-sm-4"></div>

    <div class="col-sm-2">
        <label class="control-label pull-left">JASA RP:&nbsp;&nbsp;</label>
    </div>
    <div class="col-sm-2">
        <input type="text" class="form-control pull-left price" id="jasa_rp_3" name="jasa_rp_3" placeholder="0">
    </div>
    <div class="col-sm-2">
        <label class="control-label pull-left">MATERIAL RP:&nbsp;&nbsp;</label>
    </div>
    <div class="col-sm-2">
        <input type="text" class="form-control pull-left price" id="material_rp_3" name="material_rp_3" placeholder="0">
    </div>
</div>

<div class="septemplate"></div>