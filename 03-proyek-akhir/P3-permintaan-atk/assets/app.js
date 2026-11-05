// ==========================================================================
// app.js — perilaku kecil di sisi peramban, tanpa pustaka tambahan.
// Semua fungsi dijalankan setelah halaman selesai dimuat (DOMContentLoaded).
// ==========================================================================

document.addEventListener("DOMContentLoaded", function () {   // tunggu sampai seluruh HTML siap

  // 1. Konfirmasi sebelum tindakan penting.
  //    Contoh di HTML: <form data-konfirmasi="Hapus transaksi ini?"> ... </form>
  document.querySelectorAll("form[data-konfirmasi]").forEach(function (form) {  // cari semua formulir bertanda
    form.addEventListener("submit", function (peristiwa) {    // saat formulir akan dikirim...
      if (!confirm(form.dataset.konfirmasi)) {                 // ...tampilkan kotak OK/Batal
        peristiwa.preventDefault();                            // jika Batal, pengiriman dibatalkan
      }
    });
  });

  // 2. Pratinjau rupiah di bawah isian angka.
  //    Contoh di HTML: <input type="number" name="jumlah" data-rupiah>
  document.querySelectorAll("input[data-rupiah]").forEach(function (isian) {
    const info = document.createElement("small");              // buat elemen kecil baru
    info.className = "pratinjau";                              // beri class agar bergaya keterangan
    isian.after(info);                                         // letakkan tepat di bawah isian
    function tampilkan() {                                     // fungsi untuk memperbarui teks pratinjau
      const angka = Number(isian.value);                       // ubah isi kotak menjadi angka
      info.textContent = angka > 0 ? "Rp " + angka.toLocaleString("id-ID") : "";  // 1500000 -> Rp 1.500.000
    }
    isian.addEventListener("input", tampilkan);                // perbarui setiap kali pengguna mengetik
    tampilkan();                                               // tampilkan sekali saat halaman dibuka
  });

  // 3. Pesan berhasil memudar sendiri setelah 5 detik agar layar tetap bersih.
  document.querySelectorAll(".pesan.sukses").forEach(function (kotak) {
    setTimeout(function () { kotak.classList.add("pudar"); }, 5000);  // tambah class pudar (lihat gaya.css)
  });

  // 4. Pilihan yang langsung memuat ulang halaman saat diganti (tanpa menekan tombol).
  //    Contoh di HTML: <select name="bulan" data-kirim-otomatis> ... </select>
  document.querySelectorAll("select[data-kirim-otomatis]").forEach(function (pilihan) {
    pilihan.addEventListener("change", function () { pilihan.form.submit(); });  // kirim formulir induknya
  });
});
