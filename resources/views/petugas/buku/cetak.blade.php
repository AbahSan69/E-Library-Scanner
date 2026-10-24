<!DOCTYPE html>
<html>
<head>
    <title>Cetak Label Buku</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .label {
            width: 200px;
            height: 100px;
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            margin: 10px;

            display: flex;                /* supaya isi bisa center */
            justify-content: center;
            align-items: center;
        }

        .label img.barcode {
            max-width: 100%;              /* supaya tidak melewati box */
            max-height: 90%;              /* sisakan ruang untuk angka */
            margin-bottom: 10px;           /* beri jarak ekstra antara garis barcode dan angka */
        }
    </style>
</head>
<body onload="window.print()">

    <div class="label">
        {{-- Barcode --}}
        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($book->no_barcode, 'EAN13', 2, 50, [0,0,0], true) }}">
    </div>

</body>
</html>
