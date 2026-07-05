<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-6">
        <?= form_open('buy', 'class="row g-3"') ?>

<?= form_hidden('username', session()->get('username')) ?>
<?= form_input([
    'type'  => 'hidden',
    'name'  => 'total_harga',
    'id'    => 'total_harga',
    'value' => (string) $total,
]) ?>
<?= form_input([
    'type'  => 'hidden',
    'name'  => 'ppn',
    'id'    => 'ppn',
    'value' => '0',
]) ?>
<?= form_input([
    'type'  => 'hidden',
    'name'  => 'biaya_admin',
    'id'    => 'biaya_admin',
    'value' => '0',
]) ?>
<?= form_input([
    'type'  => 'hidden',
    'name'  => 'diskon_voucher',
    'id'    => 'diskon_voucher',
    'value' => '0',
]) ?>

<div class="col-12">
    <?= form_label('Nama', 'nama', ['class' => 'form-label']) ?>
    <?= form_input([
        'name'     => 'nama',
        'id'       => 'nama',
        'class'    => 'form-control',
        'value'    => session()->get('username'),
        'readonly' => true]) ?>
</div>
<div class="col-12">
    <?= form_label('Alamat', 'alamat', ['class' => 'form-label']) ?>
    <?= form_input([
        'name'  => 'alamat',
        'id'    => 'alamat',
        'class' => 'form-control']) ?>
</div> 
<div class="col-12"> 
    <?= form_label('Kelurahan', 'kelurahan', ['class' => 'form-label']) ?>
    <?= form_dropdown('kelurahan', [], '', ['id' => 'kelurahan', 'class' => 'form-control']) ?>
</div>
<div class="col-12"> 
    <?= form_label('Layanan', 'layanan', ['class' => 'form-label']) ?> 
    <?= form_dropdown('layanan', [], '', ['id' => 'layanan', 'class' => 'form-control', 'disabled' => true]) ?>
</div>
<div class="col-12">
    <?= form_label('Ongkir', 'ongkir_display', ['class' => 'form-label']) ?>
    <?= form_input([
        'name'     => 'ongkir_display',
        'id'       => 'ongkir_display',
        'class'    => 'form-control',
        'readonly' => true]) ?>
    <?= form_input([
        'type'  => 'hidden',
        'name'  => 'ongkir',
        'id'    => 'ongkir',
        'value' => '0'
    ]) ?>
</div>
<div class="col-12">
    <?= form_label('Kode Voucher', 'voucher_code', ['class' => 'form-label']) ?>
    <?= form_input([
        'name'  => 'voucher_code',
        'id'    => 'voucher_code',
        'class' => 'form-control',
        'value' => '']) ?>
    <div class="form-text">Tersedia: FLASH10, FLASH15, MEMBER20</div>
</div>
<div class="col-12">
    <?= form_submit(
        'submit',
        'Buat Pesanan',
        ['class' => 'btn btn-primary']) ?>
</div>

<?= form_close() ?> 
    </div>
    <div class="col-lg-6">
        <table class="table">
  <thead>
      <tr>
          <th scope="col">Nama</th>
          <th scope="col">Harga</th>
          <th scope="col">Jumlah</th>
          <th scope="col">Sub Total</th>
      </tr>
  </thead>
  <tbody>
      <?php 
      if (!empty($items)) :
          foreach ($items as $index => $item) :
      ?>
              <tr>
                  <td><?= $item['name'] ?></td>
                  <td><?= number_to_currency($item['price'], 'IDR') ?></td>
                  <td><?= $item['qty'] ?></td>
                  <td><?= number_to_currency($item['price'] * $item['qty'], 'IDR') ?></td>
              </tr>
      <?php
          endforeach;
      endif;
      ?>
        <tr>
          <td colspan="2"></td>
          <td>Subtotal</td>
          <td><span id="summary_subtotal"><?= number_to_currency($total, 'IDR') ?></span></td>
      </tr>
      <tr>
          <td colspan="2"></td>
          <td>Diskon Voucher</td>
          <td class="text-danger"><span id="summary_diskon">-IDR 0</span></td>
      </tr>
      <tr>
          <td colspan="2"></td>
          <td>PPN (11%)</td>
          <td><span id="summary_ppn">IDR 0</span></td>
      </tr>
      <tr>
          <td colspan="2"></td>
          <td>Biaya Admin</td>
          <td><span id="summary_admin">IDR 0</span></td>
      </tr>
      <tr>
          <td colspan="2"></td>
          <td>Subtotal setelah Voucher</td>
          <td><span id="summary_subtotal_after">IDR <?= number_to_currency($total, 'IDR') ?></span></td>
      </tr>
      <tr>
          <td colspan="2"></td>
          <td>Grand Total</td>
          <td><strong><span id="total"><?= number_to_currency($total, 'IDR') ?></span></strong></td>
      </tr>
  </tbody>
</table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('script') ?>
<script>
// Initialize variables
let ongkir = 0;
let subtotal = <?= (int) $total ?>;
let voucherRates = {
    FLASH10: 0.10,
    FLASH15: 0.15,
    MEMBER20: 0.20
};

function getVoucherRate(code) {
    if (!code) return 0;
    const normalized = code.trim().toUpperCase();
    return voucherRates[normalized] || 0;
}

function hitungBiayaAdmin(totalHarga) {
    if (totalHarga <= 20000000) {
        return Math.round(totalHarga * 0.006);
    }
    if (totalHarga <= 40000000) {
        return Math.round(totalHarga * 0.008);
    }
    return Math.round(totalHarga * 0.01);
}

function hitungTotal() {
    const voucherCode = $("#voucher_code").val();
    const voucherRate = getVoucherRate(voucherCode);
    const diskonVoucher = Math.round(subtotal * voucherRate);
    const ppn = Math.round(subtotal * 0.11);
    const biayaAdmin = hitungBiayaAdmin(subtotal);
    const subtotalAfter = subtotal - diskonVoucher + ppn + biayaAdmin;
    const total = subtotalAfter + ongkir;

    $("#ongkir_display").val(ongkir.toLocaleString('id-ID'));
    $("#ongkir").val(ongkir);
    $("#summary_subtotal").text(`IDR ${subtotal.toLocaleString('id-ID')}`);
    $("#summary_diskon").text(`-IDR ${diskonVoucher.toLocaleString('id-ID')}`);
    $("#summary_ppn").text(`IDR ${ppn.toLocaleString('id-ID')}`);
    $("#summary_admin").text(`IDR ${biayaAdmin.toLocaleString('id-ID')}`);
    $("#summary_subtotal_after").text(`IDR ${subtotalAfter.toLocaleString('id-ID')}`);
    $("#total").text(`IDR ${total.toLocaleString('id-ID')}`);
    $("#total_harga").val(total);
    $("#ppn").val(ppn);
    $("#biaya_admin").val(biayaAdmin);
    $("#diskon_voucher").val(diskonVoucher);
}

// Initial calculation
hitungTotal();

$(document).ready(function() {
	$('#kelurahan').select2({
	    placeholder: 'Cari daerah tujuan',
	    minimumInputLength: 3,
        ajax: {
            url: '<?= site_url('ajax/destinations') ?>',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.results || []
                };
            },
            cache: true
        }
	});

	// Handle kelurahan (destination) change
	$("#kelurahan").on('change', function () {
	    let id_kelurahan = $(this).val();

	    $("#layanan").html('<option value="">Pilih layanan</option>');
	    $("#layanan").prop('disabled', true);
	    ongkir = 0;
	    hitungTotal(); 

	    console.log('ID Kelurahan:', id_kelurahan);

	    if (id_kelurahan) {
	        $.ajax({
	            url: '<?= site_url('ajax/costs') ?>', 
	            dataType: "json",
	            data: {
	                destination: id_kelurahan
	            },
	            success: function (data) { 
	                if (!data || data.length === 0) {
	                    console.warn('Tidak ada layanan ongkir untuk destination:', id_kelurahan);
	                    return;
	                }

	                $("#layanan").prop('disabled', false);
	                data.forEach(function (item) {
	                    let costValue = item.cost;
	                    if (Array.isArray(costValue)) {
	                        costValue = costValue[0]?.value || 0;
	                    }

                    const label = item.name
                        ? `${item.name} (${item.service}) Estimasi ${item.etd}`
                        : `${item.description} (${item.service}) Estimasi ${item.etd}`;

                    $("#layanan").append(
                        $('<option></option>', {
                            value: costValue,
                            text: label
	                        })
	                    );
	                });
	            },
	            error: function(xhr, status, error) {
	                console.error('Error fetching costs:', error);
	            }
	        });
	    }
	});

	// Handle layanan (service) change
	$("#layanan").on('change', function() {
	    ongkir = parseInt($(this).val()) || 0;
	    hitungTotal();
	});
});
</script>
<?= $this->endSection() ?>