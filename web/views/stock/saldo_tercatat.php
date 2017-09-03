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
        SALDO TERCATAT
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Stock</li>
        <li class="active">Saldo Tercatat</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="#" class="btn btn-primary btn-sm" id="btnExport"><i class="fa fa-fw fa-plus"></i> <span>EXPORT<span></a>
              </h3>

              <div class="box-tools">
                <div class="input-group input-group-sm" style="width: 450px;position:relative;top:5px;">
                  
                </div>
              </div>
            </div>
            <!-- /.box-header -->

            <div class="box-body table-responsive">
              <table class="easyui-datagrid aktivasi-table" id="tbl-status-material" style="height:420px;" 
                    singleSelect="true" iconCls="" fitColumn="true">
                <!--<thead frozen="true">
                  <tr>
                    <th field="dummyKode" width="60" align="center"><b>KODE</b></th>
                    <th field="dummyJenis" width="220" align="left"><b>&nbsp;&nbsp;JENIS MATERIAL</b></th>
                  </tr>
                </thead>-->
                <thead>
                  <tr>
                    <th field="dummyKode" width="70" align="center" rowspan="2"><b>KODE</b></th>
                    <th field="dummyJenis" width="280" rowspan="2">&nbsp;&nbsp;<b>JENIS MATERIAL</b></th>
                    <th field="dummySatuan" width="120" align="center" rowspan="2"><b>SATUAN MATERIAL</b></th>
                    <th field="dummySaldo" align="center" colspan="2"><b>SALDO AKHIR</b></th>
                  </tr>
                  <tr>
                    <th field="dummyQty" width="80" align="center"><b>QTY</b></th>
                    <th field="dummyNilai" width="160" align="right"><b>NILAI</b>&nbsp;&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $totalQty = 0; $totalNilai = 0; ?>
                  <?php foreach ($dataSaldo as $idx => $row) : ?>
                  <?php $totalQty += (int) $row->total_qty; $totalNilai += (int) $row->total_nilai; ?>
                  <tr>
                    <td><?php echo $row->kode_material; ?></td>
                    <td>&nbsp;&nbsp;<a href="<?php echo WEB_URL; ?>stock/kertas/kerja/<?php echo $row->kode_material; ?>" style="text-decoration:underline;color:blue;"><?php echo $row->jenis_material; ?></a></td>
                    <td><?php echo $row->satuan_material; ?></td>
                    <td><?php echo $row->total_qty; ?></td>
                    <td><?php echo toRupiah($row->total_nilai); ?>&nbsp;&nbsp;</td>
                  </tr>
                  <?php endforeach; ?>
                  <tr>
                    <td></td>
                    <td><center><b>JUMLAH</b></center></td>
                    <td></td>
                    <td><b><?php echo $totalQty; ?></b></td>
                    <td><b><?php echo toRupiah($totalNilai); ?></b>&nbsp;&nbsp;</td>
                  </tr>
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

<!--<script src="<?php echo WEB_URL; ?>js_control/stock/saldo_tercatat.js?v=<?php echo JS_VER; ?>"></script>-->