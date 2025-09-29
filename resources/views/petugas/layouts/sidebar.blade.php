<nav id="sidebar" aria-label="Main Navigation">
  <div class="content-header">
    <a class="fw-semibold text-dual" href="index.html">
      <span class="smini-visible">
        <i class="fa fa-circle-notch text-primary"></i>
      </span>
      <span class="smini-hide fs-5 tracking-wider">LMS - UOL</span>
    </a>
    <div class="d-flex align-items-center gap-1">
      <a class="d-lg-none btn btn-sm btn-alt-secondary ms-1" data-toggle="layout" data-action="sidebar_close" href="javascript:void(0)">
        <i class="fa fa-fw fa-times"></i>
      </a>
    </div>
  </div>
  <div class="js-sidebar-scroll simplebar-scrollable-y" data-simplebar="init"><div class="simplebar-wrapper" style="margin: 0px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" tabindex="0" role="region" aria-label="scrollable content" style="height: 100%; overflow: hidden scroll;"><div class="simplebar-content" style="padding: 0px;">
    <div class="content-side">
      <ul class="nav-main">
        <li class="nav-main-item">
<a class="nav-main-link {{ Request::is('1/dashboard') ? 'active' : '' }}" href="">
<i class="nav-main-link-icon si si-speedometer"></i>
<span class="nav-main-link-name">Dashboard</span>
</a>
</li>
<li class="nav-main-heading">Akademik</li>
<li class="nav-main-item">
  <a class="nav-main-link {{ Request::is('1/courses_admin') ? 'active' : '' }}" href="">
  <i class="nav-main-link-icon si si-graduation"></i>
  <span class="nav-main-link-name">Course</span>
  </a>
  </li>
  <li class="nav-main-item">
    <a class="nav-main-link {{ Request::is('1/assignments_admin') ? 'active' : '' }}" href="">
    <i class="nav-main-link-icon si si-clock"></i>
    <span class="nav-main-link-name">Tugas</span>
    </a>
    </li>
    <a class="nav-main-link {{ Request::is('1/exams_admin') ? 'active' : '' }}" href="">
      <i class="nav-main-link-icon si si-pencil"></i>
      <span class="nav-main-link-name">Ujian</span>
      </a>
      </li>
      <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('1/discussion_admin') ? 'active' : '' }}" href="">
        <i class="nav-main-link-icon si si-bubbles"></i>
        <span class="nav-main-link-name">Diskusi</span>
        </a>
        </li>
        <li class="nav-main-item">
          <a class="nav-main-link {{ Request::is('1/video_admin') ? 'active' : '' }}" href="">
          <i class="nav-main-link-icon si si-camrecorder"></i>
          <span class="nav-main-link-name">Video</span>
          </a>
          </li>

      <li class="nav-main-heading">Komunikasi</li>
    <li class="nav-main-item">
        <a class="nav-main-link {{ Request::is('1/message_admin') ? 'active' : '' }}" href="">
        <i class="nav-main-link-icon si si-bubble"></i>
        <span class="nav-main-link-name">Pesan</span>
        </a>
        </li>
        <li class="nav-main-item">
          <a class="nav-main-link {{ Request::is('1/announcement_admin') ? 'active' : '' }}" href="">
          <i class="nav-main-link-icon si si-microphone"></i>
          <span class="nav-main-link-name">Pengumuman</span>
          </a>
          </li>

        <li class="nav-main-heading">Laporan & Analitik</li>
    <li class="nav-main-item">
      <a class="nav-main-link {{ Request::is('list_calon_siswa') ? 'active' : '' }}" href="#">
      <i class="nav-main-link-icon si si-handbag"></i>
      <span class="nav-main-link-name">Laporan</span>
      </a>
      </li>

      <li class="nav-main-heading">Manajemen Pengguna</li>
          <li class="nav-main-item">
            <a class="nav-main-link nav-main-link-submenu {{ Request::is('1/super_admin') || Request::is('1/admin') || Request::is('1/lectures_admin') || Request::is('1/students_admin')  ? 'active' : '' }}" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
            <i class="nav-main-link-icon si si-layers"></i>
            <span class="nav-main-link-name">Role & Hak Akses</span>
            </a>
            <ul class="nav-main-submenu">
            <li class="nav-main-item open">
              <a class="nav-main-link {{ Request::is('1/super_admin') ? 'active' : '' }}" href="">
                <i class="nav-main-link-icon si si-people"></i>
                <span class="nav-main-link-name">Super Admin</span>
                </a>
            </li>
            {{-- <li class="nav-main-item open">
              <a class="nav-main-link {{ Request::is('list_calon_siswa') ? 'active' : '' }}" href="#">
                <i class="nav-main-link-icon si si-people"></i>
                <span class="nav-main-link-name">Admin</span>
                </a>
            </li> --}}
            <li class="nav-main-item open">
              <a class="nav-main-link {{ Request::is('1/lecturers_admin') ? 'active' : '' }}" href="">
                <i class="nav-main-link-icon si si-people"></i>
                <span class="nav-main-link-name">Dosen</span>
                </a>
            </li>
            <li class="nav-main-item open">
              <a class="nav-main-link {{ Request::is('1/students_admin') ? 'active' : '' }}" href="">
                <i class="nav-main-link-icon si si-people"></i>
                <span class="nav-main-link-name">Mahasiswa</span>
                </a>
            </li>
            </ul>
          </li>
          {{-- <li class="nav-main-item">
            <a class="nav-main-link {{ Request::is('list_calon_siswa') ? 'active' : '' }}" href="#">
            <i class="nav-main-link-icon si si-users"></i>
            <span class="nav-main-link-name">Daftar Pengguna</span>
            </a>
            </li> --}}
        </li>

        <li class="nav-main-heading">Setting</li>
          <li class="nav-main-item">
            <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
            <i class="nav-main-link-icon si si-setting"></i>
            <span class="nav-main-link-name">Konfigurasi</span>
            </a>
            <ul class="nav-main-submenu">
            <li class="nav-main-item open">
              <a class="nav-main-link {{ Request::is('list_calon_siswa') ? 'active' : '' }}" href="#">
                <i class="nav-main-link-icon si si-people"></i>
                <span class="nav-main-link-name">Super Admin</span>
                </a>
            </li>
            <li class="nav-main-item open">
              <a class="nav-main-link {{ Request::is('list_calon_siswa') ? 'active' : '' }}" href="#">
                <i class="nav-main-link-icon si si-people"></i>
                <span class="nav-main-link-name">Admin</span>
                </a>
            </li>
            <li class="nav-main-item open">
              <a class="nav-main-link {{ Request::is('list_calon_siswa') ? 'active' : '' }}" href="#">
                <i class="nav-main-link-icon si si-people"></i>
                <span class="nav-main-link-name">Dosen</span>
                </a>
            </li>
            <li class="nav-main-item open">
              <a class="nav-main-link {{ Request::is('list_calon_siswa') ? 'active' : '' }}" href="">
                <i class="nav-main-link-icon si si-people"></i>
                <span class="nav-main-link-name">Mahasiswa</span>
                </a>
            </li>
            </ul>
          </li>
        </li>
    </div>
  </div></div></div></div><div class="simplebar-placeholder" style="width: 375px; height: 735px;"></div></div><div class="simplebar-track simplebar-horizontal" style="visibility: hidden;"><div class="simplebar-scrollbar" style="width: 0px; display: none;"></div></div><div class="simplebar-track simplebar-vertical" style="visibility: visible;"><div class="simplebar-scrollbar" style="height: 494px; transform: translate3d(0px, 0px, 0px); display: block;"></div></div></div>
</nav>