 <!-- ==================== NAVBAR ==================== -->
 <nav id="mainNav" class="navbar navbar-expand-lg">
     <div class="container">
         <a class="nav-logo" href="{{ route('customer.landing', $subdomain) }}">{{ $tenant->name }}<span>.</span></a>
         <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
             <span class="navbar-toggler-icon"></span>
         </button>
         <div class="collapse navbar-collapse" id="navMenu">
             <ul class="navbar-nav ms-auto align-items-center gap-1">
                 <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                 <li class="nav-item"><a class="nav-link" href="{{route('customer.services', $subdomain) }}">Services</a></li>
                 <li class="nav-item"><a class="nav-link" href="#team">Specialists</a></li>
                 <li class="nav-item"><a class="nav-link" href="{{route('customer.gallery', $subdomain) }}">Gallery</a></li>
                 <li class="nav-item"><a class="nav-link" href="{{route('customer.products', $subdomain) }}">Products</a></li>
                 @guest('customer')
                 <li class="nav-item ms-2">
                     <a class="nav-link" href="{{ route('customer.login', $subdomain) }}" style="color:rgba(255,255,255,0.7);">Login</a>
                 </li>
                 @endguest
             </ul>
         </div>
     </div>
 </nav>
