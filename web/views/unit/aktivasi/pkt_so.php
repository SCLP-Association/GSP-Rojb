<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
        DAFTAR PKT SO
        </h1>
        <ol class="breadcrumb">
        <li><a href="<?php echo WEB_URL; ?>dashboard/home"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Unit</li>
        <li>Aktivasi</li>
        <li class="active">PKT SO</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <a href="<?php echo WEB_URL; ?>unit/aktivasi/so/pkt/detail" class="btn btn-primary btn-sm"><i class="fa fa-fw fa-list-ul"></i> <span>DETAIL PKT SO<span></a>
                <a href="<?php echo WEB_URL; ?>unit/aktivasi/so/pkt/material/detail" class="btn btn-primary btn-sm"><i class="fa fa-fw fa-list-ul"></i> <span>DETAIL MATERIAL SO<span></a>
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
                  <th style="width:4%;text-align:center;"></th>
                  <th style="width:8%;text-align:center;">NO. SO</th>
                  <th style="width:22%;text-align:left;">NO. PKB</th>
                  <th style="text-align:center;">PEKERJAAN</th>
                  <th style="width:18%;text-align:center;">PKT</th>
                  <th style="width:6%;text-align:center;">TGL</th>
                  <th style="width:13%;" class="text-center">NILAI</th>
                </tr>
                <?php $total = 0; ?>
                <?php foreach ($dataSo as $id => $row) : ?>
                <?php $total += (int) $row->pkt_nilai; ?>
                <tr>
                  <td class="text-center">
                    <!--<input type="radio" name="pktCheck" class="pkt-check" data-so="<?php echo $row->no_so; ?>"/>-->
                    <?php if (strlen($row->pkt_no_bukti) == 0 && strlen($row->pkb_no_bukti) != 0) : ?>
                      <button class="btn btn-default btn-xs btn-buat-pkt" data-so="<?php echo $row->no_so; ?>" title="Buat PKT"><i class="fa fa-fw fa-plus"></i></button>
                    <?php else: ?>
                      <span class="text-muted"><i class="fa fa-fw fa-ban"></i></span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?php echo $row->no_so; ?>
                  </td>
                  <td class="text-left" id="pkb-<?php echo str_replace("/", "-", $row->no_so); ?>">
                    <?php if (strlen($row->pkt_no_bukti) == 0 ) : ?>
                        <!--<a href="" title="edit pkb" class="linkPkb" data-so="<?php echo $row->no_so; ?>">
                          <?php echo $row->pkb_no_bukti; ?>
                        </a>-->
                        <a href="<?php echo WEB_URL; ?>unit/aktivasi/so/pkt/view_by_pkb/<?php echo str_replace("/", "_", $row->pkb_no_bukti); ?>" title="view detail pkb" data-so="<?php echo $row->no_so; ?>">
                          <span class="classNoPkb"><?php echo $row->pkb_no_bukti; ?></span>
                        </a>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <button class="btn btn-default btn-xs linkPkb" data-so="<?php echo $row->no_so; ?>" title="Edit PKB"><i class="fa fa-fw fa-edit"></i></button>
                    <?php else: ?>
                        <?php echo $row->pkb_no_bukti; ?>
                    <?php endif; ?>
                  </td>
                  <td><?php echo $row->pekerjaan; ?></td>
                  <td class="text-center">
                    <span id="pkt-<?php echo str_replace("/", "-", $row->no_so); ?>" class="pkt_no_bukti">
                        <a href="<?php echo WEB_URL; ?>unit/aktivasi/so/pkt/view/<?php echo $row->pkt_no_bukti; ?>" class="linkPkt"><?php echo $row->pkt_no_bukti; ?></a>
                    </span>
                  </td>
                  <td class="text-center tgl-pkt"><?php echo toRojbDate($row->pkt_tgl); ?></td>
                  <td class="text-right nilai-pkt"><?php echo toRupiah($row->pkt_nilai); ?></td>
                </tr>                
                <?php endforeach; ?>
                <tr>
                  <td colspan="6" style="font-weight:bold;" class="text-center">JUMLAH</td>
                  <td style="font-weight:bold;" class="text-right" id="jmlKhsPkt"><?php echo toRupiah($total); ?></td>
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

<!-- modal pkt -->
<div class="modal fade" id="modalPkt" tabindex="-1" role="dialog" aria-labelledby="modalPkbLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalPkbLabel">Input PKT Baru</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" id="form-pkb">
            <div class="box-body">
            <div class="form-group" id="fg-noPkt">
                <label for="inputMNoPkb" class="col-sm-3 control-label">NO. PKT</label>

                <div class="col-sm-7">
                <input type="hidden" id="inputMNoSo" name="no_so"/>
                <input type="text" class="form-control" id="inputMNoPkt" name="pkt_no_bukti" placeholder="NO. PKT" value="" style="text-transform:uppercase;">
                </div>
            </div>
            <div class="form-group">
                <label for="inputMTglPkt" class="col-sm-3 control-label">TGL. PKT</label>

                <div class="col-sm-7">
                <input type="hidden" class="form-control" id="inputMTglPkt" name="pkt_tgl" value="<?php echo $tgl; ?>" readonly>
                <input type="text" class="form-control" id="inputMTglPktView" placeholder="TGL SO" value="<?php echo $tgl_view; ?>" readonly>
                </div>
            </div>
            </div>
            <!-- /.box-body -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="btnCancelPkt" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-primary" id="btnBuatPkt">BUAT PKT</button>
      </div>
    </div>
  </div>
</div>

<!-- modal pkt material -->
<div class="modal fade bs-example-modal-lg" id="modalMaterial" tabindex="-1" role="dialog" aria-labelledby="modalMaterialLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalMaterialLabel">
          Input Material PKT
          <span class="pull-right" id="labelNoPkt" style="margin-right:15px;"></span>
        </h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" id="form-material">
            <div class="box-body">
            
            <table class="table" style="width:100%;margin:0;">
                <tr style="background:#e9e9e9;">
                    <th style="width:13%;text-align:center">KODE</th>
                    <th style="text-align:center">JENIS MATERIAL</th>
                    <th style="width:14%;text-align:center">SATUAN</th>
                    <th style="width:17%;text-align:center">Q</th>
                </tr>
            </table>
            <div style="height:300px;overflow: scroll;overflow-x: hidden;">
            <table class="table table-hover table-bordered tbl-material-pkt" id="tbl-material">                
                <?php foreach ($dataMaterial as $idx => $row) : ?>
                <tr class="row-material" id="kode-<?php echo $row->kode; ?>">
                    <td style="text-align:center;width:13%;">
                        <label>
                            <span style="position:relative;top:2px;"><input type="checkbox" class="material-check"/> &nbsp;</span>
                            <?php echo $row->kode; ?>
                        </label>
                    </td>
                    <td><?php echo $row->jenis; ?></td>
                    <td style="width:20%;text-align:center;"><?php echo $row->satuan; ?></td>
                    <td style="text-align:center;width:13%;">
                        <input type="number" min="0" max="" id="material-<?php echo $row->kode; ?>" data-kode="<?php echo $row->kode; ?>" data-jenis="<?php echo $row->jenis; ?>" style="text-align:center;width:70px;" maxlength="5" class="material_qty" name="material_qty[]" onkeypress='return event.charCode >= 48 && event.charCode <= 57'/>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            </div>
            <table class="table" style="width:100%%;margin:0;">
              <tr style="background:#f3f3f3;">
                  <th style="text-align:center">JUMLAH</th>
                  <th style="width:25.5%;text-align:right">
                      <input type="text" readonly="true" style="margin-left:6px;margin-right:25px;text-align:center;width:71px;" id="jml_material" name="jumlah_material"/>
                  </th>
              </tr>
          </table>

            </div>
            <!-- /.box-body -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="btnCancel" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-default" id="btnResetMaterial">RESET</button>
        <button type="button" class="btn btn-primary" id="btnSimpanMaterial">SIMPAN</button>
      </div>
    </div>
  </div>
</div>

<!-- modal pkt khs -->
<div class="modal fade bs-example-modal-lg" id="modalKhs" tabindex="-1" role="dialog" aria-labelledby="modalMaterialLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalKhsLabel">
          Input KHS PKT
          <span class="pull-right" id="labelNoPkt2" style="margin-right:15px;"></span>
        </h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" id="form-khs">
            <div class="box-body">
            
            <table class="table" style="width:100%;margin:0;">
                <tr style="background:#e9e9e9;">
                    <th style="width:13%;text-align:center">KODE</th>
                    <th style="text-align:center">ITEM</th>
                    <th style="width:6%;text-align:center">Q</th>
                    <th style="width:25%;text-align:center">TOTAL KHS</th>
                </tr>
            </table>
            <div style="height:300px;overflow: scroll;overflow-x: hidden;">
            <table class="table table-hover table-bordered" id="tbl-khs">                
                <?php foreach ($dataKhs as $idx => $row) : ?>
                <tr class="row-material">
                    <td style="text-align:center;width:13%;">
                        <label>
                            <span style="position:relative;top:2px;"><input type="checkbox" class="khs-check"/> &nbsp;</span>
                            <?php echo $row->kode; ?>
                        </label>
                    </td>
                    <td><?php echo $row->jenis; ?></td>
                    <td style="text-align:center;width:13%;">
                        <input type="text" id="khs-<?php echo $row->kode; ?>" data-kode="<?php echo $row->kode; ?>" data-jenis="<?php echo $row->jenis; ?>" style="text-align:center;width:70px;" maxlength="5" class="khs_qty" name="khs_qty[]" onkeypress='return event.charCode >= 48 && event.charCode <= 57'/>
                    </td>
                    <td style="width:20%;">
                      <input type="hidden" id="harga-khs-<?php echo $row->kode; ?>" class="khs_harga" value="<?php echo $row->harga; ?>"/>
                      <input type="text" value="0" id="total-harga-khs-<?php echo $row->kode; ?>" class="total-harga-khs" style="text-align:right;" class="text-right" readonly />
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            </div>
            <table class="table" style="width:100%%;margin:0;">
              <tr style="background:#f3f3f3;">
                  <th style="text-align:center">JUMLAH</th>
                  <th style="width:25.5%;text-align:center">
                      <input type="text" readonly="true" style="margin-left:6px;text-align:right;width:138px;" id="jml_khs" name="jumlah_khs"/>
                  </th>
              </tr>
          </table>

            </div>
            <!-- /.box-body -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="btnCancel" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-default" id="btnResetKhs">RESET</button>
        <button type="button" class="btn btn-primary" id="btnSimpanKhs">SIMPAN</button>
      </div>
    </div>
  </div>
</div>

<!-- modal pkb material -->
<div class="modal fade bs-example-modal-lg" id="modalPkbMaterial" tabindex="-1" role="dialog" aria-labelledby="modalPkbMaterialLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalPkbMaterialLabel">
          Edit Material PKB
          <span class="pull-right" id="labelNoPkb" style="margin-right:15px;"></span>
        </h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" id="form-material">
            <div class="box-body">
            
            <table class="table" style="width:100%;margin:0;">
                <tr style="background:#e9e9e9;">
                    <th style="width:13%;text-align:center">KODE</th>
                    <th style="text-align:center">JENIS MATERIAL</th>
                    <th style="width:14%;text-align:center">SATUAN</th>
                    <th style="width:17%;text-align:center">Q</th>
                </tr>
            </table>
            <div style="height:300px;overflow: scroll;overflow-x: hidden;">
            <table class="table table-hover table-bordered" id="tbl-material-pkb">                
                <?php foreach ($dataMaterial as $idx => $row) : ?>
                <tr class="row-material" id="tr-pkb-material-<?php echo $row->kode; ?>">
                    <td style="text-align:center;width:13%;">
                        <label>
                            <span style="position:relative;top:2px;"><input type="checkbox" class="material-pkb-check"/> &nbsp;</span>
                            <?php echo $row->kode; ?>
                        </label>
                    </td>
                    <td><?php echo $row->jenis; ?></td>
                    <td style="width:20%;text-align:center;"><?php echo $row->satuan; ?></td>
                    <td style="text-align:center;width:13%;">
                        <input type="text" id="material-pkb-<?php echo $row->kode; ?>" data-kode="<?php echo $row->kode; ?>" data-jenis="<?php echo $row->jenis; ?>" style="text-align:center;width:70px;" maxlength="5" class="material_pkb_qty" name="material_pkb_qty[]" onkeypress='return event.charCode >= 48 && event.charCode <= 57'/>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            </div>
            <table class="table" style="width:100%%;margin:0;">
              <tr style="background:#f3f3f3;">
                  <th style="text-align:center">JUMLAH</th>
                  <th style="width:25.5%;text-align:right">
                      <input type="text" readonly="true" style="margin-left:6px;margin-right:25px;text-align:center;width:71px;" id="jml_pkb_material" name="jumlah_pkb_material"/>
                  </th>
              </tr>
          </table>

            </div>
            <!-- /.box-body -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="btnCancelPkbMaterial" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-default" id="btnResetPkbMaterial">RESET</button>
        <button type="button" class="btn btn-primary" id="btnSimpanPkbMaterial">SIMPAN</button>
      </div>
    </div>
  </div>
</div>

<!-- modal pkb khs -->
<div class="modal fade bs-example-modal-lg" id="modalPkbKhs" tabindex="-1" role="dialog" aria-labelledby="modalPkbKhsLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalPkbKhsLabel">
          Edit KHS PKB
          <span class="pull-right" id="labelNoPkb2" style="margin-right:15px;"></span>
        </h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" id="form-khs">
            <div class="box-body">
            
            <table class="table" style="width:100%;margin:0;">
                <tr style="background:#e9e9e9;">
                    <th style="width:13%;text-align:center">KODE</th>
                    <th style="text-align:center">ITEM</th>
                    <th style="width:6%;text-align:center">Q</th>
                    <th style="width:25%;text-align:center">TOTAL KHS</th>
                </tr>
            </table>
            <div style="height:300px;overflow: scroll;overflow-x: hidden;">
            <table class="table table-hover table-bordered" id="tbl-khs-pkb">                
                <?php foreach ($dataKhs as $idx => $row) : ?>
                <tr class="row-material" id="tr-pkb-khs-<?php echo $row->kode; ?>">
                    <td style="text-align:center;width:13%;">
                        <label>
                            <span style="position:relative;top:2px;"><input type="checkbox" class="khs-pkb-check"/> &nbsp;</span>
                            <?php echo $row->kode; ?>
                        </label>
                    </td>
                    <td><?php echo $row->jenis; ?></td>
                    <td style="text-align:center;width:13%;">
                        <input type="text" id="khs-pkb-<?php echo $row->kode; ?>" data-kode="<?php echo $row->kode; ?>" data-jenis="<?php echo $row->jenis; ?>" style="text-align:center;width:70px;" maxlength="5" class="khs_pkb_qty" name="khs_pkb_qty[]" onkeypress='return event.charCode >= 48 && event.charCode <= 57'/>
                    </td>
                    <td style="width:20%;">
                      <input type="hidden" id="harga-khs-pkb-<?php echo $row->kode; ?>" class="khs_pkb_harga" value="<?php echo $row->harga; ?>"/>
                      <input type="text" value="0" id="total-harga-khs-pkb-<?php echo $row->kode; ?>" class="total-harga-khs-pkb" style="text-align:right;" class="text-right" readonly />
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            </div>
            <table class="table" style="width:100%%;margin:0;">
              <tr style="background:#f3f3f3;">
                  <th style="text-align:center">JUMLAH</th>
                  <th style="width:25.5%;text-align:center">
                      <input type="text" readonly="true" style="margin-left:6px;text-align:right;width:138px;" id="jml_pkb_khs" name="jumlah_pkb_khs"/>
                  </th>
              </tr>
          </table>

            </div>
            <!-- /.box-body -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="btnCancelPkbKhs" data-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-default" id="btnResetPkbKhs">RESET</button>
        <button type="button" class="btn btn-primary" id="btnSimpanPkbKhs">SIMPAN</button>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo WEB_URL; ?>js_control/unit/aktivasi.pkt_so.js?v=<?php echo JS_VER; ?>"></script>