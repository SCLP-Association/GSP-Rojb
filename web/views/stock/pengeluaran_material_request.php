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
        DAFTAR MATERIAL REQUEST
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Stock</li>
        <li class="active">Material Request</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="" class="btn btn-primary btn-sm" id="btnQout"><i class="fa fa-fw fa-sign-out"></i> <span>Q-OUT<span></a>
                <a href="" class="btn btn-danger btn-sm" id="btnKerugian"><span>KERUGIAN<span></a>
                <a href="" class="btn btn-primary btn-sm" id="btnRetur"><span>RETUR<span></a>
                <a href="<?php echo WEB_URL; ?>stock/pengeluaran/material" class="btn btn-primary btn-sm" id="btnPengeluaran"><span>PENGELUARAN MATERIAL<span></a>
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
                            <th field="hiddenNoSo" hidden="true">HIDDEN NO SO</th>
                            <th field="hiddenPkb" hidden="true">HIDDEN PKB</th>
                            <th field="hiddenPkt" hidden="true">HIDDEN PKT</th>
                            <th field="productid" width="165" align="center"><span style="font-weight:bold">NO. BUKTI</span></th>
                        </tr>
                    </thead>
                    <thead>
                        <tr>
                            <th field="dummyTglPkb" width="80" rowspan="2" align="center"><span style="font-weight:bold">TGL</span></th>
                            <th field="dummyPekerjaan" width="210" rowspan="2" align="center"><span style="font-weight:bold">PEKERJAAN / PROJECT</span></th>
                            <th field="hiddenData" rowspan="2" hidden="true"></th>
                            
                            <th field="dummyKode" width="60" rowspan="2" align="center"><span style="font-weight:bold">KODE</span></th>
                            <th field="dummyJenis" width="200" rowspan="2">&nbsp;&nbsp;<span style="font-weight:bold">JENIS MATERIAL</span></th>
                            <th field="dummyPekerjaan" width="180" colspan="4" align="center">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <span style="font-weight:bold">KUANTITAS</span>
                            </th>               
                        </tr>
                        <tr>
                            <th field="dummyQty" width="70" align="center"><span style="font-weight:bold">QTY</span></th>
                            <th field="dummyQtyOut" width="70" align="center"><span style="font-weight:bold">QTY-OUT</span></th>
                            <th field="dummyRetur" width="70" align="center"><span style="font-weight:bold">RETUR</span></th>
                            <th field="dummyLoss" width="70" align="center"><span style="font-weight:bold">LOSS</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataSo as $idx => $row) : ?>
                        <tr>
                            <td><?php echo $row->no_so; ?></td>
                            <td><?php echo $row->pkb_no_bukti; ?></td>
                            <td><?php echo $row->pkt_no_bukti; ?></td>
                            <td><strong><?php echo $row->pkb_no_bukti; ?></strong></td>
                            <td><?php echo toRojbDate($row->pkb_tgl); ?></td>
                            <td>&nbsp;&nbsp;<?php echo $row->pekerjaan; ?></td>
                            <td>
                            <?php
                            $arrMaterial = [];
                            foreach ($row->material as $index => $material) {
                                $arrMaterial[] = [
                                    "kode_material" => $material->kode_material,
                                    "jenis_material" => $material->jenis_material,
                                    "qty" => $material->qty,
                                    "qty_out" => $material->qty_out
                                ];
                            }
                            echo json_encode($arrMaterial);
                            ?>
                            </td>
                            
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
                                <?php foreach ($row->material as $idx3 => $rowMaterial) : ?>
                                    <?php if ($idx3 + 1 != $rowspanMaterial): ?>
                                        <div class="detail-in-table"><?php echo $rowMaterial->qty; ?></div>
                                    <?php else: ?>
                                        <div class="detail-in-table-inv"><?php echo $rowMaterial->qty; ?></div>
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

<!-- modal Retur -->
<div class="modal fade" id="modalRetur" tabindex="-1" role="dialog" aria-labelledby="modalReturLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalReturLabel">Input Retur Baru</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" id="form-pkb">
            <div class="box-body">
            <div class="form-group">
                <label for="inputMNoBuktiRetur" class="col-sm-3 control-label">NO. BA</label>

                <div class="col-sm-7">
                <input type="text" class="form-control" id="inputMNoBuktiRetur" name="no_bukti_retur" placeholder="NO. BA" readonly value="">
                </div>
            </div>
            <div class="form-group">
                <label for="inputMTglRetur" class="col-sm-3 control-label">TGL</label>

                <div class="col-sm-7">
                <input type="hidden" id="inputMTglRetur" value="<?php echo $tgl; ?>"/>
                <input type="text" class="form-control" id="inputMTglReturView" name="tgl_retur" placeholder="TGL. RETUR" readonly value="<?php echo $tgl_view; ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="inputMPeretur" class="col-sm-3 control-label">PERETUR</label>

                <div class="col-sm-7">
                <input type="text" class="form-control" id="inputMPeretur" name="peretur" placeholder="PERETUR" style="text-transform:uppercase;" value="">
                </div>
            </div>
            <div class="form-group">
                <label for="inputMTgl" class="col-sm-3 control-label">MATERIAL</label>

                <div class="col-sm-7">
                <select name="returMaterial" class="form-control" id="cbReturMaterial">
                </select>
                </div>
            </div>

            <div class="form-group" style="padding:0 20px;">
                <table class="table table-bordered" id="tbl-retur">
                <thead>
                    <tr>
                        <th style="text-align:center;width:40%">BA-QOUT</th>
                        <th style="text-align:center;width:20%">QOUT-NET</th>
                        <th style="text-align:center">RETUR</th>
                        <th style="width:10%"></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
                </table>
            </div>

            </div>
            <!-- /.box-body -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="btnCancelRetur" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-primary" id="btnSimpanRetur">SIMPAN</button>
      </div>
    </div>
   </div>
</div>


<!-- modal Kerugian -->
<div class="modal fade" id="modalKerugian" tabindex="-1" role="dialog" aria-labelledby="modalKerugianLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalKerugianLabel">Input Kerugian Baru</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" id="form-pkb-kerugian">
            <div class="box-body">
            <div class="form-group">
                <label for="inputMNoBuktiKerugian" class="col-sm-3 control-label">NO. BA</label>

                <div class="col-sm-7">
                <input type="text" class="form-control" id="inputMNoBuktiKerugian" name="no_bukti_kerugian" placeholder="NO. BA" readonly value="">
                </div>
            </div>
            <div class="form-group">
                <label for="inputMTglKerugian" class="col-sm-3 control-label">TGL</label>

                <div class="col-sm-7">
                <input type="hidden" id="inputMTglKerugian" value="<?php echo $tgl; ?>"/>
                <input type="text" class="form-control" id="inputMTglKerugianView" name="tgl_kerugian" placeholder="TGL. KERUGIAN" readonly value="<?php echo $tgl_view; ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="inputMNoBaps" class="col-sm-3 control-label">NO. BAPS</label>

                <div class="col-sm-7">
                <input type="text" class="form-control" id="inputMNoBaps" name="no_baps" placeholder="NO. BAPS" style="text-transform:uppercase;" value="">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">MATERIAL</label>

                <div class="col-sm-7">
                <select name="kerugianMaterial" class="form-control" id="cbKerugianMaterial">
                </select>
                </div>
            </div>

            <div class="form-group" style="padding:0 20px;">
                <table class="table table-bordered" id="tbl-kerugian">
                <thead>
                    <tr>
                        <th style="text-align:center;width:40%">BA-QOUT</th>
                        <th style="text-align:center;width:20%">QOUT-NET</th>
                        <th style="text-align:center">LOSS</th>
                        <th style="width:10%"></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
                </table>
            </div>

            </div>
            <!-- /.box-body -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="btnCancelKerugian" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-primary" id="btnSimpanKerugian">SIMPAN</button>
      </div>
    </div>
   </div>
</div>

<script src="<?php echo WEB_URL; ?>js_control/stock/pengeluaran_material_request.js?v=<?php echo JS_VER; ?>"></script>