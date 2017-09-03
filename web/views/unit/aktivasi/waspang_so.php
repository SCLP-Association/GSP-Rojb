<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
        DAFTAR SO AKTIVASI
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Unit</li>
        <li>Aktivasi</li>
        <li class="active">SO</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="#" class="btn btn-primary btn-sm"><i class="fa fa-fw fa-ban"></i> <span>TUTUP<span></a>
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
              <table class="table table-hover table-bordered aktivasi-table" id="tb-ref">
                <tr>
                  <th style="width:9%;text-align:center;">NO. SO</th>
                  <th style="width:19%;text-align:center;">NO. PKB</th>
                  <th style="text-align:center;">PEKERJAAN</th>
                  <th style="width:14%;text-align:center;">NAMA</th>
                  <th style="width:15%;text-align:center;">NO. BA</th>
                  <th style="width:10%;text-align:center;">TGL</th>
                </tr>
                <?php foreach ($dataSo as $id => $row) : ?>
                <tr>                  
                  <input type="hidden" class="dataMaterial" id="dataMaterial_<?php echo str_replace("/", "_", $row->no_so); ?>" value='<?php echo json_encode($row->material); ?>'/>

                  <td class="text-center"><span class="noSo"><?php echo $row->no_so; ?></span></td>
                  <td class="text-center" class="noPkb">
                    <?php if (strlen($row->waspang_no_ba) > 2): ?>
                      <?php echo $row->pkb_no_bukti; ?>
                    <?php else: ?>            
                      <a href="#" class="linkWaspang"><?php echo $row->pkb_no_bukti; ?></a>
                    <?php endif; ?>
                  </td>
                  <td><?php echo $row->pekerjaan; ?></td>
                  <td class="text-center"><span class="existName"><?php echo $row->waspang_nama; ?></span></td>
                  <td class="text-center">
                    <span class="existNoBa"><a href=""><?php echo $row->waspang_no_ba; ?></a></span>
                  </td>
                  <td class="text-center"><span class="existTgl"><?php echo toRojbDate($row->waspang_tgl); ?></span></td>
                </tr>                
                <?php endforeach; ?>
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

<!-- modal waspang -->
<div class="modal fade" id="modalWaspang" tabindex="-1" role="dialog" aria-labelledby="modalWaspangLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalWaspangLabel">BA Waspang</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal">
            <div class="box-body">
            <div class="form-group">
                <label for="inputMNoBa" class="col-sm-3 control-label">NO. BA</label>

                <div class="col-sm-7">
                <input type="text" class="form-control" id="inputMNoBa" readonly placeholder="NO. BA" value="">
                </div>
            </div>
            <div class="form-group">
                <label for="inputMTglKuitansi" class="col-sm-3 control-label">TGL</label>

                <div class="col-sm-7">
                <input type="hidden" class="form-control" id="inputMTglWaspang" value="<?php echo $tgl; ?>" readonly>
                <input type="text" class="form-control" id="inputMTglWaspangView" placeholder="TGL WASPANG" value="<?php echo $tgl_view; ?>" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="inputMNama" class="col-sm-3 control-label">NAMA</label>

                <div class="col-sm-7">
                <input type="text" class="form-control" id="inputMNama" placeholder="NAMA" readonly value="<?php echo $waspang_name; ?>">
                </div>
            </div>
            <div class="form-group">                
                <table class="table table-bordered" id="table-material-waspang" style="margin:0 auto;width:80%">
                <tr>
                  <th style="width:80%"><span id="spanNoPkb"></span></th>
                  <th class="text-center">QYT-OUT</th>
                </tr>
                </table>
            </div>
            </div>
            <!-- /.box-body -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="btnCancel" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-primary" id="btnSimpan">SIMPAN</button>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo WEB_URL; ?>js_control/unit/aktivasi.waspang_so.js?v=<?php echo JS_VER; ?>"></script>