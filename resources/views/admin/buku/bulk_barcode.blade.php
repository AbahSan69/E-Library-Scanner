<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Barcode Buku</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .page {
            width: 190mm;
            margin: auto;
            text-align: center;
        }

        /* Label 14 x 7 cm, 3 label per halaman (1 kolom x 3 baris) */
        .label {
            display: block;
            width: 140mm;  /* 14 cm */
            height: 70mm;  /* 7 cm */
            padding: 4mm;
            margin: 0 auto 10mm auto;
            text-align: center;
            border: 1px dashed #ccc; /* hapus jika tidak perlu */
            box-sizing: border-box;

            /* Tengah isi label */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .school-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2mm;
        }

        .book-title {
            font-size: 10px;
            font-weight: 600;
            max-width: 120mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2mm;
        }

        .barcode img {
            width: 85%;
            height: auto;
            max-height: 40mm;
        }

        .barcode-number {
            font-size: 12px;
            letter-spacing: 2px;
            margin-top: 2mm;
        }

        .page-break {
            page-break-after: always;
            display: block;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="page">
        @php $count = 0; @endphp
        @foreach($books as $buku)
            <div class="label">
                <div class="school-name">SMAN 1 CITEUREUP</div>

                <div class="book-title">{{ $buku->judul }}</div>

                <div class="barcode">
                    <img src="data:image/png;base64,{{ 
                        DNS1D::getBarcodePNG($buku->no_barcode, 'EAN13', 3, 100, [0,0,0], true) 
                    }}" alt="barcode">
                </div>

                <div class="barcode-number">{{ $buku->no_barcode }}</div>
            </div>

            @php $count++; @endphp
            @if($count % 3 == 0)
                <div class="page-break"></div>
                <div class="page"></div>
            @endif
        @endforeach
    </div>
</body>
</html>
