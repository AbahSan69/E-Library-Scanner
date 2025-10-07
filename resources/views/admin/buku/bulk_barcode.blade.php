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
            width: 190mm; /* sekitar A4 dikurangi margin */
            margin: auto;
            text-align: center;
        }

        .label {
            display: inline-block;
            width: 160mm;   /* 16 cm */
            height: 70mm;   /* 7 cm */
            padding: 4mm;
            margin: 2mm 0;
            text-align: center;
            vertical-align: top;
            border: 1px dashed #ccc; /* untuk preview */
            box-sizing: border-box;
        }

        .label div {
            margin: 1mm 0;
        }

        .school-name {
            font-size: 12px;
            font-weight: bold;
        }

        .book-title {
            font-size: 10px;
            font-weight: 600;
            max-width: 150mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .barcode {
            margin-top: 3mm;
            margin-bottom: 2mm;
        }

        .barcode img {
            width: 100%;
            height: auto;
            max-height: 45mm;
        }

        .barcode-number {
            font-size: 14px;
            letter-spacing: 2px;
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
            @if($count % 4 == 0)
                <div class="page-break"></div>
                <div class="page"></div>
            @endif
        @endforeach
    </div>
</body>
</html>
