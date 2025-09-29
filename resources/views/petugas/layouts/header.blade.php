<header id="page-header">
  <div class="content-header">
    <div class="d-flex align-items-center">
      <a class="fw-semibold fs-5 tracking-wider text-dual me-3 d-flex align-items-center" href="index.html">
        <img src="{{ asset('assets/oneui/media/favicons/logo_sman1citeurep.png') }}" alt="Logo SMAN I CITEUREUP" class="me-2" style="height: 32px;">
        SMAN I CITEUREUP
      </a>      
      {{-- <div class="dropdown d-inline-block me-2">
        <button type="button" class="btn btn-sm btn-alt-secondary" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-fw fa-bell"></i>
          <span class="text-primary">•</span>
        </button>
        <div class="dropdown-menu dropdown-menu-lg p-0 border-0 fs-sm" aria-labelledby="page-header-notifications-dropdown">
          <div class="p-2 bg-body-light border-bottom text-center rounded-top">
            <h5 class="dropdown-header text-uppercase">Notifications</h5>
          </div>
          <ul class="nav-items mb-0">
            <li>
              <a class="text-dark d-flex py-2" href="javascript:void(0)">
                <div class="flex-shrink-0 me-2 ms-3">
                  <i class="fa fa-fw fa-check-circle text-success"></i>
                </div>
                <div class="flex-grow-1 pe-2">
                  <div class="fw-semibold">You have a new follower</div>
                  <span class="fw-medium text-muted">15 min ago</span>
                </div>
              </a>
            </li>
            <li>
              <a class="text-dark d-flex py-2" href="javascript:void(0)">
                <div class="flex-shrink-0 me-2 ms-3">
                  <i class="fa fa-fw fa-plus-circle text-primary"></i>
                </div>
                <div class="flex-grow-1 pe-2">
                  <div class="fw-semibold">1 new sale, keep it up</div>
                  <span class="fw-medium text-muted">22 min ago</span>
                </div>
              </a>
            </li>
            <li>
              <a class="text-dark d-flex py-2" href="javascript:void(0)">
                <div class="flex-shrink-0 me-2 ms-3">
                  <i class="fa fa-fw fa-times-circle text-danger"></i>
                </div>
                <div class="flex-grow-1 pe-2">
                  <div class="fw-semibold">Update failed, restart server</div>
                  <span class="fw-medium text-muted">26 min ago</span>
                </div>
              </a>
            </li>
            <li>
              <a class="text-dark d-flex py-2" href="javascript:void(0)">
                <div class="flex-shrink-0 me-2 ms-3">
                  <i class="fa fa-fw fa-plus-circle text-primary"></i>
                </div>
                <div class="flex-grow-1 pe-2">
                  <div class="fw-semibold">2 new sales, keep it up</div>
                  <span class="fw-medium text-muted">33 min ago</span>
                </div>
              </a>
            </li>
            <li>
              <a class="text-dark d-flex py-2" href="javascript:void(0)">
                <div class="flex-shrink-0 me-2 ms-3">
                  <i class="fa fa-fw fa-user-plus text-success"></i>
                </div>
                <div class="flex-grow-1 pe-2">
                  <div class="fw-semibold">You have a new subscriber</div>
                  <span class="fw-medium text-muted">41 min ago</span>
                </div>
              </a>
            </li>
            <li>
              <a class="text-dark d-flex py-2" href="javascript:void(0)">
                <div class="flex-shrink-0 me-2 ms-3">
                  <i class="fa fa-fw fa-check-circle text-success"></i>
                </div>
                <div class="flex-grow-1 pe-2">
                  <div class="fw-semibold">You have a new follower</div>
                  <span class="fw-medium text-muted">42 min ago</span>
                </div>
              </a>
            </li>
          </ul>
          <div class="p-2 border-top text-center">
            <a class="d-inline-block fw-medium" href="javascript:void(0)">
              <i class="fa fa-fw fa-arrow-down me-1 opacity-50"></i> Load More..
            </a>
          </div>
        </div>
      </div> --}}
    </div>
    <div class="d-flex align-items-center">
      <button type="button" class="btn btn-sm btn-alt-secondary d-sm-none" data-toggle="layout" data-action="header_search_on">
        <i class="si si-magnifier"></i>
      </button>
      <div class="dropdown d-inline-block ms-2">
        <button type="button" class="btn btn-sm btn-alt-secondary" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <img class="rounded-circle" src="{{ asset('assets/oneui/media/avatars/avatar10.jpg') }}" alt="Header Avatar" style="width: 21px;">
          <span class="d-none d-sm-inline-block ms-1">{{ Auth::user()->name }}</span>
          <i class="fa fa-fw fa-angle-down d-none d-sm-inline-block"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-md dropdown-menu-end p-0 border-0" aria-labelledby="page-header-user-dropdown">
          <div class="p-3 text-center bg-body-light border-bottom rounded-top">
            <img class="img-avatar img-avatar48 img-avatar-thumb" src="{{ asset('assets/oneui/media/avatars/avatar10.jpg') }}" alt="">
            <p class="mt-2 mb-0 fw-medium">{{ Auth::user()->name }}</p>
            <p class="mb-0 text-muted fs-sm fw-medium">{{ Auth::user()->role->nama }}</p>
          </div>
          {{-- <div class="p-2">
            <a class="dropdown-item d-flex align-items-center justify-content-between" href="be_pages_generic_profile.html">
              <span class="fs-sm fw-medium">Profil</span>
            </a>
          </div> --}}
          <div role="separator" class="dropdown-divider m-0"></div>
          <div class="p-2">
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between">
                <span class="fs-sm fw-medium">Log Out</span>
              </button>
          </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="page-header-search" class="overlay-header bg-body-extra-light">
    <div class="content-header">
      <form class="w-100" method="POST">
        <div class="input-group input-group-sm">
          <button type="button" class="btn btn-alt-danger" data-toggle="layout" data-action="header_search_off">
            <i class="fa fa-fw fa-times-circle"></i>
          </button>
          <input type="text" class="form-control" placeholder="Search or hit ESC.." id="page-header-search-input" name="page-header-search-input">
        </div>
      </form>
    </div>
  </div>
  <div id="page-header-loader" class="overlay-header bg-primary-lighter">
    <div class="content-header">
      <div class="w-100 text-center">
        <i class="fa fa-fw fa-circle-notch fa-spin text-primary"></i>
      </div>
    </div>
  </div>
</header>