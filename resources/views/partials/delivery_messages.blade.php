@if(session('success_message'))<p class="portal-message success" role="status">{{ session('success_message') }}</p>@endif
@if($errors->any())<div class="portal-message error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
