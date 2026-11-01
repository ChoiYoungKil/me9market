@if(session('flash_message_success'))<div class="alert success" role="status">{{ session('flash_message_success') }}</div>@endif
@if(session('flash_message_error'))<div class="alert error" role="alert">{{ session('flash_message_error') }}</div>@endif
@if($errors->any())<div class="alert error" role="alert"><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
