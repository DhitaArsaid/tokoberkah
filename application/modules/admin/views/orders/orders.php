<!-- Page content -->
<div class="container-fluid mt--6">
  <div class="row">
    <div class="col">
      <div class="card">
        <!-- Card header -->
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Kelola Order</h3>
            <button id="printButton" class="btn btn-info"><i class="fa fa-print"></i> Print</button>
            <button id="kirimpesan" class="btn btn-info"><i class="fa fa-send"></i> kirimpesan</button>
          </div>
        </div>
        <?php if (count($orders) > 0) : ?>
          <div class="card-body p-0">
            <div class="table-responsive">
              <!-- Projects table -->
              <table id="orderTable" class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Jumlah Item</th>
                    <th scope="col">Jumlah Harga</th>
                    <th scope="col">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($orders as $order) : ?>
                    <tr>
                      <th scope="col">
                        <?php echo anchor('admin/orders/view/' . $order->id, '#' . $order->order_number); ?>
                      </th>
                      <td><?php echo $order->customer; ?></td>
                      <td>
                        <?php echo get_formatted_date($order->order_date); ?>
                      </td>
                      <td>
                        <?php echo $order->total_items; ?>
                      </td>
                      <td>
                        Rp <?php echo format_rupiah($order->total_price); ?>
                      </td>
                      <td><?php echo get_order_status($order->order_status, $order->payment_method); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="card-footer">
            <?php echo $pagination; ?>
          </div>
        <?php else : ?>
          <div class="card-body">
            <div class="alert alert-primary">
              Belum ada order
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('printButton').addEventListener('click', function() {
    var table = document.getElementById('orderTable');
    var newWin = window.open('', 'Print-Window');
    newWin.document.open();
    newWin.document.write('<html><body onload="window.print()">' + table.outerHTML + '</body></html>');
    newWin.document.close();
    setTimeout(function() {
      newWin.close();
    }, 10);
  });

  document.getElementById('kirimpesan').addEventListener('click', function() {
    if (confirm('Apakah Anda yakin ingin mengirim pesan ke semua order yang menunggu pembayaran?')) {
      var url = '<?php echo site_url('admin/orders/send_pending_messages'); ?>';
      console.log('URL:', url);

      fetch(url)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok ' + response.statusText);
          }
          return response.text();
        })
        .then(data => {
          alert(data);
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error: ' + error.message);
        });
    }
  });
</script>