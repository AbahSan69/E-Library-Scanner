<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Barcode Siswa</title>

    <style>
        /* --- Ukuran label: 5.5 × 2.5 cm --- */
        @page {
            size: 5.5cm 2.5cm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            width: 5.5cm;
            height: 2.5cm;

            display: flex;
            justify-content: center;
            align-items: center;

            font-family: sans-serif;
            text-align: center;
        }

        .label {
            width: 5.1cm;   /* sedikit lebih kecil dari page */
            padding: 1mm;
            box-sizing: border-box;
        }

        .school-name {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .barcode img {
            width: 95%;
            height: auto;
        }

        .barcode-number {
            font-size: 9px;
            margin-top: 0.5mm;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>
    <div class="label">
        <div class="school-name">SMAN 1 CITEUREUP</div>

        <div class="barcode">
            <img src="data:image/png;base64,{{ 
                DNS1D::getBarcodePNG($siswa->nisn, 'C128', 2, 60, [0,0,0], true) 
            }}">
        </div>

        <div class="barcode-number">{{ $siswa->nisn }}</div>
    </div>
</body>
</html>
