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
        REKAPITULASI BIAYA MATERIAL
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Kansi</li>
        <li>Rekapitulasi</li>
        <li class="active">Biaya Material</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <span style="position:relative;top:5px;">POSISI TGL <?php echo $posisi_date; ?></span>
              </h3>

              <div class="box-tools">
                <div class="input-group input-group-sm" style="width: 450px;position:relative;top:5px;">

                  <div class="input-group pull-right" style="width:250px;">
                      <div class="input-group-addon">
                          <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right" style="height:30px;" id="daterange" value="<?php echo $posisi_date; ?>">
                  </div>

                  <div class="input-group-btn">                    
                    <button type="submit" class="btn btn-default" id="btnSearch"><i class="fa fa-search"></i></button>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.box-header -->

            <div class="box-body table-responsive">
              <table class="easyui-datagrid aktivasi-table" id="tbl-status-material" style="height:420px;" 
                    singleSelect="true" iconCls="" fitColumn="true">
                <thead frozen="true">
                  <tr>
                    <th field="dummyKode" width="70" align="center"><b>KODE</b></th>
                    <th field="dummyJenis" width="220" align="center"><b>JENIS BIAYA</b></th>
                  </tr>
                </thead>
                <thead>
                  <tr>
                    <th field="dummyBiaya" align="center" colspan="4"><b>BIAYA</b></th>
                    <th field="dummyQty" align="center" colspan="4"><b>KUANTITAS</b></th>
                  </tr>
                  <tr>
                    <th field="dummyBiayaSerpo" width="120" align="center"><b>SERPO</b></th>
                    <th field="dummyBiayaAktivasi" width="120" align="center"><b>AKTIVASI</b></th>
                    <th field="dummyBiayaImp" width="120" align="center"><b>IMP</b></th>
                    <th field="dummyBiayaSale" width="120" align="center"><b>SALE</b></th>

                    <th field="dummyQtySerpo" width="100" align="center"><b>SERPO</b></th>
                    <th field="dummyQtyAktivasi" width="100" align="center"><b>AKTIVASI</b></th>
                    <th field="dummyQtyImp" width="100" align="center"><b>IMP</b></th>
                    <th field="dummyQtySale" width="100" align="center"><b>SALE</b></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rekap as $row) : ?>
                  <tr>
                    <td><?php echo $row->kode; ?></td>
                    <td><div style="text-align:left !important;padding-left:5px;"><?php echo $row->jenis; ?></div></td>
                    <td><?php echo toRupiahBlank($row->biaya_serpo); ?></td>
                    <td><?php echo toRupiahBlank($row->biaya_aktivasi); ?></td>
                    <td><?php echo toRupiahBlank($row->biaya_imp); ?></td>
                    <td><?php echo toRupiahBlank($row->biaya_sale); ?></td>

                    <td><?php echo zeroToBlank($row->qty_serpo); ?></td>
                    <td><?php echo zeroToBlank($row->qty_aktivasi); ?></td>
                    <td><?php echo zeroToBlank($row->qty_imp); ?></td>
                    <td><?php echo zeroToBlank($row->qty_sale); ?></td>
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

<script src="<?php echo WEB_URL; ?>js_control/kansi/rekapitulasi.biaya_material.js?v=<?php echo JS_VER; ?>"></script>