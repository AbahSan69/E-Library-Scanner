<div id="main-navigation" class="d-none d-lg-block mt-2 mt-lg-0">
    <ul class="nav-main nav-main-dark nav-main-horizontal nav-main-hover">
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('petugas/dashboard') ? 'active' : '' }}" href="{{ route('petugas.dashboard') }}">
            <i class="nav-main-link-icon si si-speedometer"></i>
            <span class="nav-main-link-name">Dashboard</span>
        </a>
      </li>
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('petugas/peminjaman') ? 'active' : '' }}" href="{{ route('petugas.peminjaman') }}">
            <i class="nav-main-link-icon si si-refresh"></i>
            <span class="nav-main-link-name">Peminjaman</span>
        </a>
      </li>
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('petugas/pengembalian') ? 'active' : '' }}" href="{{ route('petugas.pengembalian') }}">
            <i class="nav-main-link-icon si si-refresh"></i>
            <span class="nav-main-link-name">Pengembalian</span>
        </a>
      </li>
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('petugas/buku') ? 'active' : '' }}" href="{{ route('petugas.buku') }}">
            <i class="nav-main-link-icon si si-book-open"></i>
            <span class="nav-main-link-name">Buku</span>
        </a>
      </li>
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('petugas/siswa') ? 'active' : '' }}" href="{{ route('petugas.siswa') }}">
            <i class="nav-main-link-icon si si-users"></i>
            <span class="nav-main-link-name">Siswa</span>
        </a>
      </li>
    </ul>
  </div>
</div>
</div>