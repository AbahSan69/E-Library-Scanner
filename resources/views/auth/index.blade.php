<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>E-LIBRARY SMAN 1 CITEUREUP</title>
        <meta name="description" content="E-LIBRARY SMAN 1 CITEUREUP">
        <meta name="author" content="pixelcave">
        <meta name="robots" content="index, follow">
        <meta property="og:title" content="E-LIBRARY SMAN 1 CITEUREUP">
        <meta property="og:site_name" content="OneUI">
        <meta property="og:description" content="E-LIBRARY SMAN 1 CITEUREUP">
        <meta property="og:type" content="website">
        <meta property="og:url" content="">
        <meta property="og:image" content="">
        <link rel="shortcut icon" href="{{ asset('assets/media/favicons/logo_sman1citeurep.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/oneui/media/favicons/logo_sman1citeurep.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/oneui/media/favicons/logo_sman1citeurep.png') }}">
          <link rel="stylesheet" id="css-main" href="{{ asset('assets/oneui/css/oneui.min.css') }}"><link id="css-theme" rel="stylesheet" href="{{ asset('assets/oneui/css/themes/amethyst.min-5.11.css') }}">
          {{-- <script src="assets/js/setTheme.js"></script> --}}
      <script async="" src="https://www.googletagmanager.com/gtag/js?id=G-9HQDQJJYW7"></script><script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-9HQDQJJYW7');</script>
      </head>
      <body>
        @include('sweetalert::alert')
        <div id="page-container">
                  <main id="main-container">
        <div class="bg-image" style="background-image: url('{{ asset('assets/oneui/media/photos/sman1citeureup.jpeg') }}');">
          <div class="row g-0 bg-primary-dark-op">
            <div class="hero-static col-lg-4 d-none d-lg-flex flex-column justify-content-center">
              <div class="p-4 p-xl-5 flex-grow-1 d-flex align-items-center">
                <div class="w-100">
                  <a class="link-fx fw-semibold fs-2 text-white" href="index.html">
                    E-Library <br> SMAN 1 Citeureup
                  </a>
                  <p class="text-white-75 me-xl-8 mt-2">
                    E-Library membantu perpustakaan bekerja lebih efisien, memantau kinerja, dan mengelola koleksi dengan lebih efektif.
                  </p>
                </div>
              </div>
              <div class="p-4 p-xl-5 d-xl-flex justify-content-between align-items-center fs-sm">
                {{-- <p class="fw-medium text-white-50 mb-0">
                  <strong>OneUI 5.11</strong> © <span data-toggle="year-copy" class="js-year-copy-enabled">2025</span>
                </p> --}}
                <ul class="list list-inline mb-0 py-2">
                  {{-- <li class="list-inline-item">
                    <a class="text-white-75 fw-medium" href="javascript:void(0)">Legal</a>
                  </li>
                  <li class="list-inline-item">
                    <a class="text-white-75 fw-medium" href="javascript:void(0)">Contact</a>
                  </li> --}}
                  <li class="list-inline-item">
                    <a class="text-white-75 fw-medium" href="javascript:void(0)">Panduan</a>
                  </li>
                </ul>
              </div>
            </div>
            <div class="hero-static col-lg-8 d-flex flex-column align-items-center bg-body-extra-light">
              <div class="p-3 w-100 d-lg-none text-center">
                <a class="link-fx fw-semibold fs-3 text-dark" href="index.html">
                    E-Library <br> SMAN 1 Citeureup
                </a>
              </div>
              <div class="p-4 w-100 flex-grow-1 d-flex align-items-center">
                <div class="w-100">
                  <div class="text-center mb-5">
                    <p class="mb-3">
                        <img src="{{ asset('assets/oneui/media/favicons/logo_sman1citeurep.png') }}" alt="Logo" width="35" height="42">
                    </p>
                    <h1 class="fw-bold mb-2">
                      Sign In
                    </h1>
                    <p class="fw-medium text-muted">
                      {{-- Welcome, please login or <a href="op_auth_signup3.html">sign up</a> for a new account. --}}
                      Pengelola E-Library SMAN 1 Citeureup
                    </p>
                  </div>
                  <div class="row g-0 justify-content-center">
                    <div class="col-sm-8 col-xl-4">
                      <form class="js-validation-signin" action="{{ route('login') }}" method="POST" novalidate="novalidate">
                        @csrf
                        <div class="mb-4">
                          <input type="text" class="form-control form-control-lg form-control-alt py-3" id="name" name="name" placeholder="Username">
                        </div>
                        <div class="mb-4">
                          <input type="password" class="form-control form-control-lg form-control-alt py-3" id="password" name="password" placeholder="Password">
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                          <div>
                            {{-- <a class="text-muted fs-sm fw-medium d-block d-lg-inline-block mb-1" href="op_auth_reminder3.html">
                              Forgot Password?
                            </a> --}}
                          </div>
                          <div>
                            <button type="submit" class="btn btn-lg btn-alt-primary">
                              <i class="fa fa-fw fa-sign-in-alt me-1 opacity-50"></i> Sign In
                            </button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              <div class="px-4 py-3 w-100 d-lg-none d-flex flex-column flex-sm-row justify-content-between fs-sm text-center text-sm-start">
                {{-- <p class="fw-medium text-black-50 py-2 mb-0">
                  <strong>OneUI 5.11</strong> © <span data-toggle="year-copy" class="js-year-copy-enabled">2025</span>
                </p> --}}
                <ul class="list list-inline py-2 mb-0">
                  {{-- <li class="list-inline-item">
                    <a class="text-muted fw-medium" href="javascript:void(0)">Legal</a>
                  </li>
                  <li class="list-inline-item">
                    <a class="text-muted fw-medium" href="javascript:void(0)">Contact</a>
                  </li> --}}
                  <li class="list-inline-item">
                    <a class="text-muted fw-medium" href="javascript:void(0)">Panduan</a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
          </main>
          </div>
        <script src="{{ asset('assets/oneui/js/oneui.app.min.js') }}"></script>
        <script src="{{ asset('assets/oneui/js/lib/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/oneui/js/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
        <script src="{{ asset('assets/oneui/js/pages/op_auth_signin.min.js') }}"></script>
        
        
        </body>
</html>