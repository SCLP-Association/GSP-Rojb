<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
        INPUT SERPO RUTIN
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Unit</li>
        <li>Serpo</li>
        <li class="active">Input</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
            <form class="form-horizontal form-input" id="form-aktivasi">
              <h3 class="box-title">
                <a href="<?php echo WEB_URL; ?>unit/serpo/rutin" class="btn btn-primary btn-sm" id="btnTutup"><i class="fa fa-fw fa-ban"></i> <span>TUTUP<span></a>
                <a href="#" data-toggle="modal" data-target="#modalKuitansi" class="btn btn-primary btn-sm" id="btnKuitansi"><i class="fa fa-fw fa-file-text-o"></i> <span>KUITANSI<span></a>
                <button type="submit" class="btn btn-primary btn-sm" id="btnBaru"><i class="fa fa-fw fa-save"></i> <span>SIMPAN<span></button>
              </h3>

              <div class="pull-right">
                <button disabled class="btn btn-warning btn-sm" id="btnStatusKuitansi"><i class="fa fa-fw fa-ban"></i> <span>KUITANSI BELUM DIBUAT<span></button>
              </div>

              <div id="box-form" style="margin: 15px 0 10px 0;">
                <!--<div class="box-header with-border">
                    <h3 class="box-title" id="box-form-title" style="padding-left:10px;">FORM JENIS JASA</h3>
                </div>-->
                
                    <div class="box-body">
                    <!-- Kuitansi hidden field -->
                    <input type="hidden" value="0" name="no_kuitansi" id="inputNoKuitansi"/>
                    <input type="hidden" value="0" name="tgl_kuitansi" id="inputTglKuitansi"/>
                    <input type="hidden" value="0" name="admin_kuitansi" id="inputAdminKuitansi"/>
                    <input type="hidden" value="0" name="penerima_kuitansi" id="inputPenerimaKuitansi"/>
                    <input type="hidden" value="0" name="id_kuitansi" id="inputIdKuitansi"/>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">NO. BUKTI</label>

                        <div class="col-sm-3">
                        <input type="text" class="form-control" id="inputNobukti" name="no_bukti" placeholder="No Bukti" value="<?php echo $no_bukti; ?>" style="text-transform:uppercase;" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">TANGGAL</label>

                        <div class="col-sm-3">
                        <input type="hidden" class="form-control" id="inputTanggal" name="tgl" placeholder="Tanggal" value="<?php echo $tgl; ?>" style="text-transform:uppercase;" readonly>
                        <input type="text" class="form-control" id="inputTanggalView" name="tgl_view" placeholder="Tanggal" value="<?php echo $tgl_view; ?>" style="text-transform:uppercase;" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">JENIS BIAYA</label>

                        <div class="col-sm-8">
                        <input type="hidden" id="inputJenis" name="jenis_biaya"/>
                        <select class="form-control" id="inputKode" name="kode_biaya">
                            <?php foreach ($biaya as $id => $row): ?>
                            <option value="<?php echo $row->kode; ?>"><?php echo $row->kode; ?>. <?php echo $row->jenis; ?></option>
                            <?php endforeach; ?>
                        </select>
                        </div>
                    </div>
                    
                    <div id="template-biaya">
                    </div>                    

                    </div>
                    <!-- /.box-body -->
              </div>
            </form>
            </div>
            <!-- /.box-header -->
          </div>
          <!-- /.box -->
        </div>
      </div>

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- modal kuitansi -->
<div class="modal fade" id="modalKuitansi" tabindex="-1" role="dialog" aria-labelledby="modalKuitansiLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalKuitansiLabel">Kuitansi</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal">
            <div class="box-body">
            <div class="form-group">
                <label for="inputMNoKuitansi" class="col-sm-3 control-label">KUITANSI NO</label>

                <div class="col-sm-7">
                <input type="text" class="form-control" id="inputMNoKuitansi" placeholder="KUITANSI NO" readonly value="<?php echo $no_kuitansi; ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="inputMTglKuitansi" class="col-sm-3 control-label">TGL</label>

                <div class="col-sm-7">
                <input type="hidden" class="form-control" id="inputMTglKuitansi" value="<?php echo $tgl; ?>" readonly>
                <input type="text" class="form-control" id="inputMTglKuitansiView" placeholder="TGL KUITANSI" value="<?php echo $tgl_view; ?>" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="inputMAdminKuitansi" class="col-sm-3 control-label">ADMIN</label>

                <div class="col-sm-7">
                <input type="text" class="form-control" id="inputMAdminKuitansi" placeholder="ADMIN" readonly value="<?php echo $admin_kuitansi; ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="inputMPenerimaKuitansi" class="col-sm-3 control-label">PENERIMA</label>

                <div class="col-sm-9">
                <input type="text" class="form-control" id="inputMPenerimaKuitansi" placeholder="PENERIMA" style="text-transform:uppercase;">
                </div>
            </div>
            <div class="form-group">
                <label for="inputMIdKuitansi" class="col-sm-3 control-label">ID</label>

                <div class="col-sm-9">
                <input type="text" class="form-control" id="inputMIdKuitansi" placeholder="ID" style="text-transform:uppercase;">
                </div>
            </div>
            </div>
            <!-- /.box-body -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="btnCancelKuitansi" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-primary" id="btnSimpanKuitansi">SIMPAN</button>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo WEB_URL; ?>js_control/unit/serpo.input.js?v=<?php echo JS_VER; ?>"></script>