<?php
require 'functions.php';
$kota = ambilkota();
$provinsi = ambilprovinsi();
$kecamatan = ambilkecamatan();

if (isset($_POST['btn-simpan'])) {
    // Membersihkan dan mengamankan input pengguna
    $city_id = mysqli_real_escape_string($koneksi, $_POST["city_id"]);
    $province = mysqli_real_escape_string($koneksi, $_POST['province']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']); // Misalnya untuk mendapatkan $username

    // Lakukan validasi data sebelum melakukan penambahan ke database
    if (!empty($city_id) && !empty($province) && !empty($username)) {
        $city_name = "";
        foreach ($kota as $city) {
            if ($city["city_id"] == $city_id) {
                $city_name = $city["city_name"];
                break;
            }
        }
        // Query untuk memasukkan data ke dalam tabel user
        $query = "INSERT INTO user (nama_user, username, password, no_telp_user, email, alamat_lengkap_user, kecamatan_user, id_kota_user, kota_user, provinsi_user, kode_verifikasi, status) VALUES ('$namauser', '$username', '$password', '$no_telp_user', '$email', '$alamat_lengkap', '$kecamatan', '$city_id', '$city_name', '$province', '$kode_verifikasi', 'Belum Terverifikasi')";

        // Eksekusi kueri
        $execute = bisa($koneksi, $query);
        if ($execute == 1) {
            $id_user = mysqli_insert_id($koneksi);
            header("location: verifikasi_proses.php?id_user=$id_user");
            exit; // Penting untuk menghentikan eksekusi setelah melakukan redirect
        } else {
            echo "Gagal Tambah Data: " . mysqli_error($koneksi); // Tampilkan pesan error jika terjadi kesalahan
        }
    } else {
        echo "Data tidak lengkap"; // Pesan jika data tidak lengkap
    }
}
?>







<!-- <select class="form-select" id="city_id" name="city_id">
    <option value="">Pilih Kota/Kabupaten</option>
    <?php foreach ($kota as $city) : ?>
      <option value="<?= $city['city_id'] ?>"><?= $city['city_name'] ?></option>
    <?php endforeach; ?>
    </select>
    <br><br>

    <select class="form-select" id="province" name="province">
    <option value="">Pilih Provinsi</option>
    <?php foreach ($provinsi as $prov) : ?>
      <option value="<?= $prov['province'] ?>"><?= $prov['province'] ?></option>
    <?php endforeach; ?>
</select>
<br><br> -->

<!-- <select class="form-select" id="subdistrict" name="subdistrict">
<option value="">Pilih Kecamatan</option>
<?php foreach ($kecamatan as $subdistrict) : ?>
    <option value="<?= $subdistrict['subdistrict_id'] ?>"><?= $subdistrict['subdistrict_name'] ?></option>
    <?php endforeach; ?>
</select>
<br><br> -->


<select class="form-select" id="province" name="province">
    <option value="">Pilih Provinsi</option>
    <?php foreach ($provinsi as $prov) : ?>
        <option value="<?= $prov['province_id'] ?>"><?= $prov['province'] ?></option>
    <?php endforeach; ?>
</select>
<br><br>


<select class="form-select" id="city_id" name="city_id">
    <option value="">Pilih Kota/Kabupaten</option>
    <?php foreach ($kota as $city) : ?>
        <option value="<?= $city['city_id'] ?>" data-province="<?= $city['province_id'] ?>"><?= $city['city_name'] ?></option>
    <?php endforeach; ?>
</select>
<br><br>


<select class="form-select" id="subdistrict" name="subdistrict">
    <option value="">Pilih Kecamatan</option>
    <?php foreach ($kecamatan as $subdistrict) : ?>
        <option value="<?= $subdistrict['subdistrict_id'] ?>" data-city="<?= $subdistrict['city_id'] ?>"><?= $subdistrict['subdistrict_name'] ?></option>
    <?php endforeach; ?>
</select>
<br><br>



<div style="padding-top: 10px;">
    <button type="submit" name="btn-simpan" class="btn btn-primary mb-3">Daftar</button>
</div>

</form>

</div>
</div>
</div>




</div>
</div>

<script>
    document.getElementById('province').addEventListener('change', function() {
        var selectedProvince = this.value;
        var cityDropdown = document.getElementById('city_id');

        // Menyembunyikan semua opsi kota/kabupaten terlebih dahulu
        for (var i = 0; i < cityDropdown.options.length; i++) {
            cityDropdown.options[i].style.display = 'none';
        }

        // Menampilkan hanya opsi kota/kabupaten yang sesuai dengan provinsi yang dipilih
        for (var i = 0; i < cityDropdown.options.length; i++) {
            var cityProvince = cityDropdown.options[i].getAttribute('data-province');
            if (selectedProvince === cityProvince || selectedProvince === '') {
                cityDropdown.options[i].style.display = '';
            }
        }
    });




    document.getElementById('city_id').addEventListener('change', function() {
        var selectedCity = this.value;
        var subdistrictDropdown = document.getElementById('subdistrict');

        // Menyembunyikan semua opsi kecamatan terlebih dahulu
        for (var i = 0; i < subdistrictDropdown.options.length; i++) {
            subdistrictDropdown.options[i].style.display = 'none';
        }

        // Menampilkan hanya opsi kecamatan yang sesuai dengan kabupaten/kota yang dipilih
        for (var i = 0; i < subdistrictDropdown.options.length; i++) {
            var subdistrictCity = subdistrictDropdown.options[i].getAttribute('data-city');
            if (selectedCity === subdistrictCity || selectedCity === '') {
                subdistrictDropdown.options[i].style.display = '';
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>

</html>