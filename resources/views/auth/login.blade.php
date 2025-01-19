@extends('layouts.app')
<style>
    @media (max-width: 550px) {
        .mob_logo_center {
            display: flex;
            justify-content: center;
            align-items: center;
        }
    }
    .lqh-sale-banner {
  width: 100%;
  background-size: 100%;
  background-color: #003e40;
  text-align: center;
  padding: 10px 20px;
}

.lqh-sale-banner h1 {
   font-size: 20px;
   margin-top: 0px;
   color: #FFFFFF;
   text-shadow: 0 0 7px #000000;
}


.banner-link:hover {
  text-decoration: none;
}
.loggedin .sales-banner-container{
  position:fixed;
  top:0;
  z-index: 1030;
}
.loggedin .lqh-sale-banner h1 {
  font-size: 18px;
}
.loggedin .lqh-sale-banner{
  padding:6px 12px;
}
@media (max-width: 550px) {
  .loggedin .lqh-sale-banner h1,.lqh-sale-banner h1 {
        font-size: 14px;
    }
}
</style>
@section('content')

        <div id="app" data-v-app="">
            <div  class="auth-main">
                <div  class="auth-wrapper v3">
                    <div  class="auth-form">
                        <div  class="auth-header row">
                            <div  class="my-1 col mob_logo_center">
                                <a href="{{ route('login') }}"><img src="{{ asset($settings['admin_sidebar_logo']) }}"
                                        alt="Logo" style="height: 8vh;"></a>
                            </div>
                        </div>
                        @if(config("services.sales.promotion"))
                            <div class=" w-100 sales-banner-container">
                                <div class="banner-link" ><div class="lqh-sale-banner">
                                    <h1 class="animated pulse">{!!config("services.sales.promotiontext")!!}</h1>
                                    </div></div>
                            </div>
                        
                        @endif
                        <div  class="my-3 card">
                            <div  class="card-body">
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div  class="text-center">
                                        <h3  class="mb-3 text-center">Login</h3>
                                        <p  class="mb-4 fs-6">Please log in to access your profile, manage your trading accounts, and adjust your settings.</p>
                                    </div>
                                    @if (session('status'))
                                        <div class="alert alert-success">
                                            {{ session('status') }}
                                        </div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger">
                                            {{ session('error') }}
                                        </div>
                                    @endif
                                    <div  class="mt-4 row">
                                        <div  class="col-12">
                                            <div  class="form-group">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" name="email" id="email" class="form-control"
                                                    placeholder="Email" required>
                                            </div>
                                        </div>
                                        <div  class="col-12">
                                            <div  class="form-group">
                                                <label for="password" class="form-label">Password</label>
                                                <input type="password" name="password" id="password" class="form-control"
                                                    placeholder="Password" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div  class="d-flex justify-content-between align-items-center">
                                        <div  class="form-check">
                                            <input type="checkbox" class="form-check-input" id="customCheckc1">
                                            <label class="form-check-label text-muted" for="customCheckc1">Remember Me?</label>
                                        </div>
                                        <h6  class="mb-0 text-secondary f-w-400"><a
                                                href="/forgot-password" class="link-primary">Forgot Password?</a></h6>
                                    </div>
                                    <div  class="mt-1 row g-3">
                                        <div  class="col-sm-12">
                                            <div  class="d-grid">
                                                <input type="submit" name="signin" value="Login" class="btn btn-primary">
                                            </div>
                                        </div>
                                        <div  class="col-sm-12">
                                            <div  class="d-grid">
                                                <a href="/register"  class="bg-transparent border btn">
                                                    Register </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div data-v-dde07c83="" class="auth-footer">
                            <p data-v-dde07c83="" class="m-0 text-center w-100" style="font-size: 11px;"> By logging in, you confirm that you have read and agree to <?= ($settings['admin_title']) ?>'s <a data-v-dde07c83="" target="_blank" href="https://www.lqhmarkets.com/risk-disclaimer">Risk Disclaimer</a>,
                             <a data-v-dde07c83="" target="_blank" href="https://www.lqhmarkets.com/terms-conditions">Terms & Conditions</a>, and <a data-v-dde07c83="" target="_blank" href="https://www.lqhmarkets.com/privacy-policy">Privacy Policy</a>.
                            </p>
                          </div>
                    </div>
                    <div  class="auth-sidecontent"
                        style="background: linear-gradient(45deg, rgb(25, 24, 76), rgb(var(--bs-primary-rgb))) !important;">
                        <div class="p-3 text-center px-lg-5">
                            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <img src="/assets/images/acc-1.png" alt="user-image" class="mb-3 hei-150">
                                        <h5 class="mb-0 text-white">Regulatory Excellence</h5>
                                        <p class="text-white text-opacity-50">Compliance Assurance</p>
                                        <div class="my-4 star f-20">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        </div>
                                        <p class="text-white">
                                            With meticulous attention to regulatory standards,
                                            {{ $settings['admin_title'] }}
                                            guarantees compliance assurance,
                                            fostering transparency and confidence among traders with a commitment to ethical
                                            conduct and integrity.
                                        </p>
                                    </div>
                                    <div class="carousel-item">
                                        <img src="/assets/images/ben-02.png" alt="user-image" class="mb-3 hei-150">
                                        <h5 class="mb-0 text-white">Transparent Pricing Policy</h5>
                                        <p class="text-white text-opacity-50">Clear Cost Commitment</p>
                                        <div class="my-4 star f-20">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        </div>
                                        <p class="text-white">
                                            At {{ $settings['admin_title'] }}, transparency is paramount. Our pricing policy
                                            ensures clarity and fairness,
                                            empowering traders with transparent pricing structures and no hidden fees for a
                                            seamless trading experience.
                                        </p>
                                    </div>
                                    <div class="carousel-item">
                                        <img src="/assets/images/ben-03.png" alt="user-image" class="mb-3 hei-150">
                                        <h5 class="mb-0 text-white">Swift and Precise Execution</h5>
                                        <p class="text-white text-opacity-50">Precision Trading</p>
                                        <div class="my-4 star f-20">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        </div>
                                        <p class="text-white">
                                            Experience lightning-fast trade execution with {{ $settings['admin_title'] }}.
                                            Our
                                            advanced trading infrastructure ensures
                                            swift and precise order processing, enabling traders to capitalize on market
                                            opportunities instantly.
                                        </p>
                                    </div>
                                    <div class="carousel-item">
                                        <img src="/assets/images/ben-04.png" alt="user-image" class="mb-3 hei-150">
                                        <h5 class="mb-0 text-white">Competitive Spreads</h5>
                                        <p class="text-white text-opacity-50">Cost Efficiency</p>
                                        <div class="my-4 star f-20">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        </div>
                                        <p class="text-white">
                                            Gain a competitive edge with {{ $settings['admin_title'] }}'s tight spreads. Our
                                            low-cost advantage ensures competitive pricing
                                            on all trading instruments, allowing traders to maximize profitability and
                                            minimize trading costs.
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-3 carousel-indicators position-relative">
                                    <button type="button" data-bs-target="#carouselExampleIndicators"
                                        data-bs-slide-to="0" class="active" aria-current="true"
                                        aria-label="Slide 1"></button>
                                    <button type="button" data-bs-target="#carouselExampleIndicators"
                                        data-bs-slide-to="1" aria-label="Slide 2"></button>
                                    <button type="button" data-bs-target="#carouselExampleIndicators"
                                        data-bs-slide-to="2" aria-label="Slide 3"></button>
                                    <button type="button" data-bs-target="#carouselExampleIndicators"
                                        data-bs-slide-to="3" aria-label="Slide 4"></button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


@endsection
