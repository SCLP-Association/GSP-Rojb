<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
        DAFTAR BIAYA LAIN SO
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Unit</li>
        <li>Aktivasi</li>
        <li class="active">Biaya Lain SO</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="<?php echo WEB_URL; ?>unit/aktivasi/so/biaya_lain/pengeluaran" class="btn btn-primary btn-sm" id="btnInput"><i class="fa fa-fw fa-list-ul"></i> <span>PENGELUARAN BIAYA LAIN SO<span></a>
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
                  <th style="width:65%;text-align:center;">PEKERJAAN</th>
                  <th style="width:15%;" class="text-center">NILAI</th>
                </tr>
                <?php $total = 0; ?>
                <?php foreach ($dataSo as $id => $row) : ?>
                <?php $total += $row->nilai_biaya_lain; ?>
                <tr>
                  <td class="text-center">
                    <a href="<?php echo WEB_URL; ?>unit/aktivasi/so/biaya_lain/input/<?php echo str_replace("/", "-", $row->no_so); ?>">
                        <?php echo $row->no_so; ?>
                    </a>
                  </td>
                  <td class="text-center"><?php echo toRojbDate($row->tgl); ?></td>
                  <td><?php echo $row->pekerjaan; ?></td>
                  <td class="text-right"><?php echo toRupiah($row->nilai_biaya_lain); ?></td>
                </tr>                
                <?php endforeach; ?>
                <tr>
                  <td colspan="3" style="font-weight:bold;" class="text-center">JUMLAH</td>
                  <td style="font-weight:bold;" class="text-right"><?php echo toRupiah($total); ?></td>
                </tr>
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

<script src="<?php echo WEB_URL; ?>js_control/unit/aktivasi.biaya_lain_so.js?v=<?php echo JS_VER; ?>"></script>