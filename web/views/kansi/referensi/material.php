<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
        REFERENSI MATERIAL
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Kansi</li>
        <li>Referensi</li>
        <li class="active">Material</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="#" class="btn btn-primary btn-sm" id="btnBaru"><i class="fa fa-fw fa-plus"></i> <span>BARU<span></a>
                <a href="#" class="btn btn-primary btn-sm" id="btnSimpan" data-action="new" disabled><i class="fa fa-fw fa-save"></i> <span>SIMPAN<span></a>
              </h3>

              <div id="box-form" style="margin: 15px 0 10px 0;padding-top: 10px;background: #f9f9f9;">
                <div class="box-header with-border">
                    <h3 class="box-title" id="box-form-title" style="padding-left:10px;">FORM MATERIAL</h3>
                </div>
                <form class="form-horizontal">
                    <div class="box-body">
                    <div class="form-group">
                        <label class="col-sm-1 control-label">Kode</label>

                        <div class="col-sm-2">
                        <input type="text" class="form-control" id="inputKode" placeholder="Kode Material" style="text-transform:uppercase;" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-1 control-label">Jenis</label>

                        <div class="col-sm-8">
                        <input type="text" class="form-control" id="inputJenis" placeholder="Jenis Material" style="text-transform:uppercase;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-1 control-label">Satuan</label>

                        <div class="col-sm-3">
                        <input type="text" class="form-control" id="inputSatuan" placeholder="Satuan Material" style="text-transform:uppercase;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-1 control-label">Harga</label>

                        <div class="col-sm-3">
                        <input type="text" class="form-control" id="inputHarga" placeholder="Harga Material" style="text-transform:uppercase;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-1 control-label">RedLine</label>

                        <div class="col-sm-3">
                        <input type="text" class="form-control" id="inputRedline" placeholder="Red Line" style="text-transform:uppercase;">
                        </div>
                    </div>
                    </div>
                    <!-- /.box-body -->
                </form>
              </div>

              <div class="box-tools">
                <div class="input-group input-group-sm" style="width: 150px;position:relative;top:5px;">
                  <input type="text" name="key" id="keySearch" class="form-control pull-right" placeholder="Search" value="<?php echo $key; ?>">

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
            <div class="box-body table-responsive no-padding">
              <table class="table table-hover" id="tb-ref">
                <tr>
                  <th style="width:10%;text-align:center;">KODE</th>
                  <th style="width:45%">JENIS</th>
                  <th style="width:15%;text-align:center;">SATUAN</th>
                  <th style="width:10%;">HARGA</th>
                  <th style="width:10%;">RED LINE</th>
                  <th></th>
                </tr>
                <?php foreach ($data as $id => $row) : ?>
                <tr>
                  <td style="text-align:center;"><?php echo $row->kode; ?></td>
                  <td><span id="text-<?php echo $row->kode; ?>"><?php echo $row->jenis; ?><span></td>
                  <td style="text-align:center;"><span id="satuan-<?php echo $row->kode; ?>"><?php echo $row->satuan; ?><span></td>
                  <td><span id="harga-<?php echo $row->kode; ?>"><?php echo toRupiah($row->harga); ?><span></td>
                  <td><span id="redline-<?php echo $row->kode; ?>"><?php echo $row->red_line; ?><span></td>
                  <td style="text-align:right;">
                      <button class="btn btn-default btn-xs btnRefEdit" data-kode="<?php echo $row->kode; ?>" data-jenis="<?php echo $row->jenis; ?>" data-harga="<?php echo $row->harga; ?>" data-satuan="<?php echo $row->satuan; ?>" data-redline="<?php echo $row->red_line; ?>"><i class="fa fa-edit"></i></button>
                      <button class="btn btn-default btn-xs btnRefDelete" data-kode="<?php echo $row->kode; ?>"><i class="fa fa-trash-o"></i></button>
                  </td>
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

<script src="<?php echo WEB_URL; ?>js_control/kansi/referensi.material.js?v=<?php echo JS_VER; ?>"></script>