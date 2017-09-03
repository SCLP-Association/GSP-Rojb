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
        DETAIL PKT SO
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Unit</li>
        <li>Aktivasi</li>
        <li class="active">Pkt SO Detail</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="<?php echo WEB_URL; ?>unit/aktivasi/so/pkt" class="btn btn-primary btn-sm"><i class="fa fa-fw fa-ban"></i> <span>TUTUP<span></a>
                <a href="#" class="btn btn-primary btn-sm" id="btnUpload"><i class="fa fa-fw fa-upload"></i> <span>UPLOAD GR<span></a>
                <a href="#" class="btn btn-primary btn-sm" id="btnHapus"><i class="fa fa-fw fa-trash"></i> <span>HAPUS GR<span></a>
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
                <table class="easyui-datagrid aktivasi-table" style="height:420px;" 
                    singleSelect="true" iconCls="" fitColumn="true" id="tbl-pkt-detail">
                <thead frozen="true">
                  <tr>
                    <th field="dummyHiddenNoPkb" hidden="true">HIDDEN NO. PKB</th>
                    <th field="dummyHiddenNoPkt" hidden="true">HIDDEN NO. PKT</th>
                    <th field="dummyHiddenNoSo" hidden="true">HIDDEN NO. SO</th>
                    <th field="dummyHiddenPktGrFile" hidden="true">HIDDEN PKT GR FILE</th>

                    <th field="dummyNoBukti" width="80" align="center"><span style="font-weight:bold">NO. SO</span></th>
                  </tr>
                </thead>
                <thead>
                  <tr>
                    <th field="dummyTglSo" width="80" align="center" rowspan="2"><span style="font-weight:bold">TGL. SO</span></th>
                    <th field="dummyPtl" width="140" align="center" rowspan="2"><span style="font-weight:bold">PTL</span></th>
                    <th field="dummyPkb" align="center" colspan="4"><span style="font-weight:bold">P K B</span></th>
                    <th field="dummyPkt" align="center" colspan="4"><span style="font-weight:bold">P K T</span></th>
                    <th field="dummyGr" width="100" align="center" rowspan="2"><span style="font-weight:bold">GR</span></th>
                  </tr>
                  <tr>
                    <th field="dummyNoPkb" width="160" align="center"><span style="font-weight:bold">NO. PKB</span></th>
                    <th field="dummyTglPkb" width="80" align="center"><span style="font-weight:bold">TGL</span></th>
                    <th field="dummyDetailKhsPkb" width="320" align="left">&nbsp;&nbsp;<span style="font-weight:bold">DETAIL</span></th>
                    <th field="dummyQtyKhsPkb" width="60" align="center"><span style="font-weight:bold">Q</span></th>

                    <th field="dummyNoPkt" width="160" align="center"><span style="font-weight:bold">NO. PKT</span></th>
                    <th field="dummyTglPkt" width="80" align="center"><span style="font-weight:bold">TGL</span></th>
                    <th field="dummyDetailKhsPkt" width="320" align="left">&nbsp;&nbsp;<span style="font-weight:bold">DETAIL</span></th>
                    <th field="dummyQtyKhsPkt" width="60" align="center"><span style="font-weight:bold">Q</span></th>
                  </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataSo as $id => $row) : ?>
                    <tr>
                        <td><?php echo $row->pkb_no_bukti; ?></td>
                        <td><?php echo $row->pkt_no_bukti; ?></td>
                        <td><?php echo $row->no_so; ?></td>
                        <td><?php echo $row->pkt_gr_file; ?></td>

                        <td><b><?php echo $row->no_so; ?></b></td>
                        <td><?php echo toRojbDate($row->tgl); ?></td>
                        <td><?php echo $row->ptl; ?></td>
                        <!-- PKB DATA -->
                        <td><?php echo $row->pkb_no_bukti; ?></td>
                        <td><?php echo toRojbDate($row->pkb_tgl); ?></td>
                        <td>
                            <?php $rowspanKhs = count($row->khs); ?>                                
                            <?php foreach ($row->khs as $idx2 => $rowKhs) : ?>
                                <?php if ($idx2 + 1 != $rowspanKhs): ?>
                                    <div class="detail-in-table" title="<?php echo $rowKhs->jenis_khs; ?>"><?php echo $rowKhs->jenis_khs; ?></div>
                                <?php else: ?>
                                    <div class="detail-in-table-inv" title="<?php echo $rowKhs->jenis_khs; ?>"><?php echo $rowKhs->jenis_khs; ?></div>
                                <?php endif; ?>
                            <?php endforeach; ?>                                
                        </td>
                        <td>
                          <?php foreach ($row->khs as $idx2 => $rowKhs) : ?>
                              <?php if ($idx2 + 1 != $rowspanKhs): ?>
                                  <div class="detail-in-table text-center"><?php echo $rowKhs->qty; ?>&nbsp;&nbsp;</div>
                              <?php else: ?>
                                  <div  class="detail-in-table-inv text-center"><?php echo $rowKhs->qty; ?>&nbsp;&nbsp;</div>
                              <?php endif; ?>
                          <?php endforeach; ?>   
                        </td>
                        <!-- PKT DATA -->
                        <td>
                          <a href="" style="text-decoration: underline; color: blue;"><?php echo $row->pkt_no_bukti; ?></a>
                        </td>
                        <td><?php echo toRojbDate($row->pkt_tgl); ?></td>
                        <td>
                            <?php $rowspanKhsPkt = count($row->khs_pkt); ?>                                
                            <?php foreach ($row->khs_pkt as $idx2 => $rowKhsPkt) : ?>
                                <?php if ($idx2 + 1 != $rowspanKhsPkt): ?>
                                    <div class="detail-in-table" title="<?php echo $rowKhsPkt->jenis_khs; ?>"><?php echo $rowKhsPkt->jenis_khs; ?></div>
                                <?php else: ?>
                                    <div class="detail-in-table-inv" title="<?php echo $rowKhsPkt->jenis_khs; ?>"><?php echo $rowKhsPkt->jenis_khs; ?></div>
                                <?php endif; ?>
                            <?php endforeach; ?>                                
                        </td>
                        <td>
                          <?php foreach ($row->khs_pkt as $idx2 => $rowKhsPkt) : ?>
                              <?php if ($idx2 + 1 != $rowspanKhsPkt): ?>
                                  <div class="detail-in-table text-center"><?php echo $rowKhsPkt->qty; ?>&nbsp;&nbsp;</div>
                              <?php else: ?>
                                  <div  class="detail-in-table-inv text-center"><?php echo $rowKhsPkt->qty; ?>&nbsp;&nbsp;</div>
                              <?php endif; ?>
                          <?php endforeach; ?>   
                        </td>
                        <td>
                            <?php if (strlen(trim($row->pkt_gr_file)) > 0 && $row->pkt_gr_file != NULL) : ?>
                            <i class="fa fa-fw fa-file-text-o"></i> <?php echo toRojbDate($row->pkt_gr_tgl); ?>
                            <?php endif; ?>
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

<!-- modal pkt upload gr -->
<div class="modal fade" id="modalUploadGr" tabindex="-1" role="dialog" aria-labelledby="modalUploadGrLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalUploadGrLabel">Upload File Good Receive</h4>
      </div>
      <form class="form-horizontal" method="post" enctype="multipart/form-data" action="" id="form-pkt-upload">
        <div class="modal-body">
            <div class="box-body">
            <div class="form-group" id="fg-noPkt">
                <label for="inputMNoPkb" class="col-sm-3 control-label">NO. PKT</label>

                <div class="col-sm-7">
                <input type="hidden" id="inputMNoSo" name="no_so"/>
                <input type="text" class="form-control" id="inputMNoPkt" name="pkt_no_bukti" placeholder="NO. PKT" value="" style="text-transform:uppercase;" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="inputMTglPkt" class="col-sm-3 control-label">FILE GR</label>

                <div class="col-sm-7">
                <input type="file" class="form-control" name="file_gr"/>
                </div>
            </div>
            </div>
            <!-- /.box-body -->
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default" id="btnCancelGr" data-dismiss="modal">CANCEL</button>
          <button type="submit" class="btn btn-primary" name="btnUploadGr" id="btnUploadGr">UPLOAD GR</button>
        </div>
      </form>
    </div>

  </div>
</div>

<script>
    var fileUploadMsg = "<?php echo $fileUploadMsg; ?>";

    if (fileUploadMsg.trim().length > 0)
        notifLobi("success", "top right", fileUploadMsg.trim());
</script>
<script src="<?php echo WEB_URL; ?>js_control/unit/aktivasi.pkt.so_detail.js?v=<?php echo JS_VER; ?>"></script>