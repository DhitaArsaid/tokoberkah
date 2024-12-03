<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="hero-wrap hero-bread" style="background-image: url('<?php echo get_theme_uri('images/bg1.jpg'); ?>');">
    <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
            <div class="col-md-9 ftco-animate text-center">
                <p class="breadcrumbs"><span class="mr-2"><?php echo anchor(base_url(), 'Home'); ?></span> <span>Checkout</span></p>
                <h1 class="mb-0 bread">Checkout</h1>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section">
    <div class="container">
        <form action="<?php echo site_url('shop/checkout/order'); ?>" method="POST">

            <div class="row justify-content-center">
                <div class="col-xl-7 ftco-animate">
                    <h3 class="mb-4 billing-heading">Alamat Pengiriman</h3>

                    <div class="form-group">
                        <label for="name" class="form-control-label">Pengiriman untuk (nama):</label>
                        <input type="text" name="name" value="<?php echo $customer->name; ?>" class="form-control" id="name" required>
                    </div>

                    <div class="form-group">
                        <label for="hp" class="form-control-label">No. HP:</label>
                        <input type="text" name="phone_number" value="<?php echo $customer->phone_number; ?>" class="form-control" id="hp" required>
                    </div>



                    <div class="form-group">
                        <label for="address" class="form-control-label">Alamat (cantumkan link googel map lokasi anda agar mempermudah kami):</label>
                        <textarea name="address" class="form-control" id="address" required><?php echo $customer->address; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="note" class="form-control-label">Catatan:</label>
                        <textarea name="note" class="form-control" id="note"></textarea>
                    </div>

                    <input type="hidden" name="ongkir" id="ongkir_input" value="<?php echo $ongkir; ?>">
                    <input type="hidden" name="provinsi" id="provinsi_input">
                    <input type="hidden" name="kabupaten" id="kabupaten_input">
                </div>
                <div class="col-xl-5">
                    <div class="row mt-5 pt-3">
                        <div class="col-md-12 d-flex mb-5">
                            <div class="cart-detail cart-total p-3 p-md-4">
                                <h3 class="billing-heading mb-4">Rincian Belanja</h3>
                                <p class="d-flex">
                                    <span>Subtotal</span>
                                    <span>Rp <?php echo format_rupiah($subtotal); ?></span>
                                </p>
                                <p class="d-flex">
                                    <span>Ongkos kirim</span>
                                    <span id="ongkir"><?php echo $ongkir; ?></span>
                                </p>
                                <p class="d-flex">
                                    <span>Kupon</span>
                                    <span><?php echo $discount; ?></span>
                                </p>
                                <hr>
                                <p class="d-flex total-price">
                                    <span>Total</span>
                                    <span id="total">Rp <?php echo format_rupiah($total); ?></span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="cart-detail p-3 p-md-4">
                                <h3 class="billing-heading mb-4">Metode Pembayaran</h3>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <div class="radio">
                                            <label><input type="radio" name="payment" class="mr-2" value="1"> Virtual Account</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <div class="radio">
                                            <label><input type="radio" name="payment" class="mr-2" value="2" checked> Bayar ditempat</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-right" style="margin-top: 10px;">
                                <input type="submit" class="btn btn-primary py-2 px-2" value="Buat Pesanan">
                            </div>
                        </div>


                    </div>
                </div> <!-- .col-md-8 -->
            </div>

        </form>
    </div>
</section> <!-- .section -->

<script type="text/javascript">
    $('#provinsi').change(function() {
        var val = $(this).val();
        $('#provinsi_input').val($("#provinsi option:selected").text());

        $.get("<?= base_url('index.php/rajaongkir/getCity/') ?>" + val, function(data, status) {
            data = JSON.parse(data);

            var html = `<option value="">Pilih Kabupaten</option>`;

            $.each(data.rajaongkir.results, function(index, value) {
                html += `<option value="${value.city_id}">${value.city_name}</option>`
            });

            $('#kabupaten').html(html);
        });
    });

    var total = <?= $total ?>;
    $('#kabupaten').change(function() {
        $('#kabupaten_input').val($("#kabupaten option:selected").text());
        // var city = $(this).val();

        // $.get("<?= base_url('index.php/rajaongkir/ongkir/') ?>"+city+"/"+, function(data, status){
        //     data = JSON.parse(data);

        //     // expedisi
        //     console.log(data.rajaongkir.results);
        //     var html = `<option value="">Pilih Expedisi</option>`;

        //     $.each(data.rajaongkir.results, function( index, value ) {
        //         $.each(value.costs, function( i, item ) {
        //         html += `<option value="${value.code} ${item.service}" data-harga="${item.cost[0].value}">${value.code} ${item.service}</option>`
        //       });
        //     });

        //     $('#expedisi').html(html);

        //     // var ongkir = data.rajaongkir.results[0].costs[0].cost[0].value;
        //     // $('#ongkir').text('Rp ' + ongkir.toLocaleString());
        //     // $('#total').text('Rp ' + (total + ongkir).toLocaleString())
        //     // $('#ongkir_input').val(ongkir);
        // });

    });

    $('#expedisi').change(function() {
        var city = $("#kabupaten").val();
        var expedisi = $(this).val();

        $.get("<?= base_url('index.php/rajaongkir/ongkir/') ?>" + city + "/" + expedisi, function(data, status) {
            data = JSON.parse(data);

            var ongkir = data.rajaongkir.results[0].costs[0].cost[0].value;
            $('#ongkir').text('Rp ' + ongkir.toLocaleString());
            $('#total').text('Rp ' + (total + ongkir).toLocaleString())
            $('#ongkir_input').val(ongkir);
        });

    });
</script>