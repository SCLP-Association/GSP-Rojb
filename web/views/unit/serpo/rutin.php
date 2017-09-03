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
        PENGELUARAN RUTIN SERPO
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Unit</li>
        <li>Serpo</li>
        <li class="active">Rutin</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="<?php echo WEB_URL; ?>unit/serpo/input" class="btn btn-primary btn-sm" id="btnInput"><i class="fa fa-fw fa-plus"></i> <span>INPUT<span></a>
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
              <table class="easyui-datagrid aktivasi-table" style="height:420px;" 
                    singleSelect="true" iconCls="" fitColumn="true">
                <thead frozen="true">
                  <tr>
                    <th field="productid" width="140" align="center">NO. BUKTI</th>
                  </tr>
                </thead>
                <thead>
                  <tr>
                    <th field="dummyTgl" width="80" align="center">TANGGAL</th>
                    <th field="dummyKw" width="140" align="center">NO. KW</th>
                    <th field="dummyKode" width="65" align="center">KODE</th>
                    <th field="dummyJenis" width="260">&nbsp;&nbsp;JENIS BIAYA</th>
                    <th field="dummyTransaksi" width="260">&nbsp;&nbsp;DESKRIPSI TRANSAKSI</th>
                    <th field="dummyNilai" width="120" align="right">NILAI&nbsp;&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($data as $id => $row) : ?>
                  <?php $rowspan = count($row->detail); ?>
                  <tr>
                    <td><?php echo $row->no_bukti; ?></td>
                    <td><span><?php echo toRojbDate($row->tgl); ?><span></td>
                    <td>
                      <a href="" style="text-decoration: underline; color: blue;" class="no_kw"><span><?php echo $row->no_kuitansi; ?><span></a>
                    </td>
                    <td><span><?php echo $row->kode_biaya; ?><span></td>
                    <td title="<?php echo $row->jenis_biaya; ?>"><span>&nbsp;&nbsp;<?php echo $row->jenis_biaya; ?><span></td>
                    <td>
                      <?php foreach ($row->detail as $idx => $detail): ?>
                      <?php if ($idx + 1 != $rowspan): ?>
                          <div class="detail-in-table" title="<?php echo $detail->deskripsi; ?>"><?php echo $detail->deskripsi; ?></div>
                      <?php else: ?>
                          <div class="detail-in-table-inv" title="<?php echo $detail->deskripsi; ?>"><?php echo $detail->deskripsi; ?></div>
                      <?php endif; ?>
                      <?php endforeach; ?>
                    </td>
                    <td>
                      <?php foreach ($row->detail as $idx => $detail): ?>
                      <?php if ($idx + 1 != $rowspan): ?>
                          <div class="detail-in-table"><?php echo toRupiah($detail->nilai); ?>&nbsp;</div>
                      <?php else: ?>
                          <div class="detail-in-table-inv"><?php echo toRupiah($detail->nilai); ?>&nbsp;</div>
                      <?php endif; ?>
                      <?php endforeach; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!--
            <div class="box-body table-responsive">
              <table class="table table-hover table-bordered aktivasi-table" id="tb-ref">
                <tr>
                  <th style="width:14%;text-align:center;">NO. BUKTI</th>
                  <th style="width:8%;text-align:center;">TANGGAL</th>
                  <th style="width:15%;text-align:center;">NO. KW</th>
                  <th style="width:5%;text-align:center;">KODE</th>
                  <th style="width:24%;text-align:center;">JENIS BIAYA</th>
                  <th style="width:23%;text-align:center;">DESKRIPSI TRANSAKSI</th>
                  <th style="width:11%;text-align:center;">NILAI</th>
                </tr>
                <?php foreach ($data as $id => $row) : ?>
                <?php $rowspan = count($row->detail); ?>
                <tr>
                  <td class="mp-zero" style="text-align:center;vertical-align:middle;"><?php echo $row->no_bukti; ?></td>
                  <td class="mp-zero" style="text-align:center;vertical-align:middle;"><span><?php echo toRojbDate($row->tgl); ?><span></td>
                  <td class="mp-zero" style="text-align:center;vertical-align:middle;">
                    <a href="" class="no_kw"><span><?php echo $row->no_kuitansi; ?><span></a>
                  </td>
                  <td class="mp-zero" style="text-align:center;vertical-align:middle;"><span><?php echo $row->kode_biaya; ?><span></td>
                  <td class="mp-zero" style="vertical-align:middle;padding-left:7px !important;"><span><?php echo $row->jenis_biaya; ?><span></td>
                  <td class="mp-zero">
                    <?php foreach ($row->detail as $idx => $detail): ?>
                    <?php if ($idx + 1 != $rowspan): ?>
                        <div style="line-height:30px;border-bottom:1px solid #a3a3a3;padding-left:7px;"><?php echo $detail->deskripsi; ?></div>
                    <?php else: ?>
                        <div style="line-height:30px;padding-left:7px;"><?php echo $detail->deskripsi; ?></div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                  </td>
                  <td class="mp-zero" style="text-align:right;">
                    <?php foreach ($row->detail as $idx => $detail): ?>
                    <?php if ($idx + 1 != $rowspan): ?>
                        <div style="line-height:30px;border-bottom:1px solid #a3a3a3;padding-right:7px;"><?php echo toRupiah($detail->nilai); ?></div>
                    <?php else: ?>
                        <div style="line-height:30px;padding-right:7px;"><?php echo toRupiah($detail->nilai); ?></div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </table>
            </div>
            -->
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
      </div>

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script src="<?php echo WEB_URL; ?>js_control/unit/serpo.rutin.js?v=<?php echo JS_VER; ?>"></script>