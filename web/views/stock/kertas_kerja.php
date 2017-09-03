<!-- Include Jquery Easy UI -->
<link rel="stylesheet" type="text/css" href="<?php echo WEB_URL; ?>js_easyui/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="<?php echo WEB_URL; ?>js_easyui/themes/icon.css">
<script src="<?php echo WEB_URL; ?>js_easyui/jquery.easyui.min.js?v=<?php echo JS_VER; ?>"></script>
<style>
.datagrid-cell { font-size: 13px !important; margin: 0 !important; padding: 0 !important; }
</style>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_URL; ?>themes/dist/css/easyui_custom_table.css">
<script>
var KODE_MATERIAL = "<?php echo $kode; ?>";
</script>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
             KERTAS KERJA PENILAIAN MATERIAL
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Stock</li>
        <li class="active">Kertas Kerja</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="<?php echo WEB_URL; ?>stock/saldo/tercatat" class="btn btn-primary btn-sm" id="btnTutup"><i class="fa fa-fw fa-ban"></i> <span>TUTUP<span></a>
                <a href="#" class="btn btn-primary btn-sm" id="btnExport"><i class="fa fa-fw fa-plus"></i> <span>EXPORT<span></a>
                &nbsp;&nbsp;<?php echo $kode . " " . $dataKertasKerja[0]->jenis_material; ?>
              </h3>

              <div class="box-tools">
                <div class="input-group input-group-sm" style="width: 450px;position:relative;top:5px;">

                  <div class="input-group pull-right" style="width:250px;">
                    <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </div>
                    <input type="text" class="form-control pull-right" style="height:30px;" id="daterange" value="<?php echo $range_date; ?>">
                  </div>

                  <div class="input-group-btn">                    
                    <button type="submit" class="btn btn-default" id="btnSearch"><i class="fa fa-search"></i></button>
                  </div>
                </div>
            </div>
            <!-- /.box-header -->

            <div class="box-body table-responsive">
              <table class="easyui-datagrid aktivasi-table" id="tbl-status-material" style="height:420px;" 
                    singleSelect="true" iconCls="" fitColumn="true">
                <thead frozen="true">
                  <tr>
                    <th field="dummyTgl" width="80" align="center"><b>TGL</b></th>
                    <th field="dummyDokumen" width="220" align="center"><b>DOKUMEN</b></th>
                  </tr>
                </thead>
                <thead>
                  <tr>
                    <th field="dummyDeskripsi" width="240" align="center" rowspan="2">&nbsp;&nbsp;<b>DESKRIPSI</b></th>
                    <th field="dummyPerolehan" align="center" colspan="3"><b>PEROLEHAN</b></th>
                    <th field="dummyPengeluaran" align="center" colspan="3"><b>PENGELUARAN</b></th>
                    <th field="dummyTotal" align="center" colspan="2"><b>TOTAL</b></th>
                  </tr>
                  <tr>
                    <th field="dummyPerolehanQty" width="80" align="center"><b>QTY</b></th>
                    <th field="dummyPerolehanHpu" width="80" align="right"><b>HPU</b>&nbsp;&nbsp;</th>
                    <th field="dummyPerolehanNilai" width="100" align="right"><b>NILAI</b>&nbsp;&nbsp;</th>
                    <th field="dummyPengeluaranQty" width="80" align="center"><b>QTY</b></th>
                    <th field="dummyPengeluaranHpu" width="80" align="right"><b>HPU</b>&nbsp;&nbsp;</th>
                    <th field="dummyPengeluaranNilai" width="100" align="right"><b>NILAI</b>&nbsp;&nbsp;</th>
                    <th field="dummyTotalHpu" width="80" align="center"><b>QTY</b>&nbsp;&nbsp;</th>
                    <th field="dummyTotalNilai" width="100" align="right"><b>NILAI</b>&nbsp;&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($dataKertasKerja as $idx => $row) : ?>
                  <tr>
                    <td><?php echo toRojbDate($row->tgl); ?></td>
                    <td><?php echo $row->no_dokumen; ?></td>
                    <td>&nbsp;&nbsp;<?php echo $row->deskripsi; ?></td>
                    <td><?php echo $row->perolehan_qty; ?></td>
                    <td><?php echo toRupiah($row->perolehan_hpu); ?>&nbsp;&nbsp;</td>
                    <td><?php echo toRupiah($row->perolehan_nilai); ?>&nbsp;&nbsp;</td>
                    <td><?php echo $row->pengeluaran_qty; ?></td>
                    <td><?php echo toRupiah($row->pengeluaran_hpu); ?>&nbsp;&nbsp;</td>
                    <td><?php echo toRupiah($row->pengeluaran_nilai); ?>&nbsp;&nbsp;</td>
                    <td><?php echo $row->total_qty; ?></td>
                    <td><?php echo toRupiah($row->total_nilai); ?>&nbsp;&nbsp;</td>
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

<script src="<?php echo WEB_URL; ?>js_control/stock/kertas_kerja.js?v=<?php echo JS_VER; ?>"></script>