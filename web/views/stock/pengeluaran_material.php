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
        DAFTAR PENGELUARAN MATERIAL
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Stock</li>
        <li class="active">Pengeluaran Material</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="<?php echo WEB_URL; ?>stock/pengeluaran/material/request" class="btn btn-primary btn-sm"><i class="fa fa-fw fa-ban"></i> <span>TUTUP<span></a>
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
                <table class="easyui-datagrid" style="height:420px;" 
                    singleSelect="true" iconCls="" fitColumn="true" id="dgRequest">
                    <thead frozen="true">
                        <tr>
                            <th field="productid" width="175" align="center"><span style="font-weight:bold">NO. DOKUMEN</span></th>
                        </tr>
                    </thead>
                    <thead>
                        <tr>
                            <th field="dummyNoBukti" width="150" rowspan="2" align="center"><span style="font-weight:bold">NO. BUKTI</span></th>
                            <th field="dummyTgl" width="80" rowspan="2" align="center"><span style="font-weight:bold">TGL</span></th>

                            <th field="dummyKode" width="60" rowspan="2" align="center"><span style="font-weight:bold">KODE</span></th>
                            <th field="dummyJenis" width="240" rowspan="2">&nbsp;&nbsp;<span style="font-weight:bold">JENIS MATERIAL</span></th>             
                            <th field="dummyPekerjaan" width="180" colspan="3" align="center">
                                <span style="font-weight:bold">KUANTITAS</span>
                            </th>
                        </tr>
                        <tr>
                            <th field="dummyQtyOut" width="70" align="center"><span style="font-weight:bold">QTY-OUT</span></th>
                            <th field="dummyRetur" width="70" align="center"><span style="font-weight:bold">RETUR</span></th>
                            <th field="dummyLoss" width="70" align="center"><span style="font-weight:bold">LOSS</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataPengeluaran as $idx => $row) : ?>
                        <tr>
                            <td><strong><?php echo $row->no_dokumen; ?></strong></td>
                            <td>&nbsp;&nbsp;<?php echo $row->no_bukti; ?></td>
                            <td><?php echo toRojbDate($row->tgl); ?></td>
                            <!-- MATERIAL -->
                            <td>
                                <?php $rowspanMaterial = count($row->material); ?>                                
                                <?php foreach ($row->material as $idx3 => $rowMaterial) : ?>
                                    <?php if ($idx3 + 1 != $rowspanMaterial): ?>
                                        <div class="detail-in-table" title="<?php echo $rowMaterial->kode_material; ?>"><?php echo $rowMaterial->kode_material; ?></div>
                                    <?php else: ?>
                                        <div class="detail-in-table-inv" title="<?php echo $rowMaterial->kode_material; ?>"><?php echo $rowMaterial->kode_material; ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>                                
                            </td>
                            <td>                             
                                <?php foreach ($row->material as $idx3 => $rowMaterial) : ?>
                                    <?php if ($idx3 + 1 != $rowspanMaterial): ?>
                                        <div class="detail-in-table" title="<?php echo $rowMaterial->jenis_material; ?>"><?php echo $rowMaterial->jenis_material; ?></div>
                                    <?php else: ?>
                                        <div class="detail-in-table-inv" title="<?php echo $rowMaterial->jenis_material; ?>"><?php echo $rowMaterial->jenis_material; ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>                                
                            </td>
                            <td>                             
                                <?php foreach ($row->material as $idx4 => $rowMaterial) : ?>
                                    <?php if ($idx4 + 1 != $rowspanMaterial): ?>
                                        <div class="detail-in-table"><?php echo (int) $rowMaterial->qty_out; ?></div>
                                    <?php else: ?>
                                        <div class="detail-in-table-inv"><?php echo (int) $rowMaterial->qty_out; ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>                                
                            </td>
                            <td>                             
                                <?php foreach ($row->material as $idx5 => $rowMaterial) : ?>
                                    <?php if ($idx5 + 1 != $rowspanMaterial): ?>
                                        <div class="detail-in-table"><?php echo (int) $rowMaterial->retur; ?></div>
                                    <?php else: ?>
                                        <div class="detail-in-table-inv"><?php echo (int) $rowMaterial->retur; ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>                                
                            </td>
                            <td>                             
                                <?php foreach ($row->material as $idx6 => $rowMaterial) : ?>
                                    <?php if ($idx6 + 1 != $rowspanMaterial): ?>
                                        <div class="detail-in-table"><?php echo (int) $rowMaterial->loss; ?></div>
                                    <?php else: ?>
                                        <div class="detail-in-table-inv"><?php echo (int) $rowMaterial->loss; ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>                                
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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

<!-- modal Qout -->
<div class="modal fade" id="modalQout" tabindex="-1" role="dialog" aria-labelledby="modalQoutLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalQoutLabel">Input Q-OUT Baru</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" id="form-pkb">
            <div class="box-body">
            <div class="form-group">
                <label for="inputMNoBukti" class="col-sm-3 control-label">NO. BUKTI</label>

                <div class="col-sm-7">
                <input type="hidden" id="inputMNoPkb"/>
                <input type="text" class="form-control" id="inputMNoBukti" name="no_bukti" placeholder="NO. BUKTI" value="" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="inputMTgl" class="col-sm-3 control-label">TGL</label>

                <div class="col-sm-7">
                <input type="hidden" class="form-control" id="inputMTgl" name="tgl" value="<?php echo $tgl; ?>" readonly>
                <input type="text" class="form-control" id="inputMTglView" placeholder="TGL BA" value="<?php echo $tgl_view; ?>" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="inputMPenerima" class="col-sm-3 control-label">PENERIMA</label>

                <div class="col-sm-7">
                <input type="text" class="form-control" id="inputMPenerima" name="penerima" placeholder="PENERIMA" style="text-transform:uppercase;">
                </div>
            </div>

            <div class="form-group" id="listMaterial">
                <div>
                <table id="tb-material-tmp" class="table table-hover table-bordered" style="width:90%;margin: 0 auto;">
                    <thead>
                    <tr>
                        <th style="width:20%" class="text-center">KODE</th>
                        <th>JENIS MATERIAL</th>
                        <th style="width:20%" class="text-center">Q-OUT</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                </div>
            </div>

            </div>
            <!-- /.box-body -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="btnCancel" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-primary" id="btnSimpanQout">SIMPAN</button>
      </div>
    </div>
   </div>
</div>

<script src="<?php echo WEB_URL; ?>js_control/stock/pengeluaran_material.js?v=<?php echo JS_VER; ?>"></script>