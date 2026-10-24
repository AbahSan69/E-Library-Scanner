<div id="main-navigation" class="d-none d-lg-block mt-2 mt-lg-0">
    <ul class="nav-main nav-main-dark nav-main-horizontal nav-main-hover">
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('admin/dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="nav-main-link-icon si si-speedometer"></i>
            <span class="nav-main-link-name">Dashboard</span>
        </a>
      </li>
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('admin/peminjaman') ? 'active' : '' }}" href="{{ route('admin.peminjaman') }}">
            <i class="nav-main-link-icon si si-refresh"></i>
            <span class="nav-main-link-name">Peminjaman</span>
        </a>
      </li>
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('admin/pengembalian') ? 'active' : '' }}" href="{{ route('admin.pengembalian') }}">
            <i class="nav-main-link-icon si si-refresh"></i>
            <span class="nav-main-link-name">Pengembalian</span>
        </a>
      </li>
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('admin/denda') ? 'active' : '' }}" href="{{ route('admin.denda.index') }}">
            <i class="nav-main-link-icon si si-wallet"></i>
            <span class="nav-main-link-name">Denda</span>
        </a>
      </li>
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('admin/buku') ? 'active' : '' }}" href="{{ route('admin.buku') }}">
            <i class="nav-main-link-icon si si-book-open"></i>
            <span class="nav-main-link-name">Buku</span>
        </a>
      </li>
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('admin/siswa') ? 'active' : '' }}" href="{{ route('admin.siswa') }}">
            <i class="nav-main-link-icon si si-users"></i>
            <span class="nav-main-link-name">Siswa</span>
        </a>
      </li>

      <li class="nav-main-item">
  <a class="nav-main-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}" href="{{ route('admin.laporan.index') }}">
    <i class="nav-main-link-icon si si-docs"></i>
    <span class="nav-main-link-name">Laporan</span>
  </a>
</li>
<li class="nav-main-item">
  <a class="nav-main-link {{ request()->routeIs('admin.keanggotaan.*') ? 'active' : '' }}" href="{{ route('admin.keanggotaan.index') }}">
    <i class="nav-main-link-icon si si-users"></i>
    <span class="nav-main-link-name">Keanggotaan</span>
  </a>
</li>
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('admin/akun') ? 'active' : '' }}" href="{{ route('admin.akun') }}">
            <i class="nav-main-link-icon si si-user"></i>
            <span class="nav-main-link-name">Akun</span>
        </a>
      </li>
      {{-- <li class="nav-main-heading">Heading</li>
      <li class="nav-main-item">
        <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
            <i class="nav-main-link-icon si si-puzzle"></i>
            <span class="nav-main-link-name">Kelola</span>
        </a>
        <ul class="nav-main-submenu">
            <li class="nav-main-item">
                <a class="nav-main-link" href="javascript:void(0)">
                    <span class="nav-main-link-name">Link #1</span>
                </a>
            </li>
            <li class="nav-main-item">
                <a class="nav-main-link" href="javascript:void(0)">
                    <span class="nav-main-link-name">Link #2</span>
                </a>
            </li>
        </ul>
      </li> --}}
    </ul>
  </div>
</div>
</div>