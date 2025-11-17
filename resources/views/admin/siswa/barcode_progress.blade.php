<h3>Memproses semua barcode...</h3>

<div style="width: 100%; background: #ddd; height: 30px; border-radius: 5px;">
    <div id="bar" style="height: 30px; width: 0%; background: green; border-radius: 5px;"></div>
</div>

<p id="text">0%</p>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Start background process
    fetch("{{ route('admin.barcode.batch.start') }}", {
        method: 'POST',
        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
    });

    // Update progress
    let interval = setInterval(() => {
        fetch("{{ route('admin.barcode.batch.check') }}")
            .then(r => r.json())
            .then(data => {

                let p = Math.round(data.progress);

                document.getElementById("bar").style.width = p + "%";
                document.getElementById("text").innerHTML = p + "%";

                if (p >= 100) {
                    clearInterval(interval);
                    window.location.href = "{{ route('admin.barcode.batch.download') }}";
                }
            });
    }, 1000);

});
</script>
