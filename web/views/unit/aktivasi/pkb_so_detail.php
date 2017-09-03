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
        DETAIL PKB <?php echo $no_pkb; ?>
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Unit</li>
        <li>Aktivasi</li>
        <li class="active">Detail PKB</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="<?php echo WEB_URL; ?>unit/aktivasi/so/pkb" class="btn btn-primary btn-sm"><i class="fa fa-fw fa-ban"></i> <span>TUTUP<span></a>
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
                <table class="easyui-datagrid" style="height:420px;" 
                    singleSelect="true" iconCls="" fitColumn="true">
                    <thead frozen="true">
                        <tr>
                            <th field="productid" width="160" align="center"><b>NO. PKB</b></th>
                        </tr>
                    </thead>
                    <thead>
                        <tr>
                            <th field="dummyTglPkb" width="75" align="center"><b>TGL PKB</b></th>
                            <th field="dummyNoSo" width="75" align="center"><b>NO. SO</b></th>
                            <th field="dummyTglSo" width="75" align="center"><b>TGL. SO</b></th>
                            <th field="dummyPtl" width="120" align="center"><b>PTL</b></th>
                            <th field="dummyPekerjaan" width="180" align="center"><b>PEKERJAAN</b></th>
                            <th field="dummyDetailKhs" width="480" align="left">&nbsp;&nbsp;<b>DETAIL</b></th>
                            <th field="dummyQKhs" width="50" align="center"><b>Q</b></th>
                            <th field="dummyTotalKhs" width="120" align="right"><b>NILAI&nbsp;&nbsp;</b></th>
                            <th field="dummyKodeMaterial" width="60" align="center"><b>KODE</b></th>
                            <th field="dummyJenisMaterial" width="180" align="left">&nbsp;&nbsp;<b>JENIS MATERIAL</b></th>
                            <th field="dummyQMaterial" width="50" align="center"><b>Q</b></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataSoPkb as $idx => $row) : ?>
                        <tr>
                            <td><strong><?php echo $row->pkb_no_bukti; ?></strong></td>
                            <td><?php echo toRojbDate($row->pkb_tgl); ?></td>
                            <td><?php echo $row->no_so; ?></td>
                            <td><?php echo toRojbDate($row->tgl); ?></td>
                            <td><?php echo $row->ptl; ?></td>
                            <td><?php echo $row->pekerjaan; ?></td>
                            <!-- KHS -->
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
                            <td>                                
                                <?php foreach ($row->khs as $idx2 => $rowKhs) : ?>
                                    <?php if ($idx2 + 1 != $rowspanKhs): ?>
                                        <div class="detail-in-table"><?php echo toRupiah($rowKhs->total); ?>&nbsp;&nbsp;</div>
                                    <?php else: ?>
                                        <div  class="detail-in-table-inv"><?php echo toRupiah($rowKhs->total); ?>&nbsp;&nbsp;</div>
                                    <?php endif; ?>
                                <?php endforeach; ?>                                
                            </td>
                            <!-- MATERIAL -->
                            <td>
                                <?php $rowspanMaterial = count($row->material); ?>                                
                                <?php foreach ($row->material as $idx3 => $rowMaterial) : ?>
                                    <?php if ($idx3 + 1 != $rowspanMaterial): ?>
                                        <div class="detail-in-table" title="<?php echo $rowMaterial->kode_material; ?>"><?php echo $rowMaterial->kode_material; ?></div>
                                    <?php else: ?>
                                        <div class="detail-in-table-inv" title="<?php echo $rowMaterial->kode_material; ?>"><?php echo $rowMaterial->kode_material; ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>                                
                            </td>
                            <td>                             
                                <?php foreach ($row->material as $idx3 => $rowMaterial) : ?>
                                    <?php if ($idx3 + 1 != $rowspanMaterial): ?>
                                        <div class="detail-in-table" title="<?php echo $rowMaterial->jenis_material; ?>"><?php echo $rowMaterial->jenis_material; ?></div>
                                    <?php else: ?>
                                        <div class="detail-in-table-inv" title="<?php echo $rowMaterial->jenis_material; ?>"><?php echo $rowMaterial->jenis_material; ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>                                
                            </td>
                            <td>                             
                                <?php foreach ($row->material as $idx3 => $rowMaterial) : ?>
                                    <?php if ($idx3 + 1 != $rowspanMaterial): ?>
                                        <div class="detail-in-table"><?php echo $rowMaterial->qty; ?></div>
                                    <?php else: ?>
                                        <div class="detail-in-table-inv"><?php echo $rowMaterial->qty; ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>                                
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

<script src="<?php echo WEB_URL; ?>js_control/unit/aktivasi.pkb_so_detail.js?v=<?php echo JS_VER; ?>"></script>