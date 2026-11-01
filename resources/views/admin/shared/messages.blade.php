@if(session('success_message'))<div class="admin-alert success" role="status">{{ session('success_message') }}</div>@endif
@if(session('error_message'))<div class="admin-alert error" role="alert">{{ session('error_message') }}</div>@endif
@if($errors->any())<div class="admin-alert error" role="alert"><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
