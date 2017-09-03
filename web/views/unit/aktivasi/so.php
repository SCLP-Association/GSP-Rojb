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
                <a href="#" class="btn btn-primary btn-sm" id="btnInput" data-toggle="modal" data-target="#modalSo"><i class="fa fa-fw fa-plus"></i> <span>INPUT SO<span></a>
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
                  <th style="width:10%;text-align:center;">NO. SO</th>
                  <th style="width:10%;text-align:center;">TGL. SO</th>
                  <th style="width:15%;text-align:center;">PTL</th>
                  <th style="text-align:center;">PEKERJAAN</th>
                </tr>
                <?php foreach ($dataSo as $id => $row) : ?>
                <tr>
                  <td class="text-center"><?php echo $row->no_so; ?></td>
                  <td class="text-center"><?php echo toRojbDate($row->tgl); ?></td>
                  <td class="text-center"><?php echo $row->ptl; ?></td>
                  <td><?php echo $row->pekerjaan; ?></td>
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

<!-- modal so -->
<div class="modal fade" id="modalSo" tabindex="-1" role="dialog" aria-labelledby="modalSoLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalSoLabel">Input SO Baru</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal">
            <div class="box-body">
            <div class="form-group">
                <label for="inputMNoSo" class="col-sm-3 control-label">NO. SO</label>

                <div class="col-sm-7">
                <input type="text" class="form-control" id="inputMNoSo" placeholder="NO. SO" data-mask data-inputmask='"mask": "9999/9999"' value="">
                </div>
            </div>
            <div class="form-group">
                <label for="inputMTglKuitansi" class="col-sm-3 control-label">TGL</label>

                <div class="col-sm-7">
                <input type="hidden" class="form-control" id="inputMTglSo" value="<?php echo $tgl; ?>" readonly>
                <input type="text" class="form-control" id="inputMTglSoView" placeholder="TGL SO" value="<?php echo $tgl_view; ?>" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="inputMPtla" class="col-sm-3 control-label">PTLA</label>

                <div class="col-sm-7">
                <select class="form-control" id="inputMPtla">
                  <?php foreach ($dataUser as $id => $user) : ?>
                  <option value="<?php echo $user->full_name; ?>"><?php echo $user->full_name; ?></option>
                  <?php endforeach; ?>
                </select>
                </div>
            </div>
            <div class="form-group">
                <label for="inputMPekerjaan" class="col-sm-3 control-label">PEKERJAAN</label>

                <div class="col-sm-9">
                <input type="text" class="form-control" id="inputMPekerjaan" placeholder="PEKERJAAN" style="text-transform:uppercase;">
                </div>
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

<script src="<?php echo WEB_URL; ?>js_control/unit/aktivasi.so.js?v=<?php echo JS_VER; ?>"></script>