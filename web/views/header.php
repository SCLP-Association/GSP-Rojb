<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $title; ?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="<?php echo WEB_URL; ?>themes/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo WEB_URL; ?>themes/dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="<?php echo WEB_URL; ?>themes/dist/css/skins/_all-skins.min.css">
  <!-- Additional CSS -->
  <link rel="stylesheet" href="<?php echo WEB_URL; ?>themes/dist/css/additional.css?v=<?php echo JS_VER; ?>">

  <!-- jQuery 2.2.3 -->
  <script src="<?php echo WEB_URL; ?>themes/plugins/jQuery/jquery-2.2.3.min.js"></script>
  <script>
  var WEB_URL = "<?php echo WEB_URL; ?>";
  var API_URL = "<?php echo WEB_API; ?>";
  var TOKEN = "<?php echo $_SESSION["token"]; ?>";
  </script>

  <!-- Select2 -->
  <link rel="stylesheet" href="<?php echo WEB_URL; ?>themes/plugins/select2/select2.min.css">
  <script src="<?php echo WEB_URL; ?>themes/plugins/select2/select2.full.min.js"></script>

  <!-- Lobibox -->
  <link rel="stylesheet" href="<?php echo WEB_URL; ?>themes/plugins/lobibox/dist/css/lobibox.min.css">
  <script src="<?php echo WEB_URL; ?>themes/plugins/lobibox/dist/js/lobibox.min.js"></script>

  <!-- jQuery MaskMoney -->
  <script src="<?php echo WEB_URL; ?>themes/plugins/maskMoney/jquery.maskMoney.min.js"></script>

  <!-- Common Function -->
  <script src="<?php echo WEB_URL; ?>js_control/common.func.js?v=<?php echo JS_VER; ?>"></script>

  <!-- date-range-picker -->
  <link rel="stylesheet" href="<?php echo WEB_URL; ?>themes/plugins/daterangepicker/daterangepicker.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
  <script src="<?php echo WEB_URL; ?>themes/plugins/daterangepicker/daterangepicker.js"></script>

  <!-- jQuery Confirm -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.1.0/jquery-confirm.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.1.0/jquery-confirm.min.js"></script>

  <!-- InputMask -->
  <script src="<?php echo WEB_URL; ?>themes/plugins/input-mask/jquery.inputmask.js"></script>
  <script src="<?php echo WEB_URL; ?>themes/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
  <script src="<?php echo WEB_URL; ?>themes/plugins/input-mask/jquery.inputmask.extensions.js"></script>

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<!-- ADD THE CLASS layout-top-nav TO REMOVE THE SIDEBAR. -->
<body class="hold-transition skin-blue fixed sidebar-mini">
<div class="wrapper">

    <header class="main-header">
    <!-- Logo -->
    <a href="../../index2.html" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>S</b>AR</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>SIAKANGROSI</b>GSP</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?php echo WEB_URL; ?>themes/dist/img/user2-160x160.jpg" class="user-image" alt="User Image">
              <span class="hidden-xs">Alexander Pierce</span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="<?php echo WEB_URL; ?>themes/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">

                <p>
                  Alexander Pierce - Web Developer
                  <small>Member since Nov. 2012</small>
                </p>
              </li>
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="#" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="#" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
    </header>

      <!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <!--<div class="user-panel">
        <div class="pull-left image">
          <img src="<?php echo WEB_URL; ?>themes/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>Alexander Pierce</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>-->

      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
        <li class="header">MAIN NAVIGATION</li>

        <!-- TOP: KANSI -->
        <li class="treeview <?php if($menu["first"] == 1) echo "active"; ?>">
            <a href="#">
                <i class="fa fa-folder"></i> <span>KANSI</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                <!-- PETTY CASH -->
                <li class="<?php if($menu["second"] == 11) echo "active"; ?>">
                    <a href="<?php echo WEB_URL; ?>kansi/petty_cash"><i class="fa fa-circle-o"></i> <span>PETTY CASH</span></a>
                </li>

                <!-- WIP DAN HPP -->
                <li class="<?php if($menu["second"] == 12) echo "active"; ?>">
                  <a href="#"><i class="fa fa-circle-o"></i> WIP DAN HPP
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li class="<?php if($menu["third"] == 121) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/wip_hpp/serpo_pm"><i class="fa fa-circle-o"></i> SERVICE POINT DAN PM</a></li>
                    <li class="<?php if($menu["third"] == 122) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/wip_hpp/aktivasi"><i class="fa fa-circle-o"></i> AKTIVASI</a></li>
                    <li class="<?php if($menu["third"] == 123) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/wip_hpp/improvement"><i class="fa fa-circle-o"></i> IMPROVEMENT</a></li>
                    <li class="<?php if($menu["third"] == 124) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/wip_hpp/penjualan_material"><i class="fa fa-circle-o"></i> PENJUALAN MATERIAL</a></li>
                  </ul>
                </li>

                <!-- LAPORAN KEUANGAN -->
                <li class="<?php if($menu["second"] == 13) echo "active"; ?>">
                  <a href="#"><i class="fa fa-circle-o"></i> LAPORAN KEUANGAN
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li class="<?php if($menu["third"] == 131) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/laporan_keuangan/laba_rugi"><i class="fa fa-circle-o"></i> LABA RUGI</a></li>
                    <li class="<?php if($menu["third"] == 132) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/laporan_keuangan/cashflow"><i class="fa fa-circle-o"></i> CASHFLOW</a></li>
                  </ul>
                </li>

                <!-- REKAPITULASI -->
                <li class="<?php if($menu["second"] == 14) echo "active"; ?>">
                  <a href="#"><i class="fa fa-circle-o"></i> REKAPITULASI
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li class="<?php if($menu["third"] == 141) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/rekapitulasi/biaya_rutin"><i class="fa fa-circle-o"></i> BIAYA RUTIN</a></li>
                    <li class="<?php if($menu["third"] == 142) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/rekapitulasi/aset_keuangan"><i class="fa fa-circle-o"></i> ASET DAN KERUGIAN</a></li>
                    <li class="<?php if($menu["third"] == 143) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/rekapitulasi/biaya_jasa"><i class="fa fa-circle-o"></i> BIAYA JASA</a></li>
                    <li class="<?php if($menu["third"] == 144) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/rekapitulasi/biaya_lain"><i class="fa fa-circle-o"></i> BIAYA BIAYA LAIN</a></li>
                    <li class="<?php if($menu["third"] == 145) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/rekapitulasi/biaya_material"><i class="fa fa-circle-o"></i> BIAYA MATERIAL</a></li>
                    <li class="<?php if($menu["third"] == 146) echo "active"; ?>" title="pelunasan tagihan dan sanksi"><a href="<?php echo WEB_URL; ?>kansi/rekapitulasi/pelunasan_tagihan"><i class="fa fa-circle-o"></i> PELUNASAN TAG SANKSI</a></li>
                    <li class="<?php if($menu["third"] == 147) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/rekapitulasi/penggantian_biaya"><i class="fa fa-circle-o"></i> PENGGANTIAN BIAYA</a></li>
                    <li class="<?php if($menu["third"] == 148) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/rekapitulasi/biaya_kansi"><i class="fa fa-circle-o"></i> BIAYA KANSI</a></li>
                    <li class="<?php if($menu["third"] == 149) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/rekapitulasi/invoice_serpo"><i class="fa fa-circle-o"></i> INVOICE SERPO</a></li>
                  </ul>
                </li>

                <!-- REFERENSI -->
                <li class="<?php if($menu["second"] == 15) echo "active"; ?>">
                  <a href="#"><i class="fa fa-circle-o"></i> REFERENSI
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li class="<?php if($menu["third"] == 151) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/jenis_jasa"><i class="fa fa-circle-o"></i> JENIS DAN HJ JASA</a></li>
                    <li class="<?php if($menu["third"] == 152) echo "active"; ?>" title="jenis, hj, red line material"><a href="<?php echo WEB_URL; ?>kansi/referensi/material"><i class="fa fa-circle-o"></i> JENIS, HJ, RL MATERIAL</a></li>
                    <li class="<?php if($menu["third"] == 1511) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/material_regional"><i class="fa fa-circle-o"></i> MATERIAL REGIONAL</a></li>
                    <li class="<?php if($menu["third"] == 153) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/regional"><i class="fa fa-circle-o"></i> REGIONAL</a></li>
                    <li class="<?php if($menu["third"] == 154) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/jenis_biaya"><i class="fa fa-circle-o"></i> JENIS BIAYA</a></li>
                    <li class="<?php if($menu["third"] == 155) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/pelanggan"><i class="fa fa-circle-o"></i> PELANGGAN</a></li>
                    <li class="<?php if($menu["third"] == 156) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/khs"><i class="fa fa-circle-o"></i> KHS</a></li>
                    <li class="<?php if($menu["third"] == 157) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/pop"><i class="fa fa-circle-o"></i> POP</a></li>
                    <li class="<?php if($menu["third"] == 158) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/plat"><i class="fa fa-circle-o"></i> NO. PLAT</a></li>
                    <li class="<?php if($menu["third"] == 159) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/etoll"><i class="fa fa-circle-o"></i> E-TOLL</a></li>
                    <li class="<?php if($menu["third"] == 1510) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/hp"><i class="fa fa-circle-o"></i> NO. HP</a></li>

                    <li class="<?php if($menu["third"] == 151200) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/daftar_user"><i class="fa fa-circle-o"></i> DAFTAR USER</a></li>
                    <li class="<?php if($menu["third"] == 151300) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/administrasi_user"><i class="fa fa-circle-o"></i> ADMINISTRASI USER</a></li>
                    <li class="<?php if($menu["third"] == 151400) echo "active"; ?>" title="setting nomor dokumen"><a href="<?php echo WEB_URL; ?>kansi/referensi/setting_no_dok"><i class="fa fa-circle-o"></i> SET NOMOR DOKUMEN</a></li>
                    <li class="<?php if($menu["third"] == 151500) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>kansi/referensi/lain_lain"><i class="fa fa-circle-o"></i> LAIN LAIN</a></li>
                  </ul>
                </li>
          </ul> 
        </li>
        
        <!-- TOP: STOCK -->
        <li class="treeview <?php if($menu["first"] == 2) echo "active"; ?>">
            <a href="#">
                <i class="fa fa-folder"></i> <span>STOCK</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                <!-- STATUS MATERIAL -->
                <li class="<?php if($menu["second"] == 21) echo "active"; ?>">
                    <a href="<?php echo WEB_URL; ?>stock/status/material"><i class="fa fa-circle-o"></i> <span>STATUS MATERIAL</span></a>
                </li>

                <!-- IH TRANSFER -->
                <li class="<?php if($menu["second"] == 22) echo "active"; ?>">
                    <a href="<?php echo WEB_URL; ?>stock/ih/transfer"><i class="fa fa-circle-o"></i> <span>IH TRANSFER</span></a>
                </li>

                <!-- PENJUALAN MATERIAL -->
                <li class="<?php if($menu["second"] == 23) echo "active"; ?>">
                    <a href="<?php echo WEB_URL; ?>stock/material/penjualan"><i class="fa fa-circle-o"></i> <span>PENJUALAN MATERIAL</span></a>
                </li>

                <!-- PEMBELIAN MATERIAL PUSAT -->
                <li class="<?php if($menu["second"] == 24) echo "active"; ?>">
                    <a href="<?php echo WEB_URL; ?>stock/pembelian/material/pusat"><i class="fa fa-circle-o"></i> <span>PEMBELIAN MATERIAL PUSAT</span></a>
                </li>

                <!-- PENGELUARAN MATERIAL -->
                <li class="<?php if($menu["second"] == 25) echo "active"; ?>">
                    <a href="<?php echo WEB_URL; ?>stock/pengeluaran/material/request"><i class="fa fa-circle-o"></i> <span>PENGELUARAN MATERIAL</span></a>
                </li>

                <!-- PENGGUNAAN MATERIAL -->
                <li class="<?php if($menu["second"] == 26) echo "active"; ?>">
                    <a href="<?php echo WEB_URL; ?>stock/material/pengunaan"><i class="fa fa-circle-o"></i> <span>PENGGUNAAN MATERIAL</span></a>
                </li>

                <!-- SALDO TERCATAT -->
                <li class="<?php if($menu["second"] == 27) echo "active"; ?>">
                    <a href="<?php echo WEB_URL; ?>stock/saldo/tercatat"><i class="fa fa-circle-o"></i> <span>SALDO TERCATAT</span></a>
                </li>
          </ul> 
        </li>
        
        <!-- TOP: UNIT -->
        <li class="treeview <?php if($menu["first"] == 3) echo "active"; ?>">
          <a href="#">
            <i class="fa fa-folder"></i> <span>UNIT</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <!-- SERVICE POINT -->
            <li class="<?php if($menu["second"] == 31) echo "active"; ?>">
              <a href="#"><i class="fa fa-circle-o"></i> SERVICE POINT
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li class="<?php if($menu["third"] == 311) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/serpo/rutin"><i class="fa fa-circle-o"></i> RUTIN SERPO</a></li>
                <li class="<?php if($menu["third"] == 312) echo "active"; ?>">
                    <a href="#"><i class="fa fa-circle-o"></i> MATERIAL SERPO
                        <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php if($menu["fourth"] == 3121) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/serpo/material/pembelian"><i class="fa fa-circle-o"></i> PEMBELIAN</a></li>
                        <li class="<?php if($menu["fourth"] == 3122) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/serpo/material/penggunaan"><i class="fa fa-circle-o"></i> PENGGUNAAN</a></li>
                    </ul>
                </li>
                <li class="<?php if($menu["third"] == 313) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/serpo/aset"><i class="fa fa-circle-o"></i> ASET SERPO</a></li>
              </ul>
            </li>

            <!-- AKTIVASI -->
            <li class="<?php if($menu["second"] == 32) echo "active"; ?>">
              <a href="#"><i class="fa fa-circle-o"></i> AKTIVASI
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li class="<?php if($menu["third"] == 321) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/aktivasi/rutin"><i class="fa fa-circle-o"></i> BIAYA RUTIN</a></li>
                <li class="<?php if($menu["third"] == 322) echo "active"; ?>">
                    <a href="#"><i class="fa fa-circle-o"></i> SALES ORDER
                        <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php if($menu["fourth"] == 3221) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/aktivasi/so"><i class="fa fa-circle-o"></i> DAFTAR SO</a></li>
                        <li class="<?php if($menu["fourth"] == 3222) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/aktivasi/so/jasa"><i class="fa fa-circle-o"></i> JASA SO</a></li>
                        <li class="<?php if($menu["fourth"] == 3223) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/aktivasi/so/biaya_lain"><i class="fa fa-circle-o"></i> BIAYA LAIN</a></li>
                        <li class="<?php if($menu["fourth"] == 3224) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/aktivasi/so/pkb"><i class="fa fa-circle-o"></i> PKB</a></li>
                        <li class="<?php if($menu["fourth"] == 3225) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/aktivasi/so/waspang"><i class="fa fa-circle-o"></i> WASPANG</a></li>
                        <li class="<?php if($menu["fourth"] == 3226) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/aktivasi/so/pkt"><i class="fa fa-circle-o"></i> PKT</a></li>
                    </ul>
                </li>
              </ul>
            </li>

            <!-- P. MAINTENANCE -->
            <li class="<?php if($menu["second"] == 33) echo "active"; ?>">
              <a href="#"><i class="fa fa-circle-o"></i> P. MAINTENANCE
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li class="<?php if($menu["third"] == 331) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/pm/rutin"><i class="fa fa-circle-o"></i> BIAYA RUTIN</a></li>
                <li class="<?php if($menu["third"] == 332) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>unit/pm/material"><i class="fa fa-circle-o"></i> MATERIAL PM</a></li>
              </ul>
            </li>

            <!-- IMPROVEMENT -->
            <li class="<?php if($menu["second"] == 34) echo "active"; ?>">
              <a href="#"><i class="fa fa-circle-o"></i> IMPROVEMENT
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li class="<?php if($menu["third"] == 341) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>improvement/spr"><i class="fa fa-circle-o"></i> DAFTAR SPR</a></li>
                <li class="<?php if($menu["third"] == 342) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>improvement/spr/jasa"><i class="fa fa-circle-o"></i> JASA SPR</a></li>
                <li class="<?php if($menu["third"] == 343) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>improvement/spr/biaya_lain"><i class="fa fa-circle-o"></i> BIAYA LAIN SPR</a></li>
                <li class="<?php if($menu["third"] == 344) echo "active"; ?>"><a href="<?php echo WEB_URL; ?>improvement/spr/kontrol"><i class="fa fa-circle-o"></i> KONTROL SPR</a></li>
              </ul>
            </li>

            <!-- PENGGANTIAN BIAYA -->
            <li class="<?php if($menu["second"] == 35) echo "active"; ?>">
                <a href="<?php echo WEB_URL; ?>biaya/penggantian"><i class="fa fa-circle-o"></i> <span>PENGGANTIAN BIAYA</span></a>
            </li>
          </ul>          
        </li>
        
        <!-- TOP: BOD -->
        <li><a href=""><i class="fa fa-folder"></i> <span>BOD</span></a></li>
        
        </li>
	  </ul>
    </section>
    <!-- /.sidebar -->
  </aside>
    