<!-- Include Jquery Easy UI -->
<link rel="stylesheet" type="text/css" href="<?php echo WEB_URL; ?>js_easyui/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="<?php echo WEB_URL; ?>js_easyui/themes/icon.css">
<script src="<?php echo WEB_URL; ?>js_easyui/jquery.easyui.min.js?v=<?php echo JS_VER; ?>"></script>
<style>
.datagrid-cell { font-size: 13px !important; margin: 0 !important; padding: 0 !important; }
</style>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_URL; ?>themes/dist/css/easyui_custom_table.css">

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
        DAFTAR PENGGUNAAN MATERIAL SERPO
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Unit</li>
        <li>Serpo</li>
        <li class="active">Penggunaan Material</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="#" class="btn btn-primary btn-sm" id="btnInput" data-toggle="modal" data-target="#modalPenggunaanMaterial"><i class="fa fa-fw fa-plus"></i> <span>INPUT<span></a>
              </h3>

              <div class="box-tools">
                <div class="input-group input-group-sm" style="width: 450px;position:relative;top:5px;">

                  <div class="input-group pull-right" style="width:250px;">
                    <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </div>
                    <input type="text" class="form-control pull-right" style="height:30px;" id="daterange" value="<?php echo $range_date; ?>">
                  </div>

                  <input type="text" name="key" style="width:100px;" id="keySearch" class="form-control pull-right" placeholder="Search" value="<?php echo $key; ?>">

                  <div class="input-group-btn">                    
                    <button type="submit" class="btn btn-default" id="btnSearch"><i class="fa fa-search"></i></button>
                    <?php if ($key != ""): ?>
                    <button type="submit" class="btn btn-default" id="btnClearSearch" title="clear search"><i class="fa fa-close"></i></button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.box-header -->

            <div class="box-body table-responsive">
              <table class="easyui-datagrid aktivasi-table" id="tbl-material-serpo" style="height:420px;" 
                    singleSelect="true" iconCls="" fitColumn="true">
                <thead frozen="true">
                  <tr>
                    <th field="dummyNoBukti" width="120" align="center"><b>NO. AR</b></th>
                  </tr>
                </thead>
                <thead>
                  <tr>
                    <th field="dummyId" hidden="true">ID LOOP</th>
                    <th field="dummyTgl" width="80" align="center"><b>TANGGAL</b></th>
                    <th field="dummyRegional" width="130" align="center"><b>REGIONAL</b></th>
                    <th field="dummyTransaksi" width="50" align="center"><b>KODE</b></th>
                    <th field="dummyJenis" width="380" align="left">&nbsp;&nbsp;<b>JENIS MATERIAL</b></th>
                    <th field="dummySatuan" width="120" align="center"><b>SATUAN</b></th>
                    <th field="dummyQty" width="80" align="center"><b>QTY</b></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($dataSerpo as $id => $row) : ?>
                  <?php $rowspan = count($row->detail); ?>
                  <tr id="tr_<?php echo $id; ?>">
                    <td><b><?php echo $row->no_ar; ?></b></td>
                    <td><?php echo $id; ?></td>
                    <td><span><?php echo toRojbDate($row->tgl); ?><span></td>
                    <td><?php echo $row->nama_regional; ?></td>
                    <td>
                      <?php foreach ($row->detail as $idx1 => $det1): ?>
                      <?php if ($idx1 + 1 != $rowspan): ?>
                          <div class="detail-in-table" title="<?php echo $det1->kode_material; ?>"><?php echo $det1->kode_material; ?></div>
                      <?php else: ?>
                          <div class="detail-in-table-inv" title="<?php echo $det1->kode_material; ?>"><?php echo $det1->kode_material; ?></div>
                      <?php endif; ?>
                      <?php endforeach; ?>
                    </td>
                    <td>
                      <?php foreach ($row->detail as $idx2 => $det2): ?>
                      <?php if ($idx2 + 1 != $rowspan): ?>
                          <div class="detail-in-table"><?php echo $det2->jenis_material; ?>&nbsp;</div>
                      <?php else: ?>
                          <div class="detail-in-table-inv"><?php echo $det2->jenis_material; ?>&nbsp;</div>
                      <?php endif; ?>
                      <?php endforeach; ?>
                    </td>
                    <td>
                      <?php foreach ($row->detail as $idx3 => $det3): ?>
                      <?php if ($idx3 + 1 != $rowspan): ?>
                          <div class="detail-in-table"><?php echo $det3->satuan_material; ?>&nbsp;</div>
                      <?php else: ?>
                          <div class="detail-in-table-inv"><?php echo $det3->satuan_material; ?>&nbsp;</div>
                      <?php endif; ?>
                      <?php endforeach; ?>
                    </td>
                    <td>
                      <?php foreach ($row->detail as $idx4 => $det4): ?>
                      <?php if ($idx4 + 1 != $rowspan): ?>
                          <div class="detail-in-table"><?php echo $det4->qty; ?>&nbsp;</div>
                      <?php else: ?>
                          <div class="detail-in-table-inv"><?php echo $det4->qty; ?>&nbsp;</div>
                      <?php endif; ?>
                      <?php endforeach; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
      </div>

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- modal serpo penggunaan material -->
<div class="modal fade" id="modalPenggunaanMaterial" tabindex="-1" role="dialog" aria-labelledby="modalPembelianMaterialLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalPembelianMaterialLabel">Input Penggunaan Material Serpo Baru</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal">
            <div class="box-body">
            <div class="form-group">
                <label for="inputMNoAr" class="col-sm-4 control-label">NO. AR</label>

                <div class="col-sm-7">
                <input type="text" class="form-control" id="inputMNoAr" placeholder="NO. AR" value="" style="text-transform:uppercase">
                </div>
            </div>
            <div class="form-group">
                <label for="inputMTglKuitansi" class="col-sm-4 control-label">TANGGAL</label>

                <div class="col-sm-7">
                <input type="hidden" class="form-control" id="inputMTgl" value="<?php echo $tgl; ?>" readonly>
                <input type="text" class="form-control" id="inputMTglView" placeholder="TGL SO" value="<?php echo $tgl_view; ?>" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="inputMKodeRegional" class="col-sm-4 control-label">REGIONAL</label>

                <div class="col-sm-7">
                <select class="form-control" id="inputMKodeRegional">
                <?php foreach ($dataMRegional as $idx => $row) : ?>
                    <option value="<?php echo $row->kode; ?>"><?php echo $row->nama; ?></option>
                <?php endforeach; ?>
                </select>
                </div>
            </div>
            </div>
            <!-- /.box-body -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="btnCancel" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-primary" id="btnInputMaterial">MATERIAL</button>
      </div>
    </div>
  </div>
</div>

<!-- modal material -->
<div class="modal fade bs-example-modal-lg" id="modalMaterial" tabindex="-1" role="dialog" aria-labelledby="modalMaterialLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalMaterialLabel">
          Input Material
          <span class="pull-right" id="labelNoSerpoMaterial" style="margin-right:15px;"></span>
        </h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" id="form-material">
            <div class="box-body">
            
            <table class="table" style="width:100%;margin:0;">
                <tr style="background:#e9e9e9;">
                    <th style="width:12%;text-align:center;">KODE</th>
                    <th style="text-align:center;">JENIS MATERIAL</th>
                    <th style="width:15%;text-align:center;">SATUAN</th>
                    <th style="width:17%;text-align:center;">Q</th>
                    <!--<th style="text-align:center;">NILAI BELI</th>-->
                </tr>
            </table>
            <div style="height:300px;overflow: scroll;overflow-x: hidden;">
            <table class="table table-hover table-bordered" id="tbl-material">                
                <?php foreach ($dataMaterial as $idx => $row) : ?>
                <tr class="row-material">
                    <td style="text-align:center;width:13%;">
                        <label>
                            <span style="position:relative;top:2px;"><input type="checkbox" class="material-check"/> &nbsp;</span>
                            <span class="data-kode-material"><?php echo $row->kode; ?></span>
                        </label>
                    </td>
                    <td><span class="data-jenis-material"><?php echo $row->jenis; ?></span></td>
                    <td style="width:20%;text-align:center;"><span class="data-satuan-material"><?php echo $row->satuan; ?></span></td>
                    <td style="text-align:center;width:13%;">
                        <input type="text" id="material-<?php echo $row->kode; ?>" data-kode="<?php echo $row->kode; ?>" data-jenis="<?php echo $row->jenis; ?>" style="text-align:center;width:70px;" maxlength="5" class="material_qty" name="material_qty[]" onkeypress='return event.charCode >= 48 && event.charCode <= 57'/>
                    </td>
                    <!--<td style="text-align:center;width:23%;">
                        <input type="text" id="nilai_beli-<?php echo $row->kode; ?>" data-kode="<?php echo $row->kode; ?>" data-jenis="<?php echo $row->jenis; ?>" style="text-align:right;width:140px;" class="material_nilai_beli" name="material_nilai_beli[]" onkeypress='return event.charCode >= 48 && event.charCode <= 57'/>
                    </td>-->
                </tr>
                <?php endforeach; ?>
            </table>
            </div>
            <table class="table" style="width:100%%;margin:0;">
              <tr style="background:#f3f3f3;">
                  <th style="text-align:center">JUMLAH</th>
                  <th style="width:25.5%;text-align:right">
                      <input type="text" readonly="true" style="margin-right:25px;text-align:center;width:71px;" id="jml_material" name="jumlah_material"/>
                  </th>
                  <!--<th style="width:23%;text-align:right;">
                      <input type="text" readonly="true" style="margin-right:32px;text-align:right;width:140px;" id="jml_nilai_material" name="jumlah_nilai_material"/>
                  </th>-->
              </tr>
          </table>

            </div>
            <!-- /.box-body -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="btnCancel" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-default" id="btnResetMaterial">RESET</button>
        <button type="button" class="btn btn-primary" id="btnSimpanMaterial">SIMPAN</button>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo WEB_URL; ?>js_control/unit/serpo.penggunaan_material.js?v=<?php echo JS_VER; ?>"></script>
