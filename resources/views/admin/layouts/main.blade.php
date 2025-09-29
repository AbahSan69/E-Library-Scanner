<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
@include('admin.layouts.head')
<body>
  @include('sweetalert::alert')
    @yield('content')
      <script src="{{ asset('assets/oneui/js/lib/jquery.min.js') }}"></script>
      <script src="{{ asset('assets/oneui/js/oneui.app.min.js') }}"></script>
      {{-- <script src="{{ asset('assets/oneui/js/plugins/chart.js/chart.umd.js') }}"></script> --}}
      {{-- <script src="{{ asset('assets/oneui/js/pages/be_pages_dashboard.min.js') }}"></script>
      <script src="{{ asset('assets/oneui/js/plugins/flatpickr/flatpickr.min.js') }}"></script>
      <script src="{{ asset('assets/oneui/js/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script> --}}
      {{-- <script src="sweetalert2.all.min.js"></script> --}}
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>