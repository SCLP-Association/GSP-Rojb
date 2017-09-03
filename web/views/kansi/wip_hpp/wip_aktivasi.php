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
        WIP AKTIVASI
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Kansi</li>
        <li>WIP & HPP</li>
        <li class="active">Aktivasi</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="#" id="btnInvoice" class="btn btn-primary btn-sm"><span>INVOICE<span></a>
                &nbsp;&nbsp;
                <span>POSISI TGL <?php echo $posisi_date; ?></span>
              </h3>

              <div class="box-tools">
                <div class="input-group input-group-sm" style="width: 450px;position:relative;top:5px;">

                  <div class="input-group pull-right" style="width:250px;">
                      <div class="input-group-addon">
                          <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right" style="height:30px;" id="daterange" value="<?php //echo $posisi_date; ?>">
                  </div>

                  <div class="input-group-btn">                    
                    <button type="submit" class="btn btn-default" id="btnSearch"><i class="fa fa-search"></i></button>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.box-header -->

            <div class="box-body table-responsive">
              <table class="easyui-datagrid aktivasi-table" id="dg" style="height:420px;" 
                    singleSelect="true" iconCls="" fitColumn="true">
                <!--<thead frozen="true">
                  <tr>
                    <th field="dummyKode" width="70" align="center"><b>KODE</b></th>
                    <th field="dummyJenis" width="220" align="center"><b>JENIS BIAYA</b></th>
                  </tr>
                </thead>-->
                <thead>
                  <tr>
                    <th field="dummyWipSO" align="center" colspan="4"><b>WIP SO</b></th>
                    <th field="dummyPkt" align="center" colspan="3"><b>PKT</b></th>
                    <th field="dummyGr" align="center" rowspan="2" width="80"><b>GR</b></th>
                    <th field="dummyInvoice" align="center" colspan="2"><b>INVOICE</b></th>
                    <th field="dummyHPP" align="center" colspan="3"><b>HPP</b></th>
                  </tr>
                  <tr>
                    <th field="dummyWipSONomor" width="120" align="center"><b>NOMOR</b></th>
                    <th field="dummyWipSOJasa" width="120" align="center"><b>JASA</b></th>
                    <th field="dummyWipSOBiayaLain" width="120" align="center"><b>BIAYA LAIN</b></th>
                    <th field="dummyWipSOMaterial" width="120" align="center"><b>MATERIAL</b></th>

                    <th field="dummyPktNomor" width="100" align="center"><b>NOMOR</b></th>
                    <th field="dummyPktJasa" width="100" align="center"><b>JASA</b></th>
                    <th field="dummyPktMaterial" width="100" align="center"><b>MATERIAL</b></th>

                    <th field="dummyInvoiceNomor" width="100" align="center"><b>NOMOR</b></th>
                    <th field="dummyInvoiceDpp" width="100" align="center"><b>DPP</b></th>

                    <th field="dummyHPPJasa" width="100" align="center"><b>JASA</b></th>
                    <th field="dummyHPPBiayaLain" width="100" align="center"><b>BIAYA LAIN</b></th>
                    <th field="dummyHPPMaterial" width="100" align="center"><b>MATERIAL</b></th>

                  </tr>
                </thead>
                <tbody>
                <?php foreach ($dataWip as $idx => $row) : ?>
                <tr>
                  <td><?php echo $row->no_so; ?></td>
                  <td><?php echo toRupiahBlank($row->wip_so_jasa); ?></td>
                  <td><?php echo toRupiahBlank($row->wip_so_biaya_lain); ?></td>
                  <td><?php echo toRupiahBlank($row->wip_so_material); ?></td>

                  <td><?php echo $row->pkt_no_bukti; ?></td>
                  <td><?php echo toRupiahBlank($row->wip_pkt_jasa); ?></td>
                  <td><?php echo toRupiahBlank($row->wip_pkt_material); ?></td>
                                  
                  <td><?php echo $row->wip_gr; ?></td>
                  <td><?php echo $row->wip_invoice_nomor; ?></td>
                  <td><?php echo toRupiahBlank($row->wip_invoice_dpp); ?></td>

                  <td><?php echo toRupiahBlank($row->wip_hpp_jasa); ?></td>
                  <td><?php echo toRupiahBlank($row->wip_hpp_biaya_lain); ?></td>
                  <td><?php echo toRupiahBlank($row->wip_hpp_material); ?></td>
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

<script src="<?php echo WEB_URL; ?>js_control/kansi/wip_hpp.wip_aktivasi.js?v=<?php echo JS_VER; ?>"></script>