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
        DAFTAR STATUS MATERIAL
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Stock</li>
        <li class="active">Status Material</li>
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
                  
                  <input type="text" name="key" style="width:100px;" id="keySearch" class="form-control pull-right" placeholder="Search Kode" value="<?php echo $key; ?>">

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
              <table class="easyui-datagrid aktivasi-table" id="tbl-status-material" style="height:420px;" 
                    singleSelect="true" iconCls="" fitColumn="true">
                <thead frozen="true">
                  <tr>
                    <th field="dummyKode" width="60" align="center"><b>KODE</b></th>
                    <th field="dummyJenis" width="220" align="left"><b>&nbsp;&nbsp;JENIS MATERIAL</b></th>
                  </tr>
                </thead>
                <thead>
                  <tr>
                    <?php $jml = new stdClass(); ?>
                    <?php if (count($dataStock) > 0) : ?>
                    <?php foreach ($dataStock[0] as $id => $value) : ?>
                    <?php if (strtoupper($id) != "KODE" && strtoupper($id) != "JENIS_MATERIAL") : ?>
                    <th field="dummy<?php echo $id; ?>" width="80" align="center"><b><?php echo strtoupper($id); ?></b></th>
                    <?php $jml->{$id} = 0; ?>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <th field="dummyTotal" width="90" align="center"><b>TOTAL</b></th>
                  </tr>
                </thead>
                <tbody>
                  <?php $totalAll = 0; ?>
                  <?php foreach ($dataStock as $id => $row) : ?>
                  <tr>
                    <?php $totalTmp = 0; ?>
                    <?php foreach ($row as $idx => $value) : ?>                  
                    <td>&nbsp;&nbsp;<?php echo ($value == "0") ? "" : $value; ?></td>
                    <?php $totalTmp += (int) $value; ?>
                    <?php if (strtoupper($idx) != "KODE" && strtoupper($idx) != "JENIS_MATERIAL") : ?>
                    <?php $jml->{$idx} += (int) $value; ?>
                    <?php endif; ?>               
                    <?php endforeach; ?>

                    <?php $totalAll += (int) $totalTmp; ?>
                    <td><b><?php echo $totalTmp; ?></b></td>
                  </tr>
                  <?php endforeach; ?>
                  <tr>
                    <td></td>
                    <td>&nbsp;&nbsp;<b>JUMLAH</b></td>
                    <?php foreach ($jml as $idp => $prop) : ?>
                    <td>&nbsp;&nbsp;<b><?php echo $prop; ?></b></td>
                    <?php endforeach; ?>
                    <td><b><?php echo $totalAll; ?></b></td>
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

<script src="<?php echo WEB_URL; ?>js_control/stock/status_material.js?v=<?php echo JS_VER; ?>"></script>