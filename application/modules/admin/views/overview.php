<?php
// Query database untuk mendapatkan jumlah pesanan VA yang dibatalkan per bulan
$query_va = "SELECT MONTH(order_date) AS month, COUNT(*) AS canceled_orders 
             FROM orders 
             WHERE order_status = 5 AND payment_method = 1
             GROUP BY MONTH(order_date)";

// Query database untuk mendapatkan jumlah pesanan COD yang dibatalkan per bulan
$query_cod = "SELECT MONTH(order_date) AS month, COUNT(*) AS canceled_orders 
              FROM orders 
              WHERE order_status = 4 AND payment_method = 2
              GROUP BY MONTH(order_date)";

// Jalankan query untuk VA
$result_va = $this->db->query($query_va);

// Jalankan query untuk COD
$result_cod = $this->db->query($query_cod);

// Inisialisasi array untuk menyimpan data pesanan VA yang dibatalkan per bulan
$canceledOrdersDataVA = array_fill(0, 12, 0);

// Inisialisasi array untuk menyimpan data pesanan COD yang dibatalkan per bulan
$canceledOrdersDataCOD = array_fill(0, 12, 0);

// Memasukkan hasil query ke dalam array untuk VA
foreach ($result_va->result() as $row) {
  $month = $row->month - 1; // Bulan dimulai dari 1, kita konversi menjadi indeks array yang dimulai dari 0
  $canceledOrdersDataVA[$month] = $row->canceled_orders;
}

// Memasukkan hasil query ke dalam array untuk COD
foreach ($result_cod->result() as $row) {
  $month = $row->month - 1; // Bulan dimulai dari 1, kita konversi menjadi indeks array yang dimulai dari 0
  $canceledOrdersDataCOD[$month] = $row->canceled_orders;
}

// Menggabungkan hasil dari kedua metode pembayaran
$canceledOrdersData = array();
for ($i = 0; $i < 12; $i++) {
  $canceledOrdersData[$i] = $canceledOrdersDataVA[$i] + $canceledOrdersDataCOD[$i];
}
?>


<!DOCTYPE html>
<html>

<head>
  <title>Dashboard</title>
  <!-- Include CSS, JavaScript, dan library lainnya -->
  <!-- .... -->
</head>

<body>
  <!-- Include skrip JavaScript untuk grafik -->
  <script>
    // Data pesanan yang dibatalkan
    var canceledOrdersData = <?php echo json_encode($canceledOrdersData); ?>;
  </script>
  <!-- Include skrip JavaScript, jQuery, dan Chart.js -->
  <!-- .... -->
</body>

</html>

<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<!-- Header -->
<div class="header bg-success pb-6">
  <div class="container-fluid">
    <div class="header-body">
      <div class="row align-items-center py-4">
        <div class="col-lg-6 col-7">
          <h6 class="h2 text-white d-inline-block mb-0">Dasbor</h6>
        </div>
        <div class="col-lg-6 col-5 text-right">
          <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
            <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
              <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i></a></li>
              <li class="breadcrumb-item active" aria-current="page">Dasbor</li>
            </ol>
          </nav>
        </div>
      </div>

      <!-- Card stats -->
      <div class="row">
        <div class="col-xl-3 col-md-6">
          <div class="card card-stats">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-muted mb-0">Produk</h5>
                  <span class="h2 font-weight-bold mb-0"><?php echo $total_products; ?></span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                    <i class="ni ni-shop"></i>
                  </div>
                </div>
              </div>
              <p class="mt-3 mb-0 text-sm">
                <span class="text-success mr-2"><i class="fa fa-arrow-up"></i></span>
                <span class="text-nowrap">Jumlah produk yang tersedia</span>
              </p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card card-stats">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-muted mb-0">Pelanggan</h5>
                  <span class="h2 font-weight-bold mb-0"><?php echo $total_customers; ?></span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-gradient-orange text-white rounded-circle shadow">
                    <i class="ni ni-circle-08"></i>
                  </div>
                </div>
              </div>
              <p class="mt-3 mb-0 text-sm">
                <span class="text-success mr-2"><i class="fa fa-arrow-up"></i></span>
                <span class="text-nowrap">Pelanggan yang terdaftar</span>
              </p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card card-stats">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-muted mb-0">Pesanan</h5>
                  <span class="h2 font-weight-bold mb-0"><?php echo $total_order; ?></span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-gradient-green text-white rounded-circle shadow">
                    <i class="ni ni-chart-bar-32"></i>
                  </div>
                </div>
              </div>
              <p class="mt-3 mb-0 text-sm">
                <span class="text-success mr-2"><i class="fa fa-arrow-up"></i></span>
                <span class="text-nowrap">Jumlah pesanan diterima</span>
              </p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card card-stats">
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <h5 class="card-title text-uppercase text-muted mb-0">Pendapatan</h5>
                  <span class="h2 font-weight-bold mb-0">Rp <?php echo format_rupiah($total_income); ?></span>
                </div>
                <div class="col-auto">
                  <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                    <i class="ni ni-money-coins"></i>
                  </div>
                </div>
              </div>
              <p class="mt-3 mb-0 text-sm">
                <span class="text-success mr-2"><i class="fa fa-arrow-up"></i></span>
                <span class="text-nowrap">Total pendapatan</span>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Page content -->
<div class="container-fluid mt--6">
  <div class="row">
    <div class="col-xl-8">
      <div class="card" style="background-color: #343a40;"> <!-- Changed background color -->
        <div class="card-header" style="background-color: #6c757d;"> <!-- Changed background color -->
          <div class="row align-items-center">
            <div class="col">
              <h6 class="text-uppercase ls-1 mb-1" style="color: #f8f9fa;">Ringkasan</h6> <!-- Changed text color -->
              <h5 class="h3 mb-0" style="color: #ffffff;">Penjualan</h5> <!-- Changed text color -->
            </div>
          </div>
        </div>
        <div class="card-body">
          <!-- Chart -->
          <div class="chart">
            <!-- Chart wrapper -->
            <canvas id="chart-sales-dark" class="chart-canvas"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="card">
        <div class="card-header bg-transparent">
          <div class="row align-items-center">
            <div class="col">
              <h6 class="text-uppercase text-muted ls-1 mb-1">Ringkasan</h6>
              <h5 class="h3 mb-0">Pendapatan</h5>
            </div>
          </div>
        </div>
        <div class="card-body">
          <!-- Chart -->
          <div class="chart">
            <canvas id="chart-bars" class="chart-canvas"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-xl-8 col-lg-6 col-md-6">
      <div class="card bg-warning">
        <div class="card-header bg-transparent">
          <div class="row align-items-center">
            <div class="col">
              <h6 class="text-uppercase text-muted ls-1 mb-1">Ringkasan</h6>
              <h5 class="h3 mb-0">Pesanan dibatalkan</h5>
            </div>
          </div>
        </div>
        <div class="card-body">
          <!-- Chart -->
          <div class="chart">
            <canvas id="chart-new" class="chart-canvas"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-xl-4">
      <!-- Members list group card -->
      <div class="card">
        <!-- Card header -->
        <div class="card-header">
          <!-- Title -->
          <h5 class="h3 mb-0">Pelanggan </h5>
        </div>
        <!-- Card body -->
        <div class="card-body">
          <!-- List group -->
          <ul class="list-group list-group-flush list my--3">
            <?php foreach ($customers as $customer) : ?>
              <li class="list-group-item px-0">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <!-- Avatar -->
                    <a href="#" class="avatar rounded-circle">
                      <img alt="Image placeholder" src="<?php echo base_url('assets/uploads/users/' . $customer->profile_picture); ?>">
                    </a>
                  </div>
                  <div class="col ml--2">
                    <h4 class="mb-0">
                      <a href="#!"><?php echo $customer->name; ?></a>
                    </h4>

                  </div>
                  <div class="col-auto">
                    <a href="<?php echo site_url('admin/customers/view/' . $customer->user_id); ?>" class="btn btn-sm btn-primary">Profil</a>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <!-- Checklist -->
      <div class="card">
        <!-- Card header -->
        <div class="card-header">
          <!-- Title -->
          <h5 class="h3 mb-0">Order </h5>
        </div>
        <!-- Card body -->
        <div class="card-body p-0">
          <!-- List group -->
          <ul class="list-group list-group-flush" data-toggle="checklist">
            <?php foreach ($orders as $order) : ?>
              <li class="checklist-entry list-group-item flex-column align-items-start py-4 px-4">
                <div class="checklist-item checklist-item-info">
                  <div class="checklist-info">
                    <h5 class="checklist-title mb-0"><?php echo anchor('admin/orders/view/' . $order->id, 'Order #' . $order->order_number); ?></h5>
                    <small><?php echo $order->total_items; ?></small> | <small>Rp <?php echo format_rupiah($order->total_price); ?></small>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <!-- Progress track -->
      <div class="card">
        <!-- Card header -->
        <div class="card-header">
          <!-- Title -->
          <h5 class="h3 mb-0">Pembayaran menunggu konfirmasi</h5>
        </div>
        <!-- Card body -->
        <div class="card-body">
          <!-- List group -->
          <ul class="list-group list-group-flush list my--3">
            <?php foreach ($payments as $payment) : ?>
              <li class="list-group-item px-0">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <!-- Avatar -->
                    <a href="<?php echo site_url('admin/payments/users/' . $payment->user_id); ?>" class="avatar rounded-circle">
                      <img alt="Image placeholder" src="<?php echo base_url('assets/uploads/users/' . $payment->profile_picture); ?>">
                    </a>
                  </div>
                  <div class="col">
                    <h5>Order #<?php echo $payment->order_number; ?></h5>
                    <div>
                      Rp <?php echo format_rupiah($payment->payment_price); ?>
                    </div>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-xl-8">
      <div class="card">
        <div class="card-header border-0">
          <div class="row align-items-center">
            <div class="col">
              <h3 class="mb-0">Produk baru</h3>
            </div>
            <div class="col text-right">
              <a href="<?php echo site_url('admin/products'); ?>" class="btn btn-sm btn-primary">Lihat semua</a>
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <!-- Projects table -->
          <table class="table align-items-center table-flush">
            <thead class="thead-light">
              <tr>
                <th scope="col">ID</th>
                <th scope="col">Nama</th>
                <th scope="col">Harga</th>
                <th scope="col">Stok</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $product) : ?>
                <tr>
                  <th scope="col">
                    <?php echo $product->id; ?>
                  </th>
                  <td>
                    <?php echo $product->name; ?>
                  </td>
                  <td>
                    Rp <?php echo format_rupiah($product->price); ?>
                  </td>
                  <td>
                    <?php echo $product->stock; ?> <?php echo $product->product_unit; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="card">
        <div class="card-header border-0">
          <div class="row align-items-center">
            <div class="col">
              <h3 class="mb-0">Kategori produk</h3>
            </div>
            <div class="col text-right">
              <a href="<?php echo site_url('admin/products/category'); ?>" class="btn btn-sm btn-primary">Lihat semua</a>
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <!-- Projects table -->
          <table class="table align-items-center table-flush">
            <thead class="thead-light">
              <tr>
                <th scope="col">#</th>
                <th scope="col">Nama</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $category) : ?>
                <tr>
                  <th scope="col">
                    <?php echo $category->id; ?>
                  </th>
                  <td>
                    <?php echo $category->name; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="<?php echo get_theme_uri('vendor/chart.js/dist/Chart.min.js', 'argon'); ?>"></script>
  <script>
    //
    // Charts
    //

    'use strict';

    var Charts = (function() {

      // Variable

      var $toggle = $('[data-toggle="chart"]');
      var mode = 'light'; //(themeMode) ? themeMode : 'light';
      var fonts = {
        base: 'Open Sans'
      }

      // Colors
      var colors = {
        gray: {
          100: '#f6f9fc',
          200: '#e9ecef',
          300: '#dee2e6',
          400: '#ced4da',
          500: '#adb5bd',
          600: '#8898aa',
          700: '#525f7f',
          800: '#32325d',
          900: '#212529'
        },
        theme: {
          'default': '#14147a',
          'primary': '#4535b7',
          'secondary': '#f4f5f7',
          'info': '#11cdef',
          'success': '#2de8c4',
          'danger': '#f5365c',
          'warning': '#ffc410'
        },
        black: '#12263F',
        white: '#FFFFFF',
        transparent: 'transparent',
      };


      // Methods

      // Chart.js global options
      function chartOptions() {

        // Options
        var options = {
          defaults: {
            global: {
              responsive: true,
              maintainAspectRatio: false,
              defaultColor: (mode == 'dark') ? colors.gray[700] : colors.gray[600],
              defaultFontColor: (mode == 'dark') ? colors.gray[700] : colors.gray[600],
              defaultFontFamily: fonts.base,
              defaultFontSize: 13,
              layout: {
                padding: 0
              },
              legend: {
                display: false,
                position: 'bottom',
                labels: {
                  usePointStyle: true,
                  padding: 16
                }
              },
              elements: {
                point: {
                  radius: 0,
                  backgroundColor: colors.theme['primary']
                },
                line: {
                  tension: .4,
                  borderWidth: 4,
                  borderColor: colors.theme['primary'],
                  backgroundColor: colors.transparent,
                  borderCapStyle: 'rounded'
                },
                rectangle: {
                  backgroundColor: colors.theme['warning']
                },
                arc: {
                  backgroundColor: colors.theme['primary'],
                  borderColor: (mode == 'dark') ? colors.gray[800] : colors.white,
                  borderWidth: 4
                }
              },
              tooltips: {
                enabled: true,
                mode: 'index',
                intersect: false,
              }
            },
            doughnut: {
              cutoutPercentage: 83,
              legendCallback: function(chart) {
                var data = chart.data;
                var content = '';

                data.labels.forEach(function(label, index) {
                  var bgColor = data.datasets[0].backgroundColor[index];

                  content += '<span class="chart-legend-item">';
                  content += '<i class="chart-legend-indicator" style="background-color: ' + bgColor + '"></i>';
                  content += label;
                  content += '</span>';
                });

                return content;
              }
            }
          }
        }

        // yAxes
        Chart.scaleService.updateScaleDefaults('linear', {
          gridLines: {
            borderDash: [2],
            borderDashOffset: [2],
            color: (mode == 'dark') ? colors.gray[900] : colors.gray[300],
            drawBorder: false,
            drawTicks: false,
            drawOnChartArea: true,
            zeroLineWidth: 0,
            zeroLineColor: 'rgba(0,0,0,0)',
            zeroLineBorderDash: [2],
            zeroLineBorderDashOffset: [2]
          },
          ticks: {
            beginAtZero: true,
            padding: 10,
            callback: function(value) {
              if (!(value % 10)) {
                return value
              }
            }
          }
        });

        // xAxes
        Chart.scaleService.updateScaleDefaults('category', {
          gridLines: {
            drawBorder: false,
            drawOnChartArea: false,
            drawTicks: false
          },
          ticks: {
            padding: 20
          },
          maxBarThickness: 10
        });

        return options;

      }

      // Parse global options
      function parseOptions(parent, options) {
        for (var item in options) {
          if (typeof options[item] !== 'object') {
            parent[item] = options[item];
          } else {
            parseOptions(parent[item], options[item]);
          }
        }
      }

      // Push options
      function pushOptions(parent, options) {
        for (var item in options) {
          if (Array.isArray(options[item])) {
            options[item].forEach(function(data) {
              parent[item].push(data);
            });
          } else {
            pushOptions(parent[item], options[item]);
          }
        }
      }

      // Pop options
      function popOptions(parent, options) {
        for (var item in options) {
          if (Array.isArray(options[item])) {
            options[item].forEach(function(data) {
              parent[item].pop();
            });
          } else {
            popOptions(parent[item], options[item]);
          }
        }
      }

      // Toggle options
      function toggleOptions(elem) {
        var options = elem.data('add');
        var $target = $(elem.data('target'));
        var $chart = $target.data('chart');

        if (elem.is(':checked')) {

          // Add options
          pushOptions($chart, options);

          // Update chart
          $chart.update();
        } else {

          // Remove options
          popOptions($chart, options);

          // Update chart
          $chart.update();
        }
      }

      // Update options
      function updateOptions(elem) {
        var options = elem.data('update');
        var $target = $(elem.data('target'));
        var $chart = $target.data('chart');

        // Parse options
        parseOptions($chart, options);

        // Toggle ticks
        toggleTicks(elem, $chart);

        // Update chart
        $chart.update();
      }

      // Toggle ticks
      function toggleTicks(elem, $chart) {

        if (elem.data('prefix') !== undefined || elem.data('prefix') !== undefined) {
          var prefix = elem.data('prefix') ? elem.data('prefix') : '';
          var suffix = elem.data('suffix') ? elem.data('suffix') : '';

          // Update ticks
          $chart.options.scales.yAxes[0].ticks.callback = function(value) {
            if (!(value % 10)) {
              return prefix + value + suffix;
            }
          }

          // Update tooltips
          $chart.options.tooltips.callbacks.label = function(item, data) {
            var label = data.datasets[item.datasetIndex].label || '';
            var yLabel = item.yLabel;
            var content = '';

            if (data.datasets.length > 1) {
              content += '<span class="popover-body-label mr-auto">' + label + '</span>';
            }

            content += '<span class="popover-body-value">' + prefix + yLabel + suffix + '</span>';
            return content;
          }

        }
      }


      // Events

      // Parse global options
      if (window.Chart) {
        parseOptions(Chart, chartOptions());
      }

      // Toggle options
      $toggle.on({
        'change': function() {
          var $this = $(this);

          if ($this.is('[data-add]')) {
            toggleOptions($this);
          }
        },
        'click': function() {
          var $this = $(this);

          if ($this.is('[data-update]')) {
            updateOptions($this);
          }
        }
      });


      // Return

      return {
        colors: colors,
        fonts: fonts,
        mode: mode
      };

    })();

    'use strict';

    //
    // Sales chart
    //

    var SalesChart = (function() {

      // Variables

      var $chart = $('#chart-sales-dark');


      // Methods

      function init($this) {
        var salesChart = new Chart($this, {
          type: 'line',
          options: {
            scales: {
              yAxes: [{
                gridLines: {
                  color: Charts.colors.gray[700],
                  zeroLineColor: Charts.colors.gray[700]
                },
                ticks: {

                }
              }]
            }
          },
          data: {
            labels: [
              <?php foreach ($order_overviews as $order) : ?> '<?php echo get_month($order->month); ?>',
              <?php endforeach; ?>
            ],
            datasets: [{
              label: 'Order',
              data: [
                <?php foreach ($order_overviews as $order) : ?>
                  <?php echo $order->sale; ?>,
                <?php endforeach; ?>
              ]
            }]
          }
        });

        // Save to jQuery object

        $this.data('chart', salesChart);

      };


      // Events

      if ($chart.length) {
        init($chart);
      }

    })();



    var BarsChart = (function() {

      //
      // Variables
      //

      var $chart = $('#chart-bars');


      //
      // Methods
      //

      // Init chart
      function initChart($chart) {

        // Create chart
        var ordersChart = new Chart($chart, {
          type: 'bar',
          data: {
            labels: [
              <?php foreach ($income_overviews as $income) : ?> '<?php echo get_month($income->month); ?>',
              <?php endforeach; ?>
            ],
            datasets: [{
              label: 'Pendapatan',
              data: [
                <?php foreach ($income_overviews as $income) : ?> '<?php echo $income->income; ?>',
                <?php endforeach; ?>
              ]
            }]
          }
        });

        // New chart
        var NewChart = (function() {

          // Variables
          var $chart = $('#chart-new');

          // Methods
          function initChart($this) {
            var newChart = new Chart($this, {
              type: 'line',
              options: {
                scales: {
                  yAxes: [{
                    gridLines: {
                      color: Charts.colors.gray[700],
                      zeroLineColor: Charts.colors.gray[700]
                    },
                    ticks: {}
                  }]
                }
              },
              data: {
                labels: [
                  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ],
                datasets: [{
                  label: 'Pesanan Dibatalkan',
                  data: canceledOrdersData,
                  borderColor: Charts.colors.theme['danger'],
                  backgroundColor: 'rgba(255, 0, 0, 0.1)',
                  fill: true
                }]
              }
            });

            // Simpan ke objek jQuery
            $this.data('chart', newChart);
          };

          // Events
          if ($chart.length) {
            initChart($chart);
          }

        })();


        // Save to jQuery object
        $chart.data('chart', ordersChart);
      }


      // Init chart
      if ($chart.length) {
        initChart($chart);
      }

    })();
  </script>